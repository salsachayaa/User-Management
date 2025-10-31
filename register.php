<?php
require_once 'includes/functions.php';
redirectIfLoggedIn();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($full_name) || empty($email) || empty($password)) {
        $error = 'Semua field wajib diisi!';
    } elseif (!validateEmail($email)) {
        $error = 'Format email tidak valid!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak cocok!';
    } else {
        // Check if email already exists
        $existingUser = getUserByEmail($email);
        if ($existingUser) {
            $error = 'Email sudah terdaftar! Silakan gunakan email lain.';
        } else {
            // Create new user
            $hashed_password = hashPassword($password);
            $activation_token = generateToken();
            
            $conn = getDBConnection();
            $stmt = $conn->prepare("INSERT INTO users (email, password, full_name, phone, activation_token) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $email, $hashed_password, $full_name, $phone, $activation_token);
            
            if ($stmt->execute()) {
                // Send activation email
                $activation_link = BASE_URL . "/activate.php?token=" . $activation_token;
                $email_subject = "Aktivasi Akun - User Management System";
                $email_message = "
                    <html>
                    <body>
                        <h2>Selamat Datang, $full_name!</h2>
                        <p>Terima kasih telah mendaftar sebagai Admin Gudang.</p>
                        <p>Silakan klik tautan di bawah ini untuk mengaktifkan akun Anda:</p>
                        <p><a href='$activation_link'>$activation_link</a></p>
                        <p>Tautan ini akan berlaku selama 24 jam.</p>
                        <p>Jika Anda tidak merasa mendaftar, abaikan email ini.</p>
                        <br>
                        <p>Salam,<br>Tim User Management System</p>
                    </body>
                    </html>
                ";
                
                sendEmail($email, $email_subject, $email_message);
                
                $success = 'Registrasi berhasil! Silakan cek email Anda untuk mengaktifkan akun.';
            } else {
                $error = 'Terjadi kesalahan saat registrasi. Silakan coba lagi.';
            }
            
            $stmt->close();
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - User Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <h1>Registrasi Admin Gudang</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="full_name">Nama Lengkap *</label>
                        <input type="text" id="full_name" name="full_name" required 
                               value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']):''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email (Username) *</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">No. Telepon</label>
                        <input type="text" id="phone" name="phone" 
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" required minlength="6">
                        <small>Minimal 6 karakter</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Daftar</button>
                </form>
            <?php endif; ?>
            
            <p class="text-center">
                Sudah punya akun? <a href="login.php">Login di sini</a>
            </p>
        </div>
    </div>
</body>
</html>