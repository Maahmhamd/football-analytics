<?php
/**
 * API للحصول على البيانات والإحصائيات
 * Football Analytics - Main API
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

require_once 'config.php';

// الحصول على نوع الطلب
$action = isset($_GET['action']) ? sanitize_input($_GET['action']) : '';

// التحقق من الفعل المطلوب
switch($action) {
    
    // ===== المباريات =====
    case 'get_matches':
        get_matches();
        break;
    
    case 'get_match_details':
        get_match_details();
        break;
    
    case 'get_live_matches':
        get_live_matches();
        break;
    
    // ===== البطولات =====
    case 'get_tournaments':
        get_tournaments();
        break;
    
    case 'get_tournament_standings':
        get_tournament_standings();
        break;
    
    // ===== الفرق =====
    case 'get_teams':
        get_teams();
        break;
    
    case 'get_team_details':
        get_team_details();
        break;
    
    // ===== اللاعبون =====
    case 'get_top_scorers':
        get_top_scorers();
        break;
    
    case 'get_player_stats':
        get_player_stats();
        break;
    
    case 'get_team_players':
        get_team_players();
        break;
    
    // ===== الإحصائيات =====
    case 'get_match_statistics':
        get_match_statistics();
        break;
    
    case 'search':
        search();
        break;
    
    default:
        handle_error('الفعل المطلوب غير معروف', 400);
}

// ===== دوال المباريات =====

/**
 * الحصول على قائمة المباريات
 */
function get_matches() {
    global $conn;
    
    $tournament_id = isset($_GET['tournament_id']) ? (int)$_GET['tournament_id'] : 0;
    $status = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    
    $query = "SELECT 
        m.id,
        m.match_date,
        m.status,
        m.home_score,
        m.away_score,
        m.possession_home,
        m.possession_away,
        m.shots_on_target_home,
        m.shots_on_target_away,
        m.stadium,
        m.attendance,
        ht.id as home_team_id,
        ht.name as home_team_name,
        ht.logo_url as home_team_logo,
        at.id as away_team_id,
        at.name as away_team_name,
        at.logo_url as away_team_logo,
        t.id as tournament_id,
        t.name as tournament_name,
        CASE 
            WHEN m.status = 'finished' THEN 'انتهت'
            WHEN m.status = 'live' THEN 'مباشرة'
            WHEN m.status = 'scheduled' THEN 'قادمة'
            WHEN m.status = 'postponed' THEN 'مؤجلة'
        END as status_ar
    FROM matches m
    LEFT JOIN teams ht ON m.home_team_id = ht.id
    LEFT JOIN teams at ON m.away_team_id = at.id
    LEFT JOIN tournaments t ON m.tournament_id = t.id
    WHERE 1=1";
    
    if ($tournament_id > 0) {
        $query .= " AND m.tournament_id = $tournament_id";
    }
    
    if (!empty($status)) {
        $query .= " AND m.status = '$status'";
    }
    
    $query .= " ORDER BY m.match_date DESC LIMIT $limit OFFSET $offset";
    
    $result = $conn->query($query);
    
    if (!$result) {
        handle_error('خطأ في قاعدة البيانات: ' . $conn->error, 500);
    }
    
    $matches = [];
    while ($row = $result->fetch_assoc()) {
        // تحويل التاريخ إلى صيغة قابلة للقراءة
        $row['match_date_formatted'] = format_datetime($row['match_date']);
        $matches[] = $row;
    }
    
    // الحصول على العدد الكلي
    $count_query = "SELECT COUNT(*) as total FROM matches m WHERE 1=1";
    if ($tournament_id > 0) {
        $count_query .= " AND m.tournament_id = $tournament_id";
    }
    if (!empty($status)) {
        $count_query .= " AND m.status = '$status'";
    }
    
    $count_result = $conn->query($count_query);
    $total = $count_result->fetch_assoc()['total'];
    
    handle_success('تم جلب المباريات بنجاح', [
        'matches' => $matches,
        'total' => $total,
        'limit' => $limit,
        'offset' => $offset
    ]);
}

