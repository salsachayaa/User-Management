<?php
require_once 'includes/functions.php';

$error = '';
$success = '';
$valid_token = false;
$token = '';

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = sanitizeInput($_GET['token']);
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        $valid_token = true;
    } else {
        $error = 'Token reset password tidak valid atau sudah kadaluarsa.';
    }
    
    $stmt->close();
    $conn->close();
} else {
    $error = 'Token reset password tidak ditemukan.';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $valid_token) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($password)) {
        $error = 'Password harus diisi!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak cocok!';
    } else {
        $hashed_password = hashPassword($password);
        
        $conn = getDBConnection();
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?");
        $stmt->bind_param("ss", $hashed_password, $token);
        
        if ($stmt->execute()) {
            $success = true;
        } else {
            $error = 'Terjadi kesalahan. Silakan coba lagi.';
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - User Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <h1>Reset Password</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">Password berhasil direset! Silakan login dengan password baru.</div>
                <a href="login.php" class="btn btn-primary">Login Sekarang</a>
            <?php elseif ($valid_token): ?>
                <p>Masukkan password baru untuk akun Anda.</p>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="password">Password Baru</label>
                        <input type="password" id="password" name="password" required minlength="6">
                        <small>Minimal 6 karakter</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Password Baru</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </form>
            <?php else: ?>
                <a href="forgot_password.php" class="btn btn-secondary">Minta Tautan Reset Baru</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>