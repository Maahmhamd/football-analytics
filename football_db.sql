-- قاعدة بيانات تحليل مباريات كرة القدم
-- Football Analytics Database

SET CHARACTER SET utf8mb4;
SET COLLATION_CONNECTION utf8mb4_unicode_ci;

-- حذف قاعدة البيانات إن وجدت
DROP DATABASE IF EXISTS football_analytics;

-- إنشاء قاعدة البيانات
CREATE DATABASE football_analytics CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE football_analytics;

-- ===== جدول البطولات =====
CREATE TABLE tournaments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    country VARCHAR(100),
    type ENUM('league', 'cup', 'international') DEFAULT 'league',
    season INT,
    status ENUM('upcoming', 'ongoing', 'finished') DEFAULT 'upcoming',
    logo_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_season (season),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== جدول الفرق =====
CREATE TABLE teams (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    country VARCHAR(100),
    coach_name VARCHAR(255),
    founded_year INT,
    logo_url VARCHAR(500),
    stadium_name VARCHAR(255),
    stadium_capacity INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_country (country)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== جدول اللاعبين =====
CREATE TABLE players (
    id INT PRIMARY KEY AUTO_INCREMENT,
    team_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    position VARCHAR(50),
    jersey_number INT,
    age INT,
    height DECIMAL(3,2),
    weight INT,
    nationality VARCHAR(100),
    photo_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    INDEX idx_team (team_id),
    INDEX idx_position (position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== جدول المباريات =====
CREATE TABLE matches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tournament_id INT NOT NULL,
    home_team_id INT NOT NULL,
    away_team_id INT NOT NULL,
    match_date DATETIME NOT NULL,
    stadium VARCHAR(255),
    referee_name VARCHAR(255),
    status ENUM('scheduled', 'live', 'finished', 'postponed') DEFAULT 'scheduled',
    home_score INT DEFAULT 0,
    away_score INT DEFAULT 0,
    halftime_home_score INT DEFAULT 0,
    halftime_away_score INT DEFAULT 0,
    possession_home INT DEFAULT 0,
    possession_away INT DEFAULT 0,
    shots_on_target_home INT DEFAULT 0,
    shots_on_target_away INT DEFAULT 0,
    total_shots_home INT DEFAULT 0,
    total_shots_away INT DEFAULT 0,
    fouls_home INT DEFAULT 0,
    fouls_away INT DEFAULT 0,
    yellow_cards_home INT DEFAULT 0,
    yellow_cards_away INT DEFAULT 0,
    red_cards_home INT DEFAULT 0,
    red_cards_away INT DEFAULT 0,
    corners_home INT DEFAULT 0,
    corners_away INT DEFAULT 0,
    offsides_home INT DEFAULT 0,
    offsides_away INT DEFAULT 0,
    video_url VARCHAR(500),
    match_report TEXT,
    attendance INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
    FOREIGN KEY (home_team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (away_team_id) REFERENCES teams(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_match_date (match_date),
    INDEX idx_tournament (tournament_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== جدول أحداث المباراة =====
CREATE TABLE match_events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    match_id INT NOT NULL,
    player_id INT,
    team_id INT NOT NULL,
    event_type ENUM('goal', 'yellow_card', 'red_card', 'substitution', 'injury', 'corner', 'foul', 'offside') NOT NULL,
    minute INT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE SET NULL,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    INDEX idx_match (match_id),
    INDEX idx_event_type (event_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== جدول إحصائيات اللاعب في المباراة =====
CREATE TABLE player_match_stats (
    id INT PRIMARY KEY AUTO_INCREMENT,
    match_id INT NOT NULL,
    player_id INT NOT NULL,
    team_id INT NOT NULL,
    goals INT DEFAULT 0,
    assists INT DEFAULT 0,
    shots INT DEFAULT 0,
    shots_on_target INT DEFAULT 0,
    passes INT DEFAULT 0,
    pass_accuracy DECIMAL(5,2) DEFAULT 0,
    tackles INT DEFAULT 0,
    interceptions INT DEFAULT 0,
    fouls INT DEFAULT 0,
    yellow_cards INT DEFAULT 0,
    red_cards INT DEFAULT 0,
    minutes_played INT DEFAULT 0,
    rating DECIMAL(3,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    INDEX idx_match_player (match_id, player_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== جدول المستخدمين =====
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255),
    avatar_url VARCHAR(500),
    favorite_team_id INT,
    favorite_tournament_id INT,
    role ENUM('user', 'admin', 'analyst') DEFAULT 'user',
    is_active BOOLEAN DEFAULT TRUE,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (favorite_team_id) REFERENCES teams(id),
    FOREIGN KEY (favorite_tournament_id) REFERENCES tournaments(id),
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== جدول التنبيهات والإشعارات =====
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    match_id INT,
    type VARCHAR(50),
    title VARCHAR(255),
    content TEXT,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_is_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== جدول التصنيفات =====
CREATE TABLE standings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tournament_id INT NOT NULL,
    team_id INT NOT NULL,
    position INT,
    played INT DEFAULT 0,
    wins INT DEFAULT 0,
    draws INT DEFAULT 0,
    losses INT DEFAULT 0,
    goals_for INT DEFAULT 0,
    goals_against INT DEFAULT 0,
    goal_difference INT DEFAULT 0,
    points INT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tournament_team (tournament_id, team_id),
    INDEX idx_tournament (tournament_id),
    INDEX idx_position (position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== جدول إحصائيات الموسم =====
CREATE TABLE season_statistics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    player_id INT NOT NULL,
    tournament_id INT NOT NULL,
    goals INT DEFAULT 0,
    assists INT DEFAULT 0,
    yellow_cards INT DEFAULT 0,
    red_cards INT DEFAULT 0,
    matches_played INT DEFAULT 0,
    minutes_played INT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE,
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
    UNIQUE KEY unique_player_tournament (player_id, tournament_id),
    INDEX idx_goals (goals)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== إنشاء Views للتحليلات =====

-- أفضل هدافي الموسم
CREATE VIEW top_scorers AS
SELECT 
    p.id,
    p.name,
    t.name as team_name,
    COUNT(CASE WHEN me.event_type = 'goal' THEN 1 END) as goals,
    COUNT(DISTINCT m.id) as matches_played,
    ROUND(COUNT(CASE WHEN me.event_type = 'goal' THEN 1 END) / COUNT(DISTINCT m.id), 2) as goals_per_match
FROM players p
LEFT JOIN match_events me ON p.id = me.player_id AND me.event_type = 'goal'
LEFT JOIN matches m ON me.match_id = m.id
LEFT JOIN teams t ON p.team_id = t.id
WHERE m.status = 'finished'
GROUP BY p.id, p.name, t.name
ORDER BY goals DESC;

-- أفضل الفرق بالتصنيف
CREATE VIEW best_teams AS
SELECT 
    t.id,
    t.name,
    COUNT(DISTINCT m.id) as matches_played,
    SUM(CASE WHEN m.home_team_id = t.id AND m.home_score > m.away_score 
             OR m.away_team_id = t.id AND m.away_score > m.home_score THEN 1 ELSE 0 END) as wins,
    SUM(CASE WHEN m.home_score = m.away_score THEN 1 ELSE 0 END) as draws,
    SUM(CASE WHEN m.home_team_id = t.id AND m.home_score < m.away_score 
             OR m.away_team_id = t.id AND m.away_score < m.home_score THEN 1 ELSE 0 END) as losses
FROM teams t
LEFT JOIN matches m ON (m.home_team_id = t.id OR m.away_team_id = t.id) AND m.status = 'finished'
GROUP BY t.id, t.name
ORDER BY wins DESC;

-- إنشاء فهارس إضافية للأداء
CREATE INDEX idx_match_home_away ON matches(home_team_id, away_team_id);
CREATE INDEX idx_events_match_type ON match_events(match_id, event_type);
CREATE INDEX idx_stats_player_match ON player_match_stats(player_id, match_id);
