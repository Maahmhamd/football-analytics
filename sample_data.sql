-- ===== بيانات نموذجية للاختبار =====
-- Football Analytics - Sample Data

USE football_analytics;

-- ===== إدراج البطولات =====
INSERT INTO tournaments (name, country, type, season, status, logo_url) VALUES
('الدوري المصري الممتاز', 'مصر', 'league', 2024, 'ongoing', 'https://via.placeholder.com/100?text=EGY'),
('الدوري السعودي', 'السعودية', 'league', 2024, 'ongoing', 'https://via.placeholder.com/100?text=KSA'),
('دوري الإمارات', 'الإمارات', 'league', 2024, 'ongoing', 'https://via.placeholder.com/100?text=UAE'),
('كأس مصر', 'مصر', 'cup', 2024, 'ongoing', 'https://via.placeholder.com/100?text=CUP'),
('دوري أبطال آسيا', 'آسيا', 'international', 2024, 'ongoing', 'https://via.placeholder.com/100?text=AFC'),
('كأس العالم', 'العالم', 'international', 2024, 'upcoming', 'https://via.placeholder.com/100?text=WORLD');

-- ===== إدراج الفرق المصرية =====
INSERT INTO teams (name, country, coach_name, founded_year, logo_url, stadium_name, stadium_capacity) VALUES
('الأهلي', 'مصر', 'الحمد لله', 1924, 'https://via.placeholder.com/80?text=AHLY', 'الدولي بالقاهرة', 74100),
('الزمالك', 'مصر', 'إيهاب جلال', 1911, 'https://via.placeholder.com/80?text=ZAMALEK', 'استاد السلام', 30000),
('الإسماعيلي', 'مصر', 'خالد إسماعيل', 1924, 'https://via.placeholder.com/80?text=ISMAILI', 'ملعب الإسماعيلية', 18000),
('بيراميدز', 'مصر', 'عماد متعب', 2008, 'https://via.placeholder.com/80?text=PYRAMIDS', 'ملعب برج العرب', 30000),
('الفيصلي', 'مصر', 'محمود جادو', 1993, 'https://via.placeholder.com/80?text=FAYENOORD', 'ملعب السويس', 15000),
('الترجي', 'مصر', 'سيد عبد الحفيظ', 1946, 'https://via.placeholder.com/80?text=TARGE', 'ملعب استاد السويس', 20000);

-- ===== إدراج لاعبين من الأهلي =====
INSERT INTO players (team_id, name, position, jersey_number, age, height, weight, nationality, photo_url) VALUES
(1, 'محمد الشناوي', 'GK', 1, 35, 1.88, 82, 'مصر', 'https://via.placeholder.com/80?text=SHENAWY'),
(1, 'علي معلول', 'DF', 3, 32, 1.82, 78, 'تونس', 'https://via.placeholder.com/80?text=MAALOUL'),
(1, 'محمد هاني', 'DF', 4, 29, 1.86, 80, 'مصر', 'https://via.placeholder.com/80?text=HANI'),
(1, 'أحمد فتحي', 'DF', 5, 31, 1.84, 79, 'مصر', 'https://via.placeholder.com/80?text=FATHY'),
(1, 'محمد النني', 'MF', 6, 30, 1.82, 76, 'مصر', 'https://via.placeholder.com/80?text=ELNENI'),
(1, 'أحمد دعيع', 'MF', 7, 28, 1.80, 74, 'مصر', 'https://via.placeholder.com/80?text=DUIA'),
(1, 'محمود كهربا', 'MF', 8, 31, 1.84, 77, 'مصر', 'https://via.placeholder.com/80?text=KAHRABA'),
(1, 'أليو ديانج', 'FW', 9, 27, 1.90, 85, 'السنغال', 'https://via.placeholder.com/80?text=DIENG'),
(1, 'أحمد الشيخ', 'FW', 10, 29, 1.82, 76, 'مصر', 'https://via.placeholder.com/80?text=SHEIKH'),
(1, 'تريزيجيه', 'FW', 11, 26, 1.85, 78, 'مصر', 'https://via.placeholder.com/80?text=TREZEGUET');