/**
 * الحصول على تفاصيل مباراة محددة
 */
function get_match_details() {
    global $conn;
    
    $match_id = isset($_GET['match_id']) ? (int)$_GET['match_id'] : 0;
    
    if ($match_id <= 0) {
        handle_error('معرف المباراة غير صحيح', 400);
    }
    
    $query = "SELECT 
        m.*,
        ht.name as home_team_name,
        ht.logo_url as home_team_logo,
        ht.stadium_name,
        at.name as away_team_name,
        at.logo_url as away_team_logo,
        t.name as tournament_name,
        CONCAT(ht.name, ' vs ', at.name) as match_title
    FROM matches m
    LEFT JOIN teams ht ON m.home_team_id = ht.id
    LEFT JOIN teams at ON m.away_team_id = at.id
    LEFT JOIN tournaments t ON m.tournament_id = t.id
    WHERE m.id = $match_id";
    
    $result = $conn->query($query);
    
    if ($result->num_rows === 0) {
        handle_error('المباراة غير موجودة', 404);
    }
    
    $match = $result->fetch_assoc();
    
    // الحصول على أحداث المباراة
    $events = get_match_events($match_id);
    
    // الحصول على إحصائيات اللاعبين
    $player_stats_home = get_match_player_stats($match_id, $match['home_team_id']);
    $player_stats_away = get_match_player_stats($match_id, $match['away_team_id']);
    
    $match['events'] = $events;
    $match['player_stats_home'] = $player_stats_home;
    $match['player_stats_away'] = $player_stats_away;
    $match['match_date_formatted'] = format_datetime($match['match_date']);
    
    handle_success('تم جلب تفاصيل المباراة', $match);
}

/**
 * الحصول على أحداث المباراة
 */
function get_match_events($match_id) {
    global $conn;
    
    $query = "SELECT 
        me.id,
        me.minute,
        me.event_type,
        me.description,
        p.name as player_name,
        p.jersey_number,
        t.name as team_name,
        CASE 
            WHEN me.event_type = 'goal' THEN 'هدف'
            WHEN me.event_type = 'yellow_card' THEN 'بطاقة صفراء'
            WHEN me.event_type = 'red_card' THEN 'بطاقة حمراء'
            WHEN me.event_type = 'substitution' THEN 'تغيير'
            WHEN me.event_type = 'injury' THEN 'إصابة'
            WHEN me.event_type = 'corner' THEN 'ركنية'
            WHEN me.event_type = 'foul' THEN 'مخالفة'
            WHEN me.event_type = 'offside' THEN 'تسلل'
        END as event_type_ar
    FROM match_events me
    LEFT JOIN players p ON me.player_id = p.id
    LEFT JOIN teams t ON me.team_id = t.id
    WHERE me.match_id = $match_id
    ORDER BY me.minute ASC";
    
    $result = $conn->query($query);
    $events = [];
    
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    
    return $events;
}

/**
 * الحصول على إحصائيات اللاعبين في المباراة
 */
function get_match_player_stats($match_id, $team_id) {
    global $conn;
    
    $query = "SELECT 
        pms.id,
        pms.goals,
        pms.assists,
        pms.shots,
        pms.shots_on_target,
        pms.passes,
        pms.pass_accuracy,
        pms.tackles,
        pms.interceptions,
        pms.fouls,
        pms.yellow_cards,
        pms.red_cards,
        pms.minutes_played,
        pms.rating,
        p.name as player_name,
        p.jersey_number,
        p.position
    FROM player_match_stats pms
    LEFT JOIN players p ON pms.player_id = p.id
    WHERE pms.match_id = $match_id AND pms.team_id = $team_id
    ORDER BY pms.minutes_played DESC";
    
    $result = $conn->query($query);
    $stats = [];
    
    while ($row = $result->fetch_assoc()) {
        $stats[] = $row;
    }
    
    return $stats;
}

