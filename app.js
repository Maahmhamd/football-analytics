/**
 * Football Analytics - Main Application
 * JavaScript Logic for Frontend
 */

// ===== Configuration =====
const API_URL = 'api.php';
const REFRESH_INTERVAL = 30000; // 30 seconds
const CACHE_DURATION = 5 * 60 * 1000; // 5 minutes

// ===== Cache Storage =====
const cache = {
    data: {},
    timestamps: {},
    
    set: function(key, value) {
        this.data[key] = value;
        this.timestamps[key] = Date.now();
    },
    
    get: function(key) {
        if (!this.data[key]) return null;
        
        const age = Date.now() - this.timestamps[key];
        if (age > CACHE_DURATION) {
            delete this.data[key];
            delete this.timestamps[key];
            return null;
        }
        
        return this.data[key];
    },
    
    clear: function(key) {
        if (key) {
            delete this.data[key];
            delete this.timestamps[key];
        } else {
            this.data = {};
            this.timestamps = {};
        }
    }
};

// ===== API Call Handler =====
async function apiCall(action, params = {}) {
    try {
        const queryParams = new URLSearchParams({ action, ...params });
        const url = `${API_URL}?${queryParams}`;
        
        // Check cache first
        const cacheKey = `${action}_${JSON.stringify(params)}`;
        const cachedData = cache.get(cacheKey);
        if (cachedData) {
            return cachedData;
        }
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            cache.set(cacheKey, data.data);
            return data.data;
        } else {
            console.error('API Error:', data.message);
            return null;
        }
    } catch (error) {
        console.error('Fetch Error:', error);
        return null;
    }
}

// ===== DOM Elements =====
const DOM = {
    liveMatchesContainer: document.getElementById('liveMatchesContainer'),
    allMatchesContainer: document.getElementById('allMatchesContainer'),
    tournamentsContainer: document.getElementById('tournamentsContainer'),
    topScorersContainer: document.getElementById('topScorersContainer'),
    standingsContainer: document.getElementById('standingsContainer'),
    playersContainer: document.getElementById('playersContainer'),
    
    // Modals
    matchModal: document.getElementById('matchModal'),
    matchDetailContent: document.getElementById('matchDetailContent'),
    searchModal: document.getElementById('searchModal'),
    searchResultsContent: document.getElementById('searchResultsContent'),
    
    // Filters
    tournamentFilter: document.getElementById('tournamentFilter'),
    statusFilter: document.getElementById('statusFilter'),
    standingsTournamentFilter: document.getElementById('standingsTournamentFilter'),
    teamFilter: document.getElementById('teamFilter'),
    
    // Search
    searchInput: document.getElementById('searchInput'),
    
    // Buttons
    liveMatchesBtn: document.getElementById('liveMatchesBtn'),
    upcomingBtn: document.getElementById('upcomingBtn'),
    refreshBtn: document.getElementById('refreshBtn'),
    
    // Stats
    totalGoals: document.getElementById('totalGoals'),
    totalMatches: document.getElementById('totalMatches'),
    totalTeams: document.getElementById('totalTeams'),
    totalTournaments: document.getElementById('totalTournaments')
};

// ===== Initialize Application =====
function initApp() {
    console.log('Initializing Football Analytics App...');
    
    // Load initial data
    loadLiveMatches();
    loadAllMatches();
    loadTournaments();
    loadTopScorers();
    loadStatistics();
    
    // Setup event listeners
    setupEventListeners();
    
    // Setup auto-refresh for live matches
    setInterval(loadLiveMatches, REFRESH_INTERVAL);
    
    console.log('App initialized successfully');
}

// ===== Event Listeners =====
function setupEventListeners() {
    // Filter buttons
    DOM.liveMatchesBtn?.addEventListener('click', loadLiveMatches);
    DOM.upcomingBtn?.addEventListener('click', () => loadMatches('scheduled'));
    
    // Refresh button
    DOM.refreshBtn?.addEventListener('click', () => {
        cache.clear();
        loadAllMatches();
    });
    
    // Filters
    DOM.tournamentFilter?.addEventListener('change', loadAllMatches);
    DOM.statusFilter?.addEventListener('change', loadAllMatches);
    DOM.standingsTournamentFilter?.addEventListener('change', loadStandings);
    DOM.teamFilter?.addEventListener('change', loadTeamPlayers);
    
    // Search
    DOM.searchInput?.addEventListener('input', debounce(handleSearch, 300));
    
    // Modal close
    document.querySelectorAll('.close').forEach(closeBtn => {
        closeBtn.addEventListener('click', (e) => {
            e.target.parentElement.parentElement.style.display = 'none';
        });
    });
    
    // Click outside modal to close
    window.addEventListener('click', (e) => {
        if (e.target === DOM.matchModal) {
            DOM.matchModal.style.display = 'none';
        }
        if (e.target === DOM.searchModal) {
            DOM.searchModal.style.display = 'none';
        }
    });
    
    // Navigation links
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', (e) => {
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
            link.classList.add('active');
        });
    });
}

