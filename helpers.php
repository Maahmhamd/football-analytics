<?php
/**
 * دوال مساعدة وأداة مساعدة
 * Football Analytics - Helper Functions
 */

require_once 'config.php';

/**
 * ===== دوال المستخدمين =====
 */

/**
 * تسجيل مستخدم جديد
 */
function register_user($username, $email, $password, $full_name) {
    global $conn;
    
    // التحقق من عدم تكرار البريد الإلكتروني
    $check_query = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        return ['success' => false, 'message' => 'البريد الإلكتروني مسجل بالفعل'];
    }
    $stmt->close();
    
    // تشفير كلمة المرور
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    $query = "INSERT INTO users (username, email, password, full_name, is_active, role) 
              VALUES (?, ?, ?, ?, TRUE, 'user')";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $username, $email, $hashed_password, $full_name);
    
    if ($stmt->execute()) {
        $stmt->close();
        return ['success' => true, 'message' => 'تم التسجيل بنجاح'];
    } else {
        $stmt->close();
        return ['success' => false, 'message' => 'خطأ في التسجيل'];
    }
}

/**
 * تسجيل دخول المستخدم
 */
function login_user($email, $password) {
    global $conn;
    
    $query = "SELECT id, username, email, password, role FROM users WHERE email = ? AND is_active = TRUE";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return ['success' => false, 'message' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة'];
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (password_verify($password, $user['password'])) {
        // تسجيل الدخول
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        
        // تحديث وقت آخر تسجيل دخول
        $update_query = "UPDATE users SET last_login = NOW() WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("i", $user['id']);
        $update_stmt->execute();
        $update_stmt->close();
        
        return ['success' => true, 'message' => 'تم تسجيل الدخول بنجاح'];
    } else {
        return ['success' => false, 'message' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة'];
    }
}

/**
 * تسجيل الخروج
 */
function logout_user() {
    session_destroy();
    return ['success' => true, 'message' => 'تم تسجيل الخروج'];
}

/**
 * ===== دوال البيانات =====
 */

/**
 * إضافة مباراة
 */
function add_match($tournament_id, $home_team_id, $away_team_id, $match_date, $stadium, $referee_name) {
    global $conn;
    
    $query = "INSERT INTO matches (tournament_id, home_team_id, away_team_id, match_date, stadium, referee_name, status) 
              VALUES (?, ?, ?, ?, ?, ?, 'scheduled')";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiisss", $tournament_id, $home_team_id, $away_team_id, $match_date, $stadium, $referee_name);
    
    if ($stmt->execute()) {
        $match_id = $stmt->insert_id;
        $stmt->close();
        return ['success' => true, 'message' => 'تم إضافة المباراة', 'match_id' => $match_id];
    } else {
        $stmt->close();
        return ['success' => false, 'message' => 'خطأ في إضافة المباراة'];
    }
}

/**
 * تحديث نتيجة مباراة
 */
function update_match_result($match_id, $home_score, $away_score, $status = 'finished') {
    global $conn;
    
    $query = "UPDATE matches SET home_score = ?, away_score = ?, status = ? WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iisi", $home_score, $away_score, $status, $match_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        update_standings($match_id);
        return ['success' => true, 'message' => 'تم تحديث النتيجة'];
    } else {
        $stmt->close();
        return ['success' => false, 'message' => 'خطأ في تحديث النتيجة'];
    }
}

/**
 * تحديث إحصائيات المباراة
 */
function update_match_statistics($match_id, $home_possession, $home_shots, $away_shots, $home_shots_target, $away_shots_target, $corners_home, $corners_away) {
    global $conn;
    
    $query = "UPDATE matches SET 
              possession_home = ?,
              total_shots_home = ?,
              total_shots_away = ?,
              shots_on_target_home = ?,
              shots_on_target_away = ?,
              possession_away = ?,
              corners_home = ?,
              corners_away = ?
              WHERE id = ?";
    
    $possession_away = 100 - $home_possession;
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiiiiiii", $home_possession, $home_shots, $away_shots, $home_shots_target, $away_shots_target, $possession_away, $corners_home, $corners_away, $match_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        return ['success' => true, 'message' => 'تم تحديث الإحصائيات'];
    } else {
        $stmt->close();
        return ['success' => false, 'message' => 'خطأ في تحديث الإحصائيات'];
    }
}

/**
 * إضافة حدث للمباراة
 */
function add_match_event($match_id, $player_id, $team_id, $event_type, $minute, $description = '') {
    global $conn;
    
    $query = "INSERT INTO match_events (match_id, player_id, team_id, event_type, minute, description) 
              VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiiis", $match_id, $player_id, $team_id, $event_type, $minute, $description);
    
    if ($stmt->execute()) {
        $event_id = $stmt->insert_id;
        $stmt->close();
        
        // تحديث إحصائيات المباراة
        update_match_event_stats($match_id, $event_type, $team_id);
        
        return ['success' => true, 'message' => 'تم تسجيل الحدث', 'event_id' => $event_id];
    } else {
        $stmt->close();
        return ['success' => false, 'message' => 'خطأ في تسجيل الحدث'];
    }
}

