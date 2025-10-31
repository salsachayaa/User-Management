<?php
session_start();
require_once 'includes/functions.php';
requireLogin();

// Ambil user_id dari session (jika ada)
$userId = $_SESSION['user_id'] ?? null;

// Ambil data user jika login
$user = $userId ? getUserById($userId) : null;

// Ambil koneksi database
$conn = getDBConnection();

// --- Total products ---
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM products WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result ? $result->fetch_assoc() : null;
$total_products = isset($row['total']) ? (int)$row['total'] : 0;
$stmt->close();

// --- Total quantity ---
$stmt = $conn->prepare("SELECT SUM(quantity) AS total FROM products WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result ? $result->fetch_assoc() : null;
$total_quantity = isset($row['total']) ? (int)$row['total'] : 0;
$stmt->close();

// --- Total value ---
$stmt = $conn->prepare("SELECT SUM(quantity * price) AS total FROM products WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result ? $result->fetch_assoc() : null;
$total_value = isset($row['total']) ? (float)$row['total'] : 0;
$stmt->close();

// --- Total categories ---
$stmt = $conn->prepare("SELECT COUNT(DISTINCT category) AS total FROM products WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result ? $result->fetch_assoc() : null;
$total_categories = isset($row['total']) ? (int)$row['total'] : 0;
$stmt->close();

$conn->close();

// Tentukan nama untuk sapaan
$displayName = is_array($user) && !empty($user['full_name']) ? $user['full_name'] : ($_SESSION['user_name'] ?? 'Pengguna');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - User Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="navbar">
        <div class="nav-content">
            <h2>Dashboard Admin Gudang</h2>
            <div class="nav-user">
                <span>Halo, <?php echo htmlspecialchars($displayName); ?></span>
                <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="sidebar">
            <ul class="menu">
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="products.php">Kelola Produk</a></li>
                <li><a href="profile.php">Profil Saya</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <h1>Selamat Datang, <?php echo htmlspecialchars($displayName); ?>!</h1>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-content">
                        <h3><?php echo $total_products; ?></h3>
                        <p>Total Produk</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-content">
                        <h3><?php echo number_format($total_quantity); ?></h3>
                        <p>Total Kuantitas</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-content">
                        <h3><?php echo formatCurrency($total_value); ?></h3>
                        <p>Total Nilai Inventori</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🏷️</div>
                    <div class="stat-content">
                        <h3><?php echo $total_categories; ?></h3>
                        <p>Kategori Produk</p>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-actions">
                <a href="products.php?action=create" class="btn btn-primary">Tambah Produk Baru</a>
                <a href="products.php" class="btn btn-secondary">Lihat Semua Produk</a>
            </div>
            
            <div class="info-box">
                <h3>Informasi Akun</h3>
                <table class="info-table">
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td><?php echo htmlspecialchars($user['email'] ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Nama:</strong></td>
                        <td><?php echo htmlspecialchars($user['full_name'] ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Telepon:</strong></td>
                        <td><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td><span class="badge badge-success"><?php echo htmlspecialchars($user['status'] ?? '-'); ?></span></td>
                    </tr>
                    <tr>
                        <td><strong>Terdaftar:</strong></td>
                        <td><?php echo isset($user['created_at']) ? formatDate($user['created_at']) : '-'; ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