// ===== Load Live Matches =====
async function loadLiveMatches() {
    const data = await apiCall('get_live_matches');
    
    if (!data) {
        DOM.liveMatchesContainer.innerHTML = '<div class="error">خطأ في تحميل البيانات</div>';
        return;
    }
    
    if (data.length === 0) {
        DOM.liveMatchesContainer.innerHTML = '<div class="no-data">لا توجد مباريات مباشرة حالياً</div>';
        return;
    }
    
    DOM.liveMatchesContainer.innerHTML = data.map(match => createMatchCard(match)).join('');
    
    // Add click event to all match cards
    document.querySelectorAll('.match-card').forEach(card => {
        card.addEventListener('click', () => {
            const matchId = card.dataset.matchId;
            showMatchDetails(matchId);
        });
    });
}

// ===== Load All Matches =====
async function loadAllMatches() {
    const tournamentId = DOM.tournamentFilter?.value || '';
    const status = DOM.statusFilter?.value || '';
    
    const params = {
        limit: 50,
        offset: 0
    };
    
    if (tournamentId) params.tournament_id = tournamentId;
    if (status) params.status = status;
    
    const data = await apiCall('get_matches', params);
    
    if (!data) {
        DOM.allMatchesContainer.innerHTML = '<div class="error">خطأ في تحميل البيانات</div>';
        return;
    }
    
    const matches = data.matches || [];
    
    if (matches.length === 0) {
        DOM.allMatchesContainer.innerHTML = '<div class="no-data">لا توجد مباريات</div>';
        return;
    }
    
    // Display as list
    DOM.allMatchesContainer.innerHTML = `
        <div class="matches-list">
            ${matches.map(match => `
                <div class="match-list-item" data-match-id="${match.id}">
                    <div class="match-list-date">
                        <strong>${formatDate(match.match_date)}</strong>
                    </div>
                    <div class="match-list-teams">
                        <div class="team">
                            <img src="${match.home_team_logo || 'placeholder.png'}" alt="${match.home_team_name}" class="team-logo-small">
                            <span>${match.home_team_name}</span>
                        </div>
                        <div class="match-list-score">
                            <strong>${match.home_score} - ${match.away_score}</strong>
                            <span class="match-list-status ${match.status}">${getStatusAr(match.status)}</span>
                        </div>
                        <div class="team">
                            <span>${match.away_team_name}</span>
                            <img src="${match.away_team_logo || 'placeholder.png'}" alt="${match.away_team_name}" class="team-logo-small">
                        </div>
                    </div>
                    <div class="match-list-tournament">
                        ${match.tournament_name || 'بدون بطولة'}
                    </div>
                </div>
            `).join('')}
        </div>
    `;
    
    // Add click events
    document.querySelectorAll('.match-list-item').forEach(item => {
        item.addEventListener('click', () => {
            showMatchDetails(item.dataset.matchId);
        });
    });
}

// ===== Load Tournaments =====
async function loadTournaments() {
    const data = await apiCall('get_tournaments');
    
    if (!data) {
        DOM.tournamentsContainer.innerHTML = '<div class="error">خطأ في تحميل البيانات</div>';
        return;
    }
    
    if (data.length === 0) {
        DOM.tournamentsContainer.innerHTML = '<div class="no-data">لا توجد بطولات</div>';
        return;
    }
    
    // Update tournament filters
    updateFilterSelect(DOM.tournamentFilter, data);
    updateFilterSelect(DOM.standingsTournamentFilter, data);
    
    DOM.tournamentsContainer.innerHTML = data.map(tournament => `
        <div class="tournament-card" data-tournament-id="${tournament.id}">
            <img src="${tournament.logo_url || 'placeholder.png'}" alt="${tournament.name}" class="tournament-logo">
            <div class="tournament-name">${tournament.name}</div>
            <div class="tournament-type">${tournament.type_ar || tournament.type}</div>
            <div class="tournament-season">الموسم: ${tournament.season || 'N/A'}</div>
        </div>
    `).join('');
    
    // Add click events
    document.querySelectorAll('.tournament-card').forEach(card => {
        card.addEventListener('click', () => {
            const tournamentId = card.dataset.tournamentId;
            DOM.tournamentFilter.value = tournamentId;
            loadAllMatches();
            document.querySelector('.nav-link[href="#matches"]').click();
            document.querySelector('#matches').scrollIntoView({ behavior: 'smooth' });
        });
    });
}

