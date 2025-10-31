<?php
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../config/database.php';

// Generate random token
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Send email function (gunakan PHPMailer)
function sendEmail($to, $subject, $message) {
    $mail = new PHPMailer(true);
    try {
        // Konfigurasi SMTP
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;      // misalnya: smtp.gmail.com
        $mail->SMTPAuth   = true;
        $mail->Username   = 'salsachayaa@gmail.com';     // email pengirim
        $mail->Password   = 'jtrm ekdl ovdc gboa';      // password / app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;      // misalnya 587

        // Info pengirim & penerima
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($to);

        // Isi email
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        // Kirim email
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Simpan ke log jika gagal
        $logFile = __DIR__ . '/../email_log.txt';
        $errorMessage = "=== Email Failed at " . date('Y-m-d H:i:s') . " ===\n";
        $errorMessage .= "To: $to\n";
        $errorMessage .= "Subject: $subject\n";
        $errorMessage .= "Error: {$mail->ErrorInfo}\n";
        $errorMessage .= "==========================================\n\n";
        file_put_contents($logFile, $errorMessage, FILE_APPEND);
        return false;
    }
}

// Cek apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_email']);
}

// Redirect jika belum login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Redirect jika sudah login
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header('Location: dashboard.php');
        exit();
    }
}

// Sanitasi input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Validasi email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verifikasi password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Ambil user berdasarkan email
function getUserByEmail($email) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $user;
}

// Ambil user berdasarkan ID
function getUserById($id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $user;
}

// Format mata uang
function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Format tanggal
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}
?>
