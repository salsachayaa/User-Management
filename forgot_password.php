<?php
require_once 'includes/functions.php';
redirectIfLoggedIn();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitizeInput($_POST['email']);
    
    if (empty($email)) {
        $error = 'Email harus diisi!';
    } elseif (!validateEmail($email)) {
        $error = 'Format email tidak valid!';
    } else {
        $user = getUserByEmail($email);
        
        if ($user && $user['status'] == 'ACTIVE') {
            // Generate reset token
            $reset_token = generateToken();
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $conn = getDBConnection();
            $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
            $stmt->bind_param("ssi", $reset_token, $expiry, $user['id']);
            
            if ($stmt->execute()) {
                // Send reset email
                $reset_link = BASE_URL . "/reset_password.php?token=" . $reset_token;
                $email_subject = "Reset Password - User Management System";
                $email_message = "
                    <html>
                    <body>
                        <h2>Reset Password</h2>
                        <p>Halo, {$user['full_name']}!</p>
                        <p>Kami menerima permintaan untuk mereset password akun Anda.</p>
                        <p>Silakan klik tautan di bawah ini untuk membuat password baru:</p>
                        <p><a href='$reset_link'>$reset_link</a></p>
                        <p>Tautan ini akan berlaku selama 1 jam.</p>
                        <p>Jika Anda tidak meminta reset password, abaikan email ini.</p>
                        <br>
                        <p>Salam,<br>Tim User Management System</p>
                    </body>
                    </html>
                ";
                
                sendEmail($email, $email_subject, $email_message);
                
                $success = 'Tautan reset password telah dikirim ke email Anda. Silakan cek inbox atau folder spam.';
            } else {
                $error = 'Terjadi kesalahan. Silakan coba lagi.';
            }
            
            $stmt->close();
            $conn->close();
        } else {
            // Untuk keamanan, tampilkan pesan yang sama meskipun email tidak ditemukan
            $success = 'Jika email terdaftar, tautan reset password telah dikirim ke email Anda.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - User Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <h1>Lupa Password</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php else: ?>
                <p>Masukkan email Anda untuk menerima tautan reset password.</p>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Kirim Tautan Reset</button>
                </form>
            <?php endif; ?>
            
            <p class="text-center">
                <a href="login.php">Kembali ke Login</a>
            </p>
        </div>
    </div>
</body>
</html>