// ===== Load Top Scorers =====
async function loadTopScorers() {
    const data = await apiCall('get_top_scorers', { limit: 8 });
    
    if (!data) {
        DOM.topScorersContainer.innerHTML = '<div class="error">خطأ في تحميل البيانات</div>';
        return;
    }
    
    if (data.length === 0) {
        DOM.topScorersContainer.innerHTML = '<div class="no-data">لا توجد بيانات</div>';
        return;
    }
    
    DOM.topScorersContainer.innerHTML = data.map((scorer, index) => `
        <div class="scorer-card">
            <div class="scorer-rank">${index + 1}</div>
            <img src="${scorer.photo_url || 'placeholder.png'}" alt="${scorer.name}" class="scorer-photo">
            <div class="scorer-name">${scorer.name}</div>
            <div class="scorer-team">${scorer.team_name || 'N/A'}</div>
            <div class="scorer-stats">
                <div class="scorer-stat">
                    <span class="scorer-stat-number">${scorer.goals || 0}</span>
                    <span class="scorer-stat-label">أهداف</span>
                </div>
                <div class="scorer-stat">
                    <span class="scorer-stat-number">${scorer.assists || 0}</span>
                    <span class="scorer-stat-label">تمريرات</span>
                </div>
                <div class="scorer-stat">
                    <span class="scorer-stat-number">${scorer.matches_played || 0}</span>
                    <span class="scorer-stat-label">مباريات</span>
                </div>
            </div>
        </div>
    `).join('');
}

// ===== Load Standings =====
async function loadStandings() {
    const tournamentId = DOM.standingsTournamentFilter?.value;
    
    if (!tournamentId) {
        DOM.standingsContainer.innerHTML = '<div class="no-data">اختر بطولة</div>';
        return;
    }
    
    const data = await apiCall('get_tournament_standings', { tournament_id: tournamentId });
    
    if (!data) {
        DOM.standingsContainer.innerHTML = '<div class="error">خطأ في تحميل البيانات</div>';
        return;
    }
    
    if (data.length === 0) {
        DOM.standingsContainer.innerHTML = '<div class="no-data">لا توجد بيانات</div>';
        return;
    }
    
    DOM.standingsContainer.innerHTML = `
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>الترتيب</th>
                        <th>الفريق</th>
                        <th>الم</th>
                        <th>ف</th>
                        <th>ت</th>
                        <th>خ</th>
                        <th>أ</th>
                        <th>ض</th>
                        <th>ف.أ</th>
                        <th>نقاط</th>
                    </tr>
                </thead>
                <tbody>
                    ${data.map(standing => `
                        <tr>
                            <td><div class="position-badge">${standing.position || 0}</div></td>
                            <td>
                                <div class="team-with-logo">
                                    <img src="${standing.logo_url || 'placeholder.png'}" alt="${standing.team_name}" class="team-logo-small">
                                    <span>${standing.team_name}</span>
                                </div>
                            </td>
                            <td>${standing.played || 0}</td>
                            <td>${standing.wins || 0}</td>
                            <td>${standing.draws || 0}</td>
                            <td>${standing.losses || 0}</td>
                            <td>${standing.goals_for || 0}</td>
                            <td>${standing.goals_against || 0}</td>
                            <td>${standing.goal_difference || 0}</td>
                            <td><strong>${standing.points || 0}</strong></td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
}

