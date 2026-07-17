<?php
/**
 * ملف الإعدادات والاتصال بقاعدة البيانات
 * Football Analytics - Database Configuration
 */

// تعيين المنطقة الزمنية
date_default_timezone_set('Africa/Cairo');

// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'football_analytics');

// إعدادات عامة
define('APP_NAME', 'Football Analytics');
define('APP_VERSION', '1.0');
define('PRODUCTION', false); // غير إلى true في الاستضافة الحقيقية

// تفعيل عرض الأخطاء في الوضع التطوير
if (!PRODUCTION) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// محاولة الاتصال بقاعدة البيانات
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // التحقق من الاتصال
    if ($conn->connect_error) {
        throw new Exception('خطأ في الاتصال بقاعدة البيانات: ' . $conn->connect_error);
    }
    
    // تعيين الترميز
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    if (!PRODUCTION) {
        die('خطأ: ' . $e->getMessage());
    } else {
        die('عذراً، حدث خطأ في النظام. يرجى المحاولة لاحقاً.');
    }
}

// دالة لتنظيف المدخلات
function sanitize_input($data) {
    global $conn;
    return $conn->real_escape_string(trim(htmlspecialchars($data)));
}

// دالة للاستجابات JSON
function json_response($success, $message, $data = null, $code = 200) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($code);
    
    $response = [
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// دالة للتعامل مع الأخطاء
function handle_error($message, $code = 500, $data = null) {
    json_response(false, $message, $data, $code);
}

// دالة للنجاح
function handle_success($message, $data = null, $code = 200) {
    json_response(true, $message, $data, $code);
}

// دالة لتسجيل الأخطاء
function log_error($message, $file = 'error_log.txt') {
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message\n";
    error_log($log_message, 3, __DIR__ . '/' . $file);
}

// بدء الجلسة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// دالة للتحقق من تسجيل الدخول
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// دالة للحصول على بيانات المستخدم الحالي
function get_current_user() {
    if (is_logged_in()) {
        global $conn;
        $user_id = $_SESSION['user_id'];
        $query = "SELECT * FROM users WHERE id = ? AND is_active = TRUE";
        
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            $stmt->close();
        }
    }
    return null;
}

// دالة للتوقيع على حماية CSRF
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// دالة للتحقق من رمز CSRF
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ثوابت الأدوار
define('ROLE_USER', 'user');
define('ROLE_ADMIN', 'admin');
define('ROLE_ANALYST', 'analyst');

// دالة للتحقق من الدور
function has_role($required_role) {
    $user = get_current_user();
    if (!$user) return false;
    return $user['role'] === $required_role || $user['role'] === 'admin';
}

// ثوابت أنواع المباريات
define('MATCH_STATUS_SCHEDULED', 'scheduled');
define('MATCH_STATUS_LIVE', 'live');
define('MATCH_STATUS_FINISHED', 'finished');
define('MATCH_STATUS_POSTPONED', 'postponed');

// ثوابت أنواع الأحداث
define('EVENT_GOAL', 'goal');
define('EVENT_YELLOW_CARD', 'yellow_card');
define('EVENT_RED_CARD', 'red_card');
define('EVENT_SUBSTITUTION', 'substitution');

// دالة لحساب فرق الأهداف
function calculate_goal_difference($for, $against) {
    return $for - $against;
}

// دالة لحساب النقاط (3 للفوز، 1 للتعادل، 0 للخسارة)
function calculate_points($wins, $draws, $losses = 0) {
    return ($wins * 3) + $draws;
}

?>
