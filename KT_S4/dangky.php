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
    <title>Test1 - Đăng Kí Học Phần</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .nav-link {
            color: #fff !important;
        }
        .nav-link:hover {
            color: #f8f9fa !important;
        }
        .summary-info {
            color: red;
            margin: 15px 0;
        }
        .action-buttons {
            margin-top: 20px;
            text-align: right;
        }
        .btn-xoa {
            color: blue;
            text-decoration: none;
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
        }
        .btn-xoa:hover {
            text-decoration: underline;
        }
        .registration-info {
            max-width: 500px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
        }
        .info-row {
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            margin-right: 10px;
            min-width: 150px;
            display: inline-block;
        }
        .confirm-button {
            text-align: center;
            margin-top: 20px;
        }
        .course-list {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container mt-4">
        <h2>DANH SACH ĐĂNG KÝ HỌC PHẦN</h2>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($showConfirmation): ?>
            <!-- Trang xác nhận đăng ký -->
            <div class="registration-info">
                <h3 class="text-center mb-4">Thông tin Đăng kí</h3>
                
                <div class="info-row">
                    <span class="info-label">Mã số sinh viên:</span>
                    <span><?php echo htmlspecialchars($studentInfo['MaSV']); ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Họ Tên Sinh Viên:</span>
                    <span><?php echo htmlspecialchars($studentInfo['HoTen']); ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Ngày Sinh:</span>
                    <span><?php echo date('d/m/Y', strtotime($studentInfo['NgaySinh'])); ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Ngành Học:</span>
                    <span><?php echo htmlspecialchars($studentInfo['TenNganh']); ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Ngày Đăng Kí:</span>
                    <span><?php echo date('d/m/Y'); ?></span>
                </div>

                <div class="course-list">
                    <h4>Danh sách học phần đăng ký:</h4>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>MaHP</th>
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

                <div class="confirm-button">
                    <form method="POST">
                        <button type="submit" name="confirm_registration" class="btn btn-success">Xác nhận</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- Trang giỏ đăng ký -->
            
            
            <?php if (empty($_SESSION['cart'])): ?>
                <div class="alert alert-info">
                    Chưa có học phần nào trong giỏ đăng ký. 
                    <a href="hocphan.php" class="btn btn-primary btn-sm">Chọn học phần</a>
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>MaHP</th>
                            <th>Tên Học Phần</th>
                            <th>Số Tín Chỉ</th>
                            <th></th>
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
                                    <button type="submit" name="delete" class="btn btn-danger btn-sm">Xóa</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="text-end mt-3">
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="save_registration" class="btn btn-primary">Lưu đăng ký</button>
                    </form>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 