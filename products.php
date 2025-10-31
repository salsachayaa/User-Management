<?php
require_once 'includes/functions.php';
requireLogin();

$action = $_GET['action'] ?? 'list';
$error = '';
$success = '';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $conn = getDBConnection();
        
        if ($_POST['action'] == 'create') {
            $product_code = sanitizeInput($_POST['product_code']);
            $product_name = sanitizeInput($_POST['product_name']);
            $category = sanitizeInput($_POST['category']);
            $description = sanitizeInput($_POST['description']);
            $quantity = intval($_POST['quantity']);
            $unit = sanitizeInput($_POST['unit']);
            $price = floatval($_POST['price']);
            $location = sanitizeInput($_POST['location']);
            
            // Check if product code exists
            $stmt = $conn->prepare("SELECT id FROM products WHERE product_code = ?");
            $stmt->bind_param("s", $product_code);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = 'Kode produk sudah digunakan!';
            } else {
                $stmt = $conn->prepare("INSERT INTO products (user_id, product_code, product_name, category, description, quantity, unit, price, location) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issssisds", $_SESSION['user_id'], $product_code, $product_name, $category, $description, $quantity, $unit, $price, $location);
                
                if ($stmt->execute()) {
                    $success = 'Produk berhasil ditambahkan!';
                    $action = 'list';
                } else {
                    $error = 'Gagal menambahkan produk!';
                }
            }
            $stmt->close();
        }
        elseif ($_POST['action'] == 'update') {
            $id = intval($_POST['id']);
            $product_name = sanitizeInput($_POST['product_name']);
            $category = sanitizeInput($_POST['category']);
            $description = sanitizeInput($_POST['description']);
            $quantity = intval($_POST['quantity']);
            $unit = sanitizeInput($_POST['unit']);
            $price = floatval($_POST['price']);
            $location = sanitizeInput($_POST['location']);
            
            $stmt = $conn->prepare("UPDATE products SET product_name = ?, category = ?, description = ?, quantity = ?, unit = ?, price = ?, location = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("sssisdiii", $product_name, $category, $description, $quantity, $unit, $price, $location, $id, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $success = 'Produk berhasil diupdate!';
                $action = 'list';
            } else {
                $error = 'Gagal mengupdate produk!';
            }
            $stmt->close();
        }
        elseif ($_POST['action'] == 'delete') {
            $id = intval($_POST['id']);
            
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $success = 'Produk berhasil dihapus!';
            } else {
                $error = 'Gagal menghapus produk!';
            }
            $stmt->close();
        }
        
        $conn->close();
    }
}

// Get products list
$products = [];
if ($action == 'list' || $action == '') {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM products WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
    $conn->close();
}

// Get product for edit
$product = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - User Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="navbar">
        <div class="nav-content">
            <h2>Dashboard Admin Gudang</h2>
            <div class="nav-user">
                <span>Halo, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="sidebar">
            <ul class="menu">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="products.php" class="active">Kelola Produk</a></li>
                <li><a href="profile.php">Profil Saya</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($action == 'create'): ?>
                <h1>Tambah Produk Baru</h1>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="product_code">Kode Produk *</label>
                            <input type="text" id="product_code" name="product_code" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="product_name">Nama Produk *</label>
                            <input type="text" id="product_name" name="product_name" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="category">Kategori</label>
                            <input type="text" id="category" name="category">
                        </div>
                        
                        <div class="form-group">
                            <label for="location">Lokasi</label>
                            <input type="text" id="location" name="location">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <textarea id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="quantity">Kuantitas *</label>
                            <input type="number" id="quantity" name="quantity" value="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="unit">Satuan</label>
                            <input type="text" id="unit" name="unit" value="pcs">
                        </div>
                        
                        <div class="form-group">
                            <label for="price">Harga (Rp)</label>
                            <input type="number" id="price" name="price" step="0.01" value="0">
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="products.php" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
                
            <?php elseif ($action == 'edit' && $product): ?>
                <h1>Edit Produk</h1>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="product_code">Kode Produk</label>
                            <input type="text" id="product_code" value="<?php echo htmlspecialchars($product['product_code']); ?>" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label for="product_name">Nama Produk *</label>
                            <input type="text" id="product_name" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="category">Kategori</label>
                            <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($product['category']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="location">Lokasi</label>
                            <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($product['location']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <textarea id="description" name="description" rows="3"><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="quantity">Kuantitas *</label>
                            <input type="number" id="quantity" name="quantity" value="<?php echo $product['quantity']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="unit">Satuan</label>
                            <input type="text" id="unit" name="unit" value="<?php echo htmlspecialchars($product['unit']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="price">Harga (Rp)</label>
                            <input type="number" id="price" name="price" step="0.01" value="<?php echo $product['price']; ?>">
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="products.php" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
                
            <?php else: ?>
                <div class="page-header">
                    <h1>Kelola Produk</h1>
                    <a href="products.php?action=create" class="btn btn-primary">Tambah Produk</a>
                </div>
                
                <?php if (empty($products)): ?>
                    <div class="empty-state">
                        <p>Belum ada produk. Mulai tambahkan produk pertama Anda!</p>
                        <a href="products.php?action=create" class="btn btn-primary">Tambah Produk</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Produk</th>
                                    <th>Kategori</th>
                                    <th>Kuantitas</th>
                                    <th>Harga</th>
                                    <th>Lokasi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $p): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($p['product_code']); ?></td>
                                    <td><?php echo htmlspecialchars($p['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($p['category']); ?></td>
                                    <td><?php echo number_format($p['quantity']); ?> <?php echo htmlspecialchars($p['unit']); ?></td>
                                    <td><?php echo formatCurrency($p['price']); ?></td>
                                    <td><?php echo htmlspecialchars($p['location']); ?></td>
                                    <td class="action-buttons">
                                        <a href="products.php?action=edit&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus produk ini?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>






