/**
 * الحصول على المباريات المباشرة
 */
function get_live_matches() {
    global $conn;
    
    $query = "SELECT 
        m.id,
        m.match_date,
        m.status,
        m.home_score,
        m.away_score,
        m.possession_home,
        m.possession_away,
        m.shots_on_target_home,
        m.shots_on_target_away,
        ht.name as home_team_name,
        ht.logo_url as home_team_logo,
        at.name as away_team_name,
        at.logo_url as away_team_logo,
        t.name as tournament_name
    FROM matches m
    LEFT JOIN teams ht ON m.home_team_id = ht.id
    LEFT JOIN teams at ON m.away_team_id = at.id
    LEFT JOIN tournaments t ON m.tournament_id = t.id
    WHERE m.status = 'live'
    ORDER BY m.match_date DESC";
    
    $result = $conn->query($query);
    $matches = [];
    
    while ($row = $result->fetch_assoc()) {
        $matches[] = $row;
    }
    
    handle_success('المباريات المباشرة', $matches);
}

// ===== دوال البطولات =====

/**
 * الحصول على قائمة البطولات
 */
function get_tournaments() {
    global $conn;
    
    $query = "SELECT 
        id,
        name,
        country,
        type,
        season,
        status,
        logo_url,
        CASE 
            WHEN type = 'league' THEN 'دوري'
            WHEN type = 'cup' THEN 'كأس'
            WHEN type = 'international' THEN 'دولي'
        END as type_ar
    FROM tournaments
    ORDER BY season DESC";
    
    $result = $conn->query($query);
    $tournaments = [];
    
    while ($row = $result->fetch_assoc()) {
        $tournaments[] = $row;
    }
    
    handle_success('تم جلب البطولات', $tournaments);
}

/**
 * الحصول على جدول الترتيب للبطولة
 */
function get_tournament_standings() {
    global $conn;
    
    $tournament_id = isset($_GET['tournament_id']) ? (int)$_GET['tournament_id'] : 0;
    
    if ($tournament_id <= 0) {
        handle_error('معرف البطولة غير صحيح', 400);
    }
    
    $query = "SELECT 
        s.position,
        s.played,
        s.wins,
        s.draws,
        s.losses,
        s.goals_for,
        s.goals_against,
        s.goal_difference,
        s.points,
        t.id as team_id,
        t.name as team_name,
        t.logo_url
    FROM standings s
    LEFT JOIN teams t ON s.team_id = t.id
    WHERE s.tournament_id = $tournament_id
    ORDER BY s.position ASC";
    
    $result = $conn->query($query);
    $standings = [];
    
    while ($row = $result->fetch_assoc()) {
        $standings[] = $row;
    }
    
    handle_success('جدول الترتيب', $standings);
}

// ===== دوال الفرق =====

/**
 * الحصول على قائمة الفرق
 */
function get_teams() {
    global $conn;
    
    $country = isset($_GET['country']) ? sanitize_input($_GET['country']) : '';
    
    $query = "SELECT 
        id,
        name,
        country,
        coach_name,
        founded_year,
        logo_url,
        stadium_name,
        stadium_capacity
    FROM teams";
    
    if (!empty($country)) {
        $query .= " WHERE country = '$country'";
    }
    
    $query .= " ORDER BY name ASC";
    
    $result = $conn->query($query);
    $teams = [];
    
    while ($row = $result->fetch_assoc()) {
        $teams[] = $row;
    }
    
    handle_success('قائمة الفرق', $teams);
}

/**
 * الحصول على تفاصيل الفريق
 */
function get_team_details() {
    global $conn;
    
    $team_id = isset($_GET['team_id']) ? (int)$_GET['team_id'] : 0;
    
    if ($team_id <= 0) {
        handle_error('معرف الفريق غير صحيح', 400);
    }
    
    $query = "SELECT * FROM teams WHERE id = $team_id";
    $result = $conn->query($query);
    
    if ($result->num_rows === 0) {
        handle_error('الفريق غير موجود', 404);
    }
    
    $team = $result->fetch_assoc();
    
    // الحصول على اللاعبين
    $players = get_team_players($team_id);
    
    // الحصول على آخر المباريات
    $recent_matches = get_team_recent_matches($team_id);
    
    $team['players'] = $players;
    $team['recent_matches'] = $recent_matches;
    
    handle_success('تفاصيل الفريق', $team);
}