// ===== Load Team Players =====
async function loadTeamPlayers() {
    const teamId = DOM.teamFilter?.value;
    
    if (!teamId) {
        DOM.playersContainer.innerHTML = '<div class="no-data">اختر فريق</div>';
        return;
    }
    
    const data = await apiCall('get_team_players', { team_id: teamId });
    
    if (!data) {
        DOM.playersContainer.innerHTML = '<div class="error">خطأ في تحميل البيانات</div>';
        return;
    }
    
    if (data.length === 0) {
        DOM.playersContainer.innerHTML = '<div class="no-data">لا توجد لاعبين</div>';
        return;
    }
    
    DOM.playersContainer.innerHTML = data.map(player => `
        <div class="player-card">
            <img src="${player.photo_url || 'placeholder.png'}" alt="${player.name}" class="player-avatar">
            <div class="player-name">${player.name}</div>
            <div class="player-position">${getPositionAr(player.position)}</div>
            <div class="player-details">
                <div class="detail-item">
                    <span class="detail-label">الرقم</span>
                    <span class="detail-value">#${player.jersey_number || '-'}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">العمر</span>
                    <span class="detail-value">${player.age || '-'}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">الطول</span>
                    <span class="detail-value">${player.height || '-'} سم</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">الوزن</span>
                    <span class="detail-value">${player.weight || '-'} كج</span>
                </div>
            </div>
        </div>
    `).join('');
}

// ===== Load Statistics =====
async function loadStatistics() {
    const matchesData = await apiCall('get_matches', { limit: 100, offset: 0 });
    const teamsData = await apiCall('get_teams');
    const tournamentsData = await apiCall('get_tournaments');
    
    if (matchesData) {
        let totalGoals = 0;
        matchesData.matches?.forEach(match => {
            totalGoals += (match.home_score || 0) + (match.away_score || 0);
        });
        DOM.totalMatches.textContent = matchesData.total || 0;
        DOM.totalGoals.textContent = totalGoals;
    }
    
    if (teamsData) {
        DOM.totalTeams.textContent = teamsData.length || 0;
        
        // Update team filter
        updateFilterSelect(DOM.teamFilter, teamsData, 'id', 'name');
    }
    
    if (tournamentsData) {
        DOM.totalTournaments.textContent = tournamentsData.length || 0;
    }
}

