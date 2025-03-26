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

// Xử lý xóa tất cả học phần khỏi giỏ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_all'])) {
    $_SESSION['cart'] = [];
    header("Location: dangky.php");
    exit();
}

// Xử lý xóa một học phần khỏi giỏ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete']) && isset($_POST['mahp'])) {
    $mahp = $_POST['mahp'];
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['MaHP'] == $mahp) {
            unset($_SESSION['cart'][$key]);
            $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
            break;
        }
    }
    header("Location: dangky.php");
    exit();
}

// Lấy thông tin sinh viên và ngành học
$sql = "SELECT sv.*, nh.TenNganh 
        FROM SinhVien sv 
        LEFT JOIN NganhHoc nh ON sv.MaNganh = nh.MaNganh 
        WHERE sv.MaSV = :masv";
$stmt = $conn->prepare($sql);
$stmt->execute([':masv' => $currentUser['masv']]);
$studentInfo = $stmt->fetch(PDO::FETCH_ASSOC);

// Xử lý xác nhận đăng ký và lưu vào database
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_registration'])) {
    try {
        if (!empty($_SESSION['cart'])) {
            // Tạo đăng ký mới
            $sql = "INSERT INTO DangKy (MaSV, NgayDK) VALUES (:masv, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':masv' => $currentUser['masv']]);
            $madk = $conn->lastInsertId();
            
            // Thêm từng học phần vào chi tiết đăng ký
            foreach ($_SESSION['cart'] as $course) {
                $sql = "INSERT INTO ChiTietDangKy (MaDK, MaHP) VALUES (:madk, :mahp)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':madk' => $madk,
                    ':mahp' => $course['MaHP']
                ]);
            }
            
            // Xóa giỏ sau khi lưu thành công
            $_SESSION['cart'] = [];
            $success = "Đăng ký học phần thành công!";
        } else {
            $error = "Không có học phần nào trong giỏ để đăng ký!";
        }
    } catch(PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "Có học phần đã được đăng ký trước đó!";
        } else {
            $error = "Lỗi khi lưu đăng ký: " . $e->getMessage();
        }
    }
}

// Kiểm tra xem có đang ở trang xác nhận không
$showConfirmation = isset($_POST['save_registration']) && !empty($_SESSION['cart']);

// Tính tổng số học phần và tín chỉ trong giỏ
$totalCourses = count($_SESSION['cart']);
$totalCredits = array_sum(array_column($_SESSION['cart'], 'SoTinChi'));
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác Nhận Đăng Ký Học Phần</title>
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
        .btn-success {
            background-color: #2ecc71;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
        }
        .btn-success:hover {
            background-color: #27ae60;
        }
        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .registration-info {
            background-color: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .info-row {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            font-weight: 600;
            color: #2c3e50;
            min-width: 180px;
            display: inline-block;
        }
        .info-value {
            color: #34495e;
        }
        .course-list {
            margin-top: 25px;
        }
        .course-list h4 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }
        .summary-box {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .summary-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .summary-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .summary-label {
            color: #7f8c8d;
        }
        .summary-value {
            font-weight: 600;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container mt-4">
        <h2 class="page-title">XÁC NHẬN ĐĂNG KÝ HỌC PHẦN</h2>

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

        <?php if ($showConfirmation): ?>
            <!-- Trang xác nhận đăng ký -->
            <div class="registration-info">
                <h3 class="text-center mb-4">
                    <i class="fas fa-user-graduate"></i> Thông tin sinh viên
                </h3>
                
                <div class="info-row">
                    <span class="info-label">Mã số sinh viên:</span>
                    <span class="info-value"><?php echo htmlspecialchars($studentInfo['MaSV']); ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Họ Tên Sinh Viên:</span>
                    <span class="info-value"><?php echo htmlspecialchars($studentInfo['HoTen']); ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Ngày Sinh:</span>
                    <span class="info-value"><?php echo date('d/m/Y', strtotime($studentInfo['NgaySinh'])); ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Ngành Học:</span>
                    <span class="info-value"><?php echo htmlspecialchars($studentInfo['TenNganh']); ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Ngày Đăng Ký:</span>
                    <span class="info-value"><?php echo date('d/m/Y'); ?></span>
                </div>

                <div class="course-list">
                    <h4><i class="fas fa-book"></i> Danh sách học phần đăng ký</h4>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Mã HP</th>
                                    <th>Tên Học Phần</th>
                                    <th>Số Tín Chỉ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($_SESSION['cart'] as $course): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($course['MaHP']); ?></td>
                                    <td><?php echo htmlspecialchars($course['TenHP']); ?></td>
                                    <td><?php echo htmlspecialchars($course['SoTinChi']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="summary-box">
                    <div class="summary-title">
                        <i class="fas fa-calculator"></i> Tổng kết
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Tổng số học phần:</span>
                        <span class="summary-value"><?php echo $totalCourses; ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Tổng số tín chỉ:</span>
                        <span class="summary-value"><?php echo $totalCredits; ?></span>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <form method="POST">
                        <button type="submit" name="confirm_registration" class="btn btn-success btn-lg">
                            <i class="fas fa-check-circle"></i> Xác nhận đăng ký
                        </button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- Trang giỏ đăng ký -->
            <?php if (empty($_SESSION['cart'])): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Chưa có học phần nào trong giỏ đăng ký. 
                    <a href="hocphan.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Chọn học phần
                    </a>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-shopping-cart"></i> Giỏ đăng ký học phần
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
                                    <?php foreach($_SESSION['cart'] as $course): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($course['MaHP']); ?></td>
                                        <td><?php echo htmlspecialchars($course['TenHP']); ?></td>
                                        <td><?php echo htmlspecialchars($course['SoTinChi']); ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="mahp" value="<?php echo htmlspecialchars($course['MaHP']); ?>">
                                                <button type="submit" name="delete" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i> Xóa
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="summary-box">
                            <div class="summary-title">
                                <i class="fas fa-calculator"></i> Tổng kết
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Tổng số học phần:</span>
                                <span class="summary-value"><?php echo $totalCourses; ?></span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Tổng số tín chỉ:</span>
                                <span class="summary-value"><?php echo $totalCredits; ?></span>
                            </div>
                        </div>

                        <div class="text-end mt-3">
                            <form method="POST" style="display: inline;">
                                <button type="submit" name="save_registration" class="btn btn-primary">
                                    <i class="fas fa-check"></i> Tiếp tục
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 