// ===== دوال اللاعبين =====

/**
 * الحصول على أفضل هدافي الموسم
 */
function get_top_scorers() {
    global $conn;
    
    $tournament_id = isset($_GET['tournament_id']) ? (int)$_GET['tournament_id'] : 0;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    
    $query = "SELECT 
        ss.id,
        ss.goals,
        ss.assists,
        ss.matches_played,
        ROUND(ss.goals / ss.matches_played, 2) as goals_per_match,
        p.id as player_id,
        p.name,
        p.position,
        p.photo_url,
        t.name as team_name,
        t.logo_url as team_logo
    FROM season_statistics ss
    LEFT JOIN players p ON ss.player_id = p.id
    LEFT JOIN teams t ON p.team_id = t.id";
    
    if ($tournament_id > 0) {
        $query .= " WHERE ss.tournament_id = $tournament_id";
    }
    
    $query .= " ORDER BY ss.goals DESC LIMIT $limit";
    
    $result = $conn->query($query);
    $scorers = [];
    
    while ($row = $result->fetch_assoc()) {
        $scorers[] = $row;
    }
    
    handle_success('أفضل الهدافين', $scorers);
}

/**
 * الحصول على إحصائيات اللاعب
 */
function get_player_stats() {
    global $conn;
    
    $player_id = isset($_GET['player_id']) ? (int)$_GET['player_id'] : 0;
    
    if ($player_id <= 0) {
        handle_error('معرف اللاعب غير صحيح', 400);
    }
    
    $query = "SELECT 
        p.*,
        t.name as team_name,
        t.logo_url as team_logo,
        COUNT(DISTINCT m.id) as matches_played,
        SUM(CASE WHEN me.event_type = 'goal' THEN 1 ELSE 0 END) as total_goals
    FROM players p
    LEFT JOIN teams t ON p.team_id = t.id
    LEFT JOIN player_match_stats m ON p.id = m.player_id
    LEFT JOIN match_events me ON p.id = me.player_id
    WHERE p.id = $player_id
    GROUP BY p.id";
    
    $result = $conn->query($query);
    
    if ($result->num_rows === 0) {
        handle_error('اللاعب غير موجود', 404);
    }
    
    $player = $result->fetch_assoc();
    
    handle_success('إحصائيات اللاعب', $player);
}

/**
 * الحصول على لاعبي الفريق
 */
function get_team_players($team_id = 0) {
    global $conn;
    
    if ($team_id == 0) {
        $team_id = isset($_GET['team_id']) ? (int)$_GET['team_id'] : 0;
    }
    
    if ($team_id <= 0) {
        handle_error('معرف الفريق غير صحيح', 400);
    }
    
    $query = "SELECT 
        id,
        name,
        position,
        jersey_number,
        age,
        height,
        weight,
        nationality,
        photo_url
    FROM players
    WHERE team_id = $team_id
    ORDER BY jersey_number ASC";
    
    $result = $conn->query($query);
    $players = [];
    
    while ($row = $result->fetch_assoc()) {
        $players[] = $row;
    }
    
    if ($team_id > 0 && !isset($_GET['team_id'])) {
        return $players;
    }
    
    handle_success('لاعبو الفريق', $players);
}

/**
 * الحصول على مباريات الفريق الأخيرة
 */