-- ===== إدراج لاعبين من الزمالك =====
INSERT INTO players (team_id, name, position, jersey_number, age, height, weight, nationality, photo_url) VALUES
(2, 'محمود جنش', 'GK', 1, 33, 1.88, 82, 'مصر', 'https://via.placeholder.com/80?text=GENESH'),
(2, 'أحمد حجازي', 'DF', 3, 32, 1.84, 80, 'مصر', 'https://via.placeholder.com/80?text=HEGAZY'),
(2, 'أحمد عبدالقادر', 'DF', 4, 28, 1.82, 77, 'مصر', 'https://via.placeholder.com/80?text=ABDULKADER'),
(2, 'إمام عاشور', 'MF', 6, 26, 1.79, 73, 'مصر', 'https://via.placeholder.com/80?text=ASHOUR'),
(2, 'عمرو وردة', 'MF', 7, 30, 1.83, 76, 'مصر', 'https://via.placeholder.com/80?text=WARDA'),
(2, 'أحمد سيد زيزو', 'FW', 9, 28, 1.85, 78, 'مصر', 'https://via.placeholder.com/80?text=ZIZO'),
(2, 'خالد بويراز', 'FW', 10, 24, 1.84, 77, 'الجزائر', 'https://via.placeholder.com/80?text=BOURAZAN');

-- ===== إدراج المباريات =====
INSERT INTO matches (tournament_id, home_team_id, away_team_id, match_date, stadium, referee_name, status, home_score, away_score, halftime_home_score, halftime_away_score, possession_home, possession_away, shots_on_target_home, shots_on_target_away, total_shots_home, total_shots_away, fouls_home, fouls_away, yellow_cards_home, yellow_cards_away, red_cards_home, red_cards_away, corners_home, corners_away, attendance) VALUES

-- مباريات مصرية
(1, 1, 2, '2024-01-15 19:00:00', 'الدولي بالقاهرة', 'إبراهيم نور الدين', 'finished', 2, 1, 1, 1, 58, 42, 6, 3, 15, 10, 8, 10, 2, 1, 0, 0, 5, 3, 74000),
(1, 3, 4, '2024-01-15 21:00:00', 'ملعب الإسماعيلية', 'أحمد الشناوي', 'finished', 1, 1, 0, 0, 50, 50, 4, 4, 12, 11, 7, 6, 1, 1, 0, 0, 4, 4, 15000),
(1, 1, 3, '2024-01-20 19:30:00', 'الدولي بالقاهرة', 'شريف طه', 'finished', 3, 0, 2, 0, 62, 38, 8, 1, 18, 7, 5, 12, 1, 2, 0, 0, 6, 2, 60000),
(1, 2, 5, '2024-01-20 21:00:00', 'استاد السلام', 'علاء الدين الشرقاوي', 'finished', 2, 0, 1, 0, 55, 45, 5, 2, 14, 9, 6, 8, 2, 1, 0, 0, 3, 1, 25000),

