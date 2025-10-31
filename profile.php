<?php
session_start();
require_once 'includes/functions.php';
requireLogin();

$error = '';
$success = '';

// Pastikan session user id ada
$userId = $_SESSION['user_id'] ?? null;
$user = $userId ? getUserById($userId) : null;

// Jika user tidak ditemukan
if (!$user) {
    $error = 'Data pengguna tidak ditemukan. Silakan login kembali.';
}

// Update profile
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $userId) {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'update_profile') {
            $full_name = sanitizeInput($_POST['full_name']);
            $phone = sanitizeInput($_POST['phone']);

            if (empty($full_name)) {
                $error = 'Nama lengkap harus diisi!';
            } else {
                $conn = getDBConnection();
                $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
                $stmt->bind_param("ssi", $full_name, $phone, $userId);

                if ($stmt->execute()) {
                    $_SESSION['user_name'] = $full_name;
                    $success = 'Profil berhasil diupdate!';
                    $user = getUserById($userId);
                } else {
                    $error = 'Gagal mengupdate profil!';
                }

                $stmt->close();
                $conn->close();
            }
        } 
        elseif ($_POST['action'] == 'change_password') {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if (!$user) {
                $error = 'User tidak ditemukan, tidak bisa ubah password.';
            } elseif (empty($current_password) || empty($new_password)) {
                $error = 'Semua field password harus diisi!';
            } elseif (!verifyPassword($current_password, $user['password'])) {
                $error = 'Password saat ini salah!';
            } elseif (strlen($new_password) < 6) {
                $error = 'Password baru minimal 6 karakter!';
            } elseif ($new_password !== $confirm_password) {
                $error = 'Password baru dan konfirmasi tidak cocok!';
            } else {
                $hashed_password = hashPassword($new_password);

                $conn = getDBConnection();
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashed_password, $userId);

                if ($stmt->execute()) {
                    $success = 'Password berhasil diubah!';
                } else {
                    $error = 'Gagal mengubah password!';
                }

                $stmt->close();
                $conn->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - User Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="navbar">
        <div class="nav-content">
            <h2>Dashboard Admin Gudang</h2>
            <div class="nav-user">
                <span>Halo, <?php echo htmlspecialchars($_SESSION['user_name'] ?? ($user['full_name'] ?? 'Pengguna')); ?></span>
                <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="sidebar">
            <ul class="menu">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="products.php">Kelola Produk</a></li>
                <li><a href="profile.php" class="active">Profil Saya</a></li>
            </ul>
        </div>

        <div class="main-content">
            <h1>Profil Saya</h1>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if (!$user): ?>
                <div class="alert alert-error">
                    Data pengguna tidak ditemukan. Silakan <a href="logout.php">login ulang</a>.
                </div>
            <?php else: ?>

            <div class="profile-section">
                <h2>Informasi Profil</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="form-group">
                        <label for="email">Email (Username)</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled>
                        <small>Email tidak dapat diubah</small>
                    </div>

                    <div class="form-group">
                        <label for="full_name">Nama Lengkap *</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">No. Telepon</label>
                        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </form>
            </div>

            <div class="profile-section">
                <h2>Ubah Password</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="change_password">

                    <div class="form-group">
                        <label for="current_password">Password Saat Ini *</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>

                    <div class="form-group">
                        <label for="new_password">Password Baru *</label>
                        <input type="password" id="new_password" name="new_password" required minlength="6">
                        <small>Minimal 6 karakter</small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Password Baru *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                    </div>

                    <button type="submit" class="btn btn-primary">Ubah Password</button>
                </form>
            </div>

            <div class="info-box">
                <h3>Informasi Akun</h3>
                <table class="info-table">
                    <tr>
                        <td><strong>Status Akun:</strong></td>
                        <td><span class="badge badge-success"><?php echo htmlspecialchars($user['status'] ?? '-'); ?></span></td>
                    </tr>
                    <tr>
                        <td><strong>Tanggal Registrasi:</strong></td>
                        <td><?php echo isset($user['created_at']) ? formatDate($user['created_at']) : '-'; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Terakhir Diupdate:</strong></td>
                        <td><?php echo isset($user['updated_at']) ? formatDate($user['updated_at']) : '-'; ?></td>
                    </tr>
                </table>
            </div>

            <?php endif; ?>
        </div>
    </div>
</body>
</html>
