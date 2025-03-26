<?php
require_once 'config.php';
require_once 'check_login.php';
requireLogin();

$currentUser = getCurrentUser();
if (!$currentUser) {
    header("Location: login.php");
    exit();
}

// Khởi tạo giỏ học phần trong session nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Lấy danh sách học phần từ cơ sở dữ liệu
$sql = "SELECT * FROM HocPhan ORDER BY MaHP";
$stmt = $conn->prepare($sql);
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Xử lý đăng ký học phần vào giỏ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register']) && isset($_POST['mahp'])) {
    $mahp = $_POST['mahp'];
    
    // Kiểm tra xem học phần đã có trong giỏ chưa
    if (!in_array($mahp, array_column($_SESSION['cart'], 'MaHP'))) {
        // Lấy thông tin học phần
        $sql = "SELECT * FROM HocPhan WHERE MaHP = :mahp";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':mahp' => $mahp]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($course) {
            // Thêm vào giỏ
            $_SESSION['cart'][] = $course;
            $success = "Đã thêm học phần vào giỏ đăng ký!";
        }
    } else {
        $error = "Học phần này đã có trong giỏ đăng ký!";
    }
}

// Xử lý xóa khỏi giỏ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove']) && isset($_POST['mahp'])) {
    $mahp = $_POST['mahp'];

    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['MaHP'] == $mahp) {
            unset($_SESSION['cart'][$key]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);

            // Cập nhật lại số lượng trong CSDL
            $sql = "UPDATE HocPhan SET SoLuongConLai = SoLuongConLai + 1 WHERE MaHP = :mahp";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':mahp' => $mahp]);

            $success = "Đã xóa học phần khỏi giỏ!";
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký Học Phần</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .page-title {
            color: #2c3e50;
            margin-bottom: 30px;
            font-weight: 600;
            text-align: center;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .card-header {
            background-color: #3498db;
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
        }
        .table {
            margin-bottom: 0;
        }
        .table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
        .table td {
            vertical-align: middle;
        }
        .btn-primary {
            background-color: #3498db;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
        }
        .btn-primary:hover {
            background-color: #2980b9;
        }
        .btn-danger {
            background-color: #e74c3c;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
        }
        .btn-danger:hover {
            background-color: #c0392b;
        }
        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .cart-summary {
            background-color: #fff;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .cart-count {
            background-color: #e74c3c;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container mt-4">
        <h2 class="page-title">DANH SÁCH HỌC PHẦN</h2>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Hiển thị giỏ đăng ký -->
        <?php if (!empty($_SESSION['cart'])): ?>
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-shopping-cart"></i> Giỏ đăng ký
                    <span class="cart-count"><?php echo count($_SESSION['cart']); ?></span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Mã HP</th>
                                    <th>Tên Học Phần</th>
                                    <th>Số Tín Chỉ</th>
                                    <th>Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($_SESSION['cart'] as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['MaHP']); ?></td>
                                    <td><?php echo htmlspecialchars($item['TenHP']); ?></td>
                                    <td><?php echo htmlspecialchars($item['SoTinChi']); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="mahp" value="<?php echo htmlspecialchars($item['MaHP']); ?>">
                                            <button type="submit" name="remove" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i> Xóa
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <a href="dangky.php" class="btn btn-primary">
                            <i class="fas fa-check"></i> Xác nhận đăng ký
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Danh sách học phần -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-book"></i> Danh sách học phần có thể đăng ký
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Mã HP</th>
                                <th>Tên Học Phần</th>
                                <th>Số Tín Chỉ</th>
                                <th>Số Lượng Còn Lại</th>
                                <th>Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($courses as $course): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($course['MaHP']); ?></td>
                                <td><?php echo htmlspecialchars($course['TenHP']); ?></td>
                                <td><?php echo htmlspecialchars($course['SoTinChi']); ?></td>
                                <td>
                                    <span class="badge <?php echo $course['SoLuongConLai'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo htmlspecialchars($course['SoLuongConLai']); ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="mahp" value="<?php echo htmlspecialchars($course['MaHP']); ?>">
                                        <button type="submit" name="register" class="btn btn-primary btn-sm"
                                            <?php echo ($course['SoLuongConLai'] == 0 || in_array($course['MaHP'], array_column($_SESSION['cart'], 'MaHP'))) ? 'disabled' : ''; ?>>
                                            <i class="fas fa-plus"></i>
                                            <?php echo ($course['SoLuongConLai'] == 0) ? 'Hết chỗ' : (in_array($course['MaHP'], array_column($_SESSION['cart'], 'MaHP')) ? 'Đã thêm vào giỏ' : 'Thêm vào giỏ'); ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>