-- مباريات قادمة
(1, 4, 6, '2024-02-01 19:00:00', 'ملعب برج العرب', 'محمد عبدالفضيل', 'scheduled', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(1, 1, 5, '2024-02-05 20:00:00', 'الدولي بالقاهرة', 'أسامة الشرقاوي', 'scheduled', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),

-- مباريات مباشرة
(1, 2, 3, '2024-01-25 19:00:00', 'استاد السلام', 'إبراهيم الشامي', 'live', 1, 1, 1, 0, 52, 48, 3, 2, 11, 8, 4, 5, 1, 1, 0, 0, 2, 1, 20000),
(1, 1, 4, '2024-01-25 21:00:00', 'الدولي بالقاهرة', 'محمد عزت', 'live', 2, 0, 1, 0, 65, 35, 5, 1, 16, 5, 6, 9, 2, 2, 0, 0, 7, 1, 55000);

-- ===== إدراج أحداث المباريات =====
INSERT INTO match_events (match_id, player_id, team_id, event_type, minute, description) VALUES
-- أحداث المباراة الأولى (الأهلي vs الزمالك)
(1, 8, 1, 'goal', 20, 'هدف جميل من ديانج'),
(1, 9, 1, 'goal', 45, 'تسجيل الهدف الثاني من أحمد الشيخ'),
(1, 7, 2, 'goal', 60, 'هدف كهربا لتقليل الفرق'),
(1, 4, 1, 'yellow_card', 35, 'بطاقة صفراء'),
(1, 5, 2, 'yellow_card', 42, 'بطاقة صفراء'),
(1, 6, 1, 'substitution', 70, 'تغيير من الملعب'),

-- أحداث المباراة الثالثة (الأهلي vs الإسماعيلي)
(3, 8, 1, 'goal', 15, 'تسجيل الهدف الأول'),
(3, 9, 1, 'goal', 35, 'هدف من أحمد الشيخ'),
(3, 10, 1, 'goal', 50, 'هدف من تريزيجيه'),
(3, 3, 3, 'yellow_card', 25, 'بطاقة صفراء'),
(3, 1, 3, 'yellow_card', 55, 'بطاقة صفراء');

-- ===== إدراج إحصائيات اللاعبين في المباريات =====
INSERT INTO player_match_stats (match_id, player_id, team_id, goals, assists, shots, shots_on_target, passes, pass_accuracy, tackles, interceptions, fouls, yellow_cards, red_cards, minutes_played, rating) VALUES
-- إحصائيات مباراة الأهلي vs الزمالك
(1, 1, 1, 0, 0, 0, 0, 35, 95.5, 0, 0, 0, 0, 0, 90, 7.0),
(1, 8, 1, 1, 0, 5, 3, 45, 88.0, 1, 0, 2, 0, 0, 90, 8.5),
(1, 9, 1, 1, 1, 6, 4, 38, 85.0, 0, 1, 1, 0, 0, 90, 8.0),
(1, 10, 1, 0, 1, 4, 2, 32, 82.0, 2, 1, 2, 0, 0, 75, 7.5),
(1, 11, 2, 1, 0, 4, 2, 40, 87.0, 1, 0, 1, 0, 0, 90, 7.0),
(1, 6, 2, 0, 0, 2, 1, 48, 90.0, 3, 2, 2, 1, 0, 90, 7.0);

-- ===== إدراج الترتيب =====
INSERT INTO standings (tournament_id, team_id, position, played, wins, draws, losses, goals_for, goals_against, goal_difference, points) VALUES
(1, 1, 1, 4, 3, 0, 1, 8, 2, 6, 9),
(1, 2, 2, 4, 2, 1, 1, 6, 3, 3, 7),
(1, 3, 3, 4, 1, 2, 1, 5, 5, 0, 5),
(1, 4, 4, 3, 1, 1, 1, 3, 3, 0, 4),
(1, 5, 5, 3, 0, 2, 1, 2, 4, -2, 2),
(1, 6, 6, 3, 0, 0, 3, 1, 8, -7, 0);

-- ===== إدراج إحصائيات الموسم =====
INSERT INTO season_statistics (player_id, tournament_id, goals, assists, yellow_cards, red_cards, matches_played, minutes_played) VALUES
(8, 1, 5, 2, 1, 0, 4, 360),
(9, 1, 4, 3, 1, 0, 4, 330),
(10, 1, 2, 2, 2, 0, 3, 270),
(11, 1, 3, 1, 0, 0, 4, 360),
(14, 1, 2, 1, 1, 0, 4, 360),
(15, 1, 1, 2, 0, 0, 4, 300);

-- ===== إدراج مستخدمين =====
INSERT INTO users (username, email, password, full_name, favorite_team_id, favorite_tournament_id, role, is_active) VALUES
('admin', 'admin@example.com', SHA2('admin123', 256), 'المسؤول', 1, 1, 'admin', TRUE),
('user1', 'user1@example.com', SHA2('password123', 256), 'مستخدم عادي', 1, 1, 'user', TRUE),
('analyst1', 'analyst@example.com', SHA2('analyst123', 256), 'محلل البيانات', 2, 1, 'analyst', TRUE);

-- التحقق من البيانات
SELECT COUNT(*) as total_tournaments FROM tournaments;
SELECT COUNT(*) as total_teams FROM teams;
SELECT COUNT(*) as total_players FROM players;
SELECT COUNT(*) as total_matches FROM matches;
SELECT COUNT(*) as total_standings FROM standings;

COMMIT;
