<?php
require_once 'includes/functions.php';
redirectIfLoggedIn();

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi!';
    } else {
        $user = getUserByEmail($email);
        
        if (!$user) {
            $error = 'Email tidak terdaftar!';
        } elseif ($user['status'] !== 'ACTIVE') {
            $error = 'Akun Anda belum diaktivasi. Silakan cek email untuk tautan aktivasi.';
        } elseif (!verifyPassword($password, $user['password'])) {
            $error = 'Password salah!';
        } else {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['full_name'];
            
            header('Location: dashboard.php');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - User Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <h1>Login Admin Gudang</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (isset($_GET['activated'])): ?>
                <div class="alert alert-success">Akun berhasil diaktivasi! Silakan login.</div>
            <?php endif; ?>
            
            <?php if (isset($_GET['reset'])): ?>
                <div class="alert alert-success">Password berhasil direset! Silakan login dengan password baru.</div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            
            <p class="text-center">
                <a href="forgot_password.php">Lupa Password?</a>
            </p>
            
            <p class="text-center">
                Belum punya akun? <a href="register.php">Daftar di sini</a>
            </p>
        </div>
    </div>
</body>
</html>