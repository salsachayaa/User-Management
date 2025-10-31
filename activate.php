<?php
require_once 'includes/functions.php';

$message = '';
$success = false;

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = sanitizeInput($_GET['token']);
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE activation_token = ? AND status = 'PENDING'");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        // Activate account
        $stmt2 = $conn->prepare("UPDATE users SET status = 'ACTIVE', activation_token = NULL WHERE id = ?");
        $stmt2->bind_param("i", $user['id']);
        
        if ($stmt2->execute()) {
            $success = true;
            $message = 'Akun Anda berhasil diaktivasi! Silakan login untuk melanjutkan.';
        } else {
            $message = 'Terjadi kesalahan saat mengaktivasi akun. Silakan coba lagi.';
        }
        $stmt2->close();
    } else {
        $message = 'Token aktivasi tidak valid atau akun sudah diaktivasi.';
    }
    
    $stmt->close();
    $conn->close();
} else {
    $message = 'Token aktivasi tidak ditemukan.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivasi Akun - User Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <h1>Aktivasi Akun</h1>
            
            <div class="alert <?php echo $success ? 'alert-success' : 'alert-error'; ?>">
                <?php echo $message; ?>
            </div>
            
            <?php if ($success): ?>
                <a href="login.php" class="btn btn-primary">Login Sekarang</a>
            <?php else: ?>
                <a href="register.php" class="btn btn-secondary">Kembali ke Registrasi</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>