// ===== Show Match Details =====
async function showMatchDetails(matchId) {
    const data = await apiCall('get_match_details', { match_id: matchId });
    
    if (!data) {
        alert('خطأ في تحميل تفاصيل المباراة');
        return;
    }
    
    DOM.matchDetailContent.innerHTML = `
        <div class="match-detail">
            <div class="match-detail-header">
                <h2>${data.match_title}</h2>
                <div style="font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.5rem;">
                    ${formatDate(data.match_date)} | ${data.stadium || 'ملعب'} | ${data.tournament_name || ''}
                </div>
            </div>
            
            <div class="match-detail-score">
                <div class="detail-team">
                    <img src="${data.home_team_logo || 'placeholder.png'}" alt="${data.home_team_name}" class="detail-team-logo">
                    <div class="detail-team-name">${data.home_team_name}</div>
                </div>
                <div class="detail-score">${data.home_score} - ${data.away_score}</div>
                <div class="detail-team">
                    <img src="${data.away_team_logo || 'placeholder.png'}" alt="${data.away_team_name}" class="detail-team-logo">
                    <div class="detail-team-name">${data.away_team_name}</div>
                </div>
            </div>
            
            <div class="match-info">
                <div class="info-item">
                    <span class="info-label">الحالة:</span>
                    <span class="info-value">${getStatusAr(data.status)}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">الحضور:</span>
                    <span class="info-value">${data.attendance || 'N/A'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">الحكم:</span>
                    <span class="info-value">${data.referee_name || 'N/A'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">الملعب:</span>
                    <span class="info-value">${data.stadium_name || 'N/A'}</span>
                </div>
            </div>
            
            ${data.home_score !== undefined ? `
            <div style="margin: 1.5rem 0; padding: 1.5rem; background: var(--light-bg); border-radius: 8px;">
                <h3 style="margin-bottom: 1rem; color: var(--primary-color);">إحصائيات المباراة</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; font-size: 0.875rem;">
                    <div style="text-align: center;">
                        <div style="font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">امتلاك الكرة</div>
                        <div style="display: flex; justify-content: center; align-items: center; gap: 0.5rem;">
                            <span style="color: var(--primary-color); font-weight: 700;">${data.possession_home || 0}%</span>
                            <div style="width: 60px; height: 4px; background: #e0e7ff; border-radius: 2px; position: relative;">
                                <div style="position: absolute; height: 100%; background: var(--primary-color); width: ${data.possession_home || 0}%;"></div>
                            </div>
                            <span style="color: var(--text-secondary); font-weight: 700;">${100 - (data.possession_away || 0)}%</span>
                        </div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">الرميات على المرمى</div>
                        <div style="font-size: 1.25rem; font-weight: 700; color: var(--primary-color);">
                            ${data.shots_on_target_home || 0} - ${data.shots_on_target_away || 0}
                        </div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">الركنيات</div>
                        <div style="font-size: 1.25rem; font-weight: 700; color: var(--primary-color);">
                            ${data.corners_home || 0} - ${data.corners_away || 0}
                        </div>
                    </div>
                </div>
            </div>
            ` : ''}
            
            ${data.video_url ? `
            <div style="margin: 1.5rem 0; padding: 1.5rem; background: var(--light-bg); border-radius: 8px;">
                <h3 style="margin-bottom: 1rem; color: var(--primary-color);">البث المرئي</h3>
                <video width="100%" height="auto" controls style="border-radius: 8px;">
                    <source src="${data.video_url}" type="video/mp4">
                    المتصفح الخاص بك لا يدعم تشغيل الفيديو.
                </video>
            </div>
            ` : ''}
            
            ${data.events && data.events.length > 0 ? `
            <div style="margin: 1.5rem 0; padding: 1.5rem; background: var(--light-bg); border-radius: 8px;">
                <h3 style="margin-bottom: 1rem; color: var(--primary-color);">أحداث المباراة</h3>
                <div class="events-timeline">
                    ${data.events.map(event => `
                        <div class="event-item">
                            <div class="event-marker">${event.minute || 0}'</div>
                            <div class="event-content">
                                <div class="event-type">${event.event_type_ar || event.event_type}</div>
                                <div class="event-description">
                                    ${event.player_name || ''} - ${event.team_name || ''}
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
            ` : ''}
            
            ${data.player_stats_home && data.player_stats_home.length > 0 ? `
            <div style="margin: 1.5rem 0;">
                <h3 style="margin-bottom: 1rem; color: var(--primary-color);">اللاعبون</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <h4 style="margin-bottom: 1rem; color: var(--primary-color);">${data.home_team_name}</h4>
                        <div class="table-wrapper">
                            <table style="font-size: 0.875rem;">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>اللاعب</th>
                                        <th>أ</th>
                                        <th>ت</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.player_stats_home.map(player => `
                                        <tr>
                                            <td>${player.jersey_number || '-'}</td>
                                            <td>${player.player_name}</td>
                                            <td>${player.goals || 0}</td>
                                            <td>${player.assists || 0}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 1rem; color: var(--primary-color);">${data.away_team_name}</h4>
                        <div class="table-wrapper">
                            <table style="font-size: 0.875rem;">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>اللاعب</th>
                                        <th>أ</th>
                                        <th>ت</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.player_stats_away.map(player => `
                                        <tr>
                                            <td>${player.jersey_number || '-'}</td>
                                            <td>${player.player_name}</td>
                                            <td>${player.goals || 0}</td>
                                            <td>${player.assists || 0}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            ` : ''}
        </div>
    `;
    
    DOM.matchModal.style.display = 'block';
}

// ===== Search Handler =====
async function handleSearch(e) {
    const query = e.target.value.trim();
    
    if (query.length < 2) {
        DOM.searchModal.style.display = 'none';
        return;
    }
    
    const data = await apiCall('search', { q: query });
    
    if (!data) {
        alert('خطأ في البحث');
        return;
    }
    
    let html = '';
    
    if (data.teams && data.teams.length > 0) {
        html += '<div class="search-results-section"><div class="search-results-title">الفرق</div>';
        data.teams.forEach(team => {
            html += `
                <div class="search-result-item" onclick="handleSelectTeam(${team.id})">
                    <img src="${team.logo_url || 'placeholder.png'}" alt="${team.name}" class="search-result-image">
                    <div class="search-result-info">
                        <h4>${team.name}</h4>
                    </div>
                </div>
            `;
        });
        html += '</div>';
    }
    
    if (data.players && data.players.length > 0) {
        html += '<div class="search-results-section"><div class="search-results-title">اللاعبون</div>';
        data.players.forEach(player => {
            html += `
                <div class="search-result-item">
                    <img src="${player.photo_url || 'placeholder.png'}" alt="${player.name}" class="search-result-image">
                    <div class="search-result-info">
                        <h4>${player.name}</h4>
                        <p>${player.position || ''} - ${player.team_name || ''}</p>
                    </div>
                </div>
            `;
        });
        html += '</div>';
    }
    
    if (data.tournaments && data.tournaments.length > 0) {
        html += '<div class="search-results-section"><div class="search-results-title">البطولات</div>';
        data.tournaments.forEach(tournament => {
            html += `
                <div class="search-result-item" onclick="handleSelectTournament(${tournament.id})">
                    <img src="${tournament.logo_url || 'placeholder.png'}" alt="${tournament.name}" class="search-result-image">
                    <div class="search-result-info">
                        <h4>${tournament.name}</h4>
                    </div>
                </div>
            `;
        });
        html += '</div>';
    }
    
    if (!html) {
        html = '<div class="no-data">لم يتم العثور على نتائج</div>';
    }
    
    DOM.searchResultsContent.innerHTML = html;
    DOM.searchModal.style.display = 'block';
}

// ===== Helper Functions =====

function handleSelectTeam(teamId) {
    DOM.teamFilter.value = teamId;
    loadTeamPlayers();
    DOM.searchModal.style.display = 'none';
    document.querySelector('.nav-link[href="#players"]').click();
    document.querySelector('#players').scrollIntoView({ behavior: 'smooth' });
}

function handleSelectTournament(tournamentId) {
    DOM.tournamentFilter.value = tournamentId;
    loadAllMatches();
    DOM.searchModal.style.display = 'none';
    document.querySelector('.nav-link[href="#matches"]').click();
    document.querySelector('#matches').scrollIntoView({ behavior: 'smooth' });
}

function createMatchCard(match) {
    return `
        <div class="match-card" data-match-id="${match.id}">
            <div class="match-status ${match.status}">${match.status_ar || 'N/A'}</div>
            <div class="match-teams">
                <div class="team-info">
                    <img src="${match.home_team_logo || 'placeholder.png'}" alt="${match.home_team_name}" class="team-logo">
                    <div class="team-name">${match.home_team_name}</div>
                </div>
                <div class="match-score">
                    <div class="score-display">${match.home_score || 0} - ${match.away_score || 0}</div>
                    <div class="score-time">${match.status === 'live' ? 'مباشرة' : formatDate(match.match_date)}</div>
                </div>
                <div class="team-info">
                    <img src="${match.away_team_logo || 'placeholder.png'}" alt="${match.away_team_name}" class="team-logo">
                    <div class="team-name">${match.away_team_name}</div>
                </div>
            </div>
            <div class="match-stats">
                <div class="stat-item">
                    <span class="stat-value">${match.possession_home || 0}%</span>
                    <span class="stat-label">امتلاك</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">${match.shots_on_target_home || 0}</span>
                    <span class="stat-label">رميات</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">${match.possession_away || 0}%</span>
                    <span class="stat-label">امتلاك</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">${match.shots_on_target_away || 0}</span>
                    <span class="stat-label">رميات</span>
                </div>
            </div>
        </div>
    `;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('ar-EG', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getStatusAr(status) {
    const statuses = {
        'live': 'مباشرة',
        'finished': 'انتهت',
        'scheduled': 'قادمة',
        'postponed': 'مؤجلة'
    };
    return statuses[status] || status;
}

function getPositionAr(position) {
    const positions = {
        'GK': 'حارس مرمى',
        'DF': 'مدافع',
        'MF': 'وسط',
        'FW': 'مهاجم'
    };
    return positions[position] || position;
}

function updateFilterSelect(selectElement, data, idField = 'id', nameField = 'name') {
    if (!selectElement || !data) return;
    
    const currentValue = selectElement.value;
    const options = selectElement.querySelectorAll('option:not(:first-child)');
    options.forEach(opt => opt.remove());
    
    data.forEach(item => {
        const option = document.createElement('option');
        option.value = item[idField];
        option.textContent = item[nameField];
        selectElement.appendChild(option);
    });
    
    if (currentValue) {
        selectElement.value = currentValue;
    }
}

function debounce(func, delay) {
    let timeoutId;
    return function(...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
}

// ===== Initialize on DOM Ready =====
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initApp);
} else {
    initApp();
}