/**
 * تحديث إحصائيات الفريق بناءً على الأحداث
 */
function update_match_event_stats($match_id, $event_type, $team_id) {
    global $conn;
    
    $query = "SELECT home_team_id, away_team_id FROM matches WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $match_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    $is_home = ($result['home_team_id'] == $team_id);
    $suffix = $is_home ? '_home' : '_away';
    
    switch($event_type) {
        case 'goal':
            $update = "UPDATE matches SET home_score = home_score + 1 WHERE id = ? AND home_team_id = ?";
            if (!$is_home) {
                $update = "UPDATE matches SET away_score = away_score + 1 WHERE id = ? AND away_team_id = ?";
            }
            break;
        case 'yellow_card':
            $update = "UPDATE matches SET yellow_cards" . $suffix . " = yellow_cards" . $suffix . " + 1 WHERE id = ?";
            break;
        case 'red_card':
            $update = "UPDATE matches SET red_cards" . $suffix . " = red_cards" . $suffix . " + 1 WHERE id = ?";
            break;
        case 'corner':
            $update = "UPDATE matches SET corners" . $suffix . " = corners" . $suffix . " + 1 WHERE id = ?";
            break;
        default:
            return false;
    }
    
    if (strpos($update, '?') && $event_type != 'yellow_card' && $event_type != 'red_card' && $event_type != 'corner') {
        $upd_stmt = $conn->prepare($update);
        $upd_stmt->bind_param("ii", $match_id, $team_id);
    } else {
        $upd_stmt = $conn->prepare($update);
        $upd_stmt->bind_param("i", $match_id);
    }
    
    $upd_stmt->execute();
    $upd_stmt->close();
}

/**
 * تحديث الترتيب بعد انتهاء المباراة
 */
function update_standings($match_id) {
    global $conn;
    
    // الحصول على بيانات المباراة
    $query = "SELECT tournament_id, home_team_id, away_team_id, home_score, away_score FROM matches WHERE id = ? AND status = 'finished'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $match_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return false;
    }
    
    $match = $result->fetch_assoc();
    $stmt->close();
    
    $tournament_id = $match['tournament_id'];
    $home_team_id = $match['home_team_id'];
    $away_team_id = $match['away_team_id'];
    $home_score = $match['home_score'];
    $away_score = $match['away_score'];
    
    // تحديث الفريق الضيف
    if ($home_score > $away_score) {
        // الفريق الرئيسي انتصر
        update_team_standing($tournament_id, $home_team_id, 1, 0, 0, $home_score, $away_score);
        update_team_standing($tournament_id, $away_team_id, 0, 0, 1, $away_score, $home_score);
    } elseif ($away_score > $home_score) {
        // الفريق الضيف انتصر
        update_team_standing($tournament_id, $home_team_id, 0, 0, 1, $home_score, $away_score);
        update_team_standing($tournament_id, $away_team_id, 1, 0, 0, $away_score, $home_score);
    } else {
        // تعادل
        update_team_standing($tournament_id, $home_team_id, 0, 1, 0, $home_score, $away_score);
        update_team_standing($tournament_id, $away_team_id, 0, 1, 0, $away_score, $home_score);
    }
    
    return true;
}

/**
 * تحديث ترتيب فريق واحد
 */
function update_team_standing($tournament_id, $team_id, $wins, $draws, $losses, $goals_for, $goals_against) {
    global $conn;
    
    $points = ($wins * 3) + $draws;
    $goal_difference = $goals_for - $goals_against;
    
    $check_query = "SELECT id FROM standings WHERE tournament_id = ? AND team_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $tournament_id, $team_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        // تحديث
        $update_query = "UPDATE standings SET 
                        played = played + 1,
                        wins = wins + ?,
                        draws = draws + ?,
                        losses = losses + ?,
                        goals_for = goals_for + ?,
                        goals_against = goals_against + ?,
                        goal_difference = goals_for + ? - (goals_against + ?),
                        points = points + ?
                        WHERE tournament_id = ? AND team_id = ?";
        
        $upd_stmt = $conn->prepare($update_query);
        $upd_stmt->bind_param("iiiiiiii", $wins, $draws, $losses, $goals_for, $goals_against, $goals_for, $goals_against, $points, $tournament_id, $team_id);
        $upd_stmt->execute();
        $upd_stmt->close();
    } else {
        $stmt->close();
        // إدراج جديد
        $played = $wins + $draws + $losses;
        $insert_query = "INSERT INTO standings (tournament_id, team_id, position, played, wins, draws, losses, goals_for, goals_against, goal_difference, points)
                        VALUES (?, ?, 1, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $ins_stmt = $conn->prepare($insert_query);
        $ins_stmt->bind_param("iiiiiiii", $tournament_id, $team_id, $played, $wins, $draws, $losses, $goals_for, $goals_against, $goal_difference, $points);
        $ins_stmt->execute();
        $ins_stmt->close();
    }
    
    // تحديث المواضع
    update_standings_positions($tournament_id);
}