function get_team_recent_matches($team_id) {
    global $conn;
    
    $query = "SELECT 
        m.id,
        m.match_date,
        m.home_score,
        m.away_score,
        m.status,
        ht.name as home_team_name,
        ht.logo_url as home_team_logo,
        at.name as away_team_name,
        at.logo_url as away_team_logo,
        t.name as tournament_name
    FROM matches m
    LEFT JOIN teams ht ON m.home_team_id = ht.id
    LEFT JOIN teams at ON m.away_team_id = at.id
    LEFT JOIN tournaments t ON m.tournament_id = t.id
    WHERE (m.home_team_id = $team_id OR m.away_team_id = $team_id)
    AND m.status = 'finished'
    ORDER BY m.match_date DESC
    LIMIT 5";
    
    $result = $conn->query($query);
    $matches = [];
    
    while ($row = $result->fetch_assoc()) {
        $matches[] = $row;
    }
    
    return $matches;
}

// ===== دوال الإحصائيات =====

/**
 * الحصول على إحصائيات المباراة
 */
function get_match_statistics() {
    global $conn;
    
    $match_id = isset($_GET['match_id']) ? (int)$_GET['match_id'] : 0;
    
    if ($match_id <= 0) {
        handle_error('معرف المباراة غير صحيح', 400);
    }
    
    $query = "SELECT 
        m.id,
        m.home_score,
        m.away_score,
        m.possession_home,
        m.possession_away,
        m.shots_on_target_home,
        m.shots_on_target_away,
        m.total_shots_home,
        m.total_shots_away,
        m.fouls_home,
        m.fouls_away,
        m.yellow_cards_home,
        m.yellow_cards_away,
        m.red_cards_home,
        m.red_cards_away,
        m.corners_home,
        m.corners_away,
        m.offsides_home,
        m.offsides_away,
        ht.name as home_team_name,
        at.name as away_team_name
    FROM matches m
    LEFT JOIN teams ht ON m.home_team_id = ht.id
    LEFT JOIN teams at ON m.away_team_id = at.id
    WHERE m.id = $match_id";
    
    $result = $conn->query($query);
    
    if ($result->num_rows === 0) {
        handle_error('المباراة غير موجودة', 404);
    }
    
    $stats = $result->fetch_assoc();
    
    handle_success('إحصائيات المباراة', $stats);
}

/**
 * البحث في البيانات
 */
function search() {
    global $conn;
    
    $query = isset($_GET['q']) ? sanitize_input($_GET['q']) : '';
    
    if (strlen($query) < 2) {
        handle_error('يجب إدخال على الأقل حرفين', 400);
    }
    
    $results = [
        'teams' => [],
        'players' => [],
        'tournaments' => []
    ];
    
    // البحث عن الفرق
    $team_query = "SELECT id, name, logo_url, 'team' as type FROM teams WHERE name LIKE '%$query%' LIMIT 5";
    $team_result = $conn->query($team_query);
    while ($row = $team_result->fetch_assoc()) {
        $results['teams'][] = $row;
    }
    
    // البحث عن اللاعبين
    $player_query = "SELECT p.id, p.name, p.position, p.photo_url, t.name as team_name, 'player' as type 
                     FROM players p 
                     LEFT JOIN teams t ON p.team_id = t.id 
                     WHERE p.name LIKE '%$query%' LIMIT 5";
    $player_result = $conn->query($player_query);
    while ($row = $player_result->fetch_assoc()) {
        $results['players'][] = $row;
    }
    
    // البحث عن البطولات
    $tournament_query = "SELECT id, name, logo_url, 'tournament' as type FROM tournaments WHERE name LIKE '%$query%' LIMIT 5";
    $tournament_result = $conn->query($tournament_query);
    while ($row = $tournament_result->fetch_assoc()) {
        $results['tournaments'][] = $row;
    }
    
    handle_success('نتائج البحث', $results);
}

// ===== دوال مساعدة =====

/**
 * تنسيق التاريخ والوقت
 */
function format_datetime($datetime) {
    if (empty($datetime)) return '';
    
    $date = new DateTime($datetime);
    return $date->format('Y-m-d H:i:s');
}

// معالج الأخطاء الافتراضي
if (empty($action)) {
    handle_error('لا توجد عملية محددة', 400);
}

?>
