<?php
require_once 'check_login.php';
if (!isset($currentUser)) {
    $currentUser = getCurrentUser();
}

// Khởi tạo giỏ học phần trong session nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Đếm số lượng học phần trong giỏ
$cartCount = count($_SESSION['cart']);
?>

<!-- Navigation Menu -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Test1</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" 
                       href="index.php">Sinh Viên</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'hocphan.php' ? 'active' : ''; ?>" 
                       href="hocphan.php">Học Phần</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dangky.php' ? 'active' : ''; ?>" 
                       href="dangky.php">
                        <i class="fas fa-shopping-cart"></i>
                        Giỏ đăng ký <?php if ($cartCount > 0): ?><span class="badge bg-danger"><?php echo $cartCount; ?></span><?php endif; ?>
                    </a>
                </li>
                <?php if ($currentUser): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Đăng Xuất</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : ''; ?>" 
                           href="login.php">Đăng Nhập</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
    .nav-link {
        color: #fff !important;
    }
    .nav-link:hover {
        color: #f8f9fa !important;
    }
    .badge {
        position: relative;
        top: -8px;
        margin-left: 2px;
        font-size: 0.75em;
    }
</style> 