/**
 * تحديث مواضع الترتيب
 */
function update_standings_positions($tournament_id) {
    global $conn;
    
    $query = "SELECT id FROM standings WHERE tournament_id = ? ORDER BY points DESC, goal_difference DESC, goals_for DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $tournament_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $position = 1;
    while ($row = $result->fetch_assoc()) {
        $update_query = "UPDATE standings SET position = ? WHERE id = ?";
        $upd_stmt = $conn->prepare($update_query);
        $upd_stmt->bind_param("ii", $position, $row['id']);
        $upd_stmt->execute();
        $upd_stmt->close();
        $position++;
    }
    
    $stmt->close();
}

/**
 * ===== دوال الإشعارات =====
 */

/**
 * إرسال إشعار للمستخدم
 */
function send_notification($user_id, $title, $content, $match_id = null, $type = 'match') {
    global $conn;
    
    $query = "INSERT INTO notifications (user_id, match_id, type, title, content, is_read) 
              VALUES (?, ?, ?, ?, ?, FALSE)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iisss", $user_id, $match_id, $type, $title, $content);
    
    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        $stmt->close();
        return false;
    }
}

/**
 * إرسال إشعارات عندما يتم تسجيل هدف
 */
function notify_goal($match_id, $scorer_name, $team_name, $home_score, $away_score) {
    global $conn;
    
    $title = "⚽ هدف!";
    $content = "$scorer_name سجل لفريق $team_name | النتيجة الآن $home_score - $away_score";
    $type = 'goal';
    
    // إرسال إشعار لمتابعي الفريق
    $query = "SELECT id FROM users WHERE favorite_team_id IN (SELECT home_team_id FROM matches WHERE id = ?) 
              OR favorite_team_id IN (SELECT away_team_id FROM matches WHERE id = ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $match_id, $match_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        send_notification($row['id'], $title, $content, $match_id, $type);
    }
    
    $stmt->close();
}

/**
 * ===== دوال الإحصائيات =====
 */

/**
 * حساب متوسط تقييم اللاعب
 */
function calculate_player_rating($player_id) {
    global $conn;
    
    $query = "SELECT AVG(rating) as avg_rating FROM player_match_stats WHERE player_id = ? AND rating > 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $player_id);
    $stmt->execute();
    
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return $result['avg_rating'] ?? 0;
}

/**
 * حساب نسبة دقة الأهداف
 */
function calculate_shot_accuracy($player_id) {
    global $conn;
    
    $query = "SELECT 
              SUM(shots) as total_shots,
              SUM(shots_on_target) as shots_on_target
              FROM player_match_stats WHERE player_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $player_id);
    $stmt->execute();
    
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($result['total_shots'] > 0) {
        return round(($result['shots_on_target'] / $result['total_shots']) * 100, 2);
    }
    
    return 0;
}

/**
 * ===== دوال النسخ الاحتياطي =====
 */

/**
 * عمل نسخة احتياطية من قاعدة البيانات
 */
function backup_database() {
    global $conn;
    
    $backup_file = 'backups/backup_' . date('Y-m-d_H-i-s') . '.sql';
    
    // إنشاء مجلد النسخ الاحتياطية إن لم يكن موجوداً
    if (!is_dir('backups')) {
        mkdir('backups', 0755, true);
    }
    
    // استخدام mysqldump
    $command = "mysqldump -h " . DB_HOST . " -u " . DB_USER;
    if (!empty(DB_PASS)) {
        $command .= " -p" . DB_PASS;
    }
    $command .= " " . DB_NAME . " > " . $backup_file;
    
    $output = null;
    $return_var = null;
    exec($command, $output, $return_var);
    
    if ($return_var === 0) {
        return ['success' => true, 'message' => 'تم عمل النسخة الاحتياطية', 'file' => $backup_file];
    } else {
        return ['success' => false, 'message' => 'خطأ في عمل النسخة الاحتياطية'];
    }
}

/**
 * حذف النسخ الاحتياطية القديمة
 */
function cleanup_old_backups($days = 30) {
    $backup_dir = 'backups/';
    
    if (!is_dir($backup_dir)) return false;
    
    $files = scandir($backup_dir);
    $count = 0;
    
    foreach ($files as $file) {
        $file_path = $backup_dir . $file;
        $file_time = filemtime($file_path);
        $file_age = (time() - $file_time) / (60 * 60 * 24); // بالأيام
        
        if ($file_age > $days && is_file($file_path)) {
            unlink($file_path);
            $count++;
        }
    }
    
    return $count;
}

?>
