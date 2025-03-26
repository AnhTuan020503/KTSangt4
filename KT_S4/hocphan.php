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

// Lấy danh sách học phần
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


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register']) && isset($_POST['mahp'])) {
    $mahp = $_POST['mahp'];

    // Lấy thông tin học phần
    $sql = "SELECT * FROM HocPhan WHERE MaHP = :mahp";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':mahp' => $mahp]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($course) {
        if ($course['SoLuongConLai'] > 0) { // Kiểm tra số lượng còn lại
            if (!in_array($mahp, array_column($_SESSION['cart'], 'MaHP'))) {
                // Thêm vào giỏ
                $_SESSION['cart'][] = $course;

                // Giảm số lượng trong CSDL
                $sql = "UPDATE HocPhan SET SoLuongConLai = SoLuongConLai - 1 WHERE MaHP = :mahp";
                $stmt = $conn->prepare($sql);
                $stmt->execute([':mahp' => $mahp]);

                $success = "Đã thêm học phần vào giỏ đăng ký!";
            } else {
                $error = "Học phần này đã có trong giỏ!";
            }
        } else {
            $error = "Học phần này đã hết chỗ!";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test1 - Học Phần</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container mt-4">
        <h2>DANH SÁCH HỌC PHẦN</h2>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Hiển thị giỏ đăng ký -->
        <?php if (!empty($_SESSION['cart'])): ?>
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    Giỏ đăng ký (<?php echo count($_SESSION['cart']); ?> học phần)
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>MaHP</th>
                                    <th>Tên Học Phần</th>
                                    <th>Số Tín Chỉ</th>
                                    <th></th>
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
                                            <button type="submit" name="remove" class="btn btn-danger btn-sm">Xóa</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-2">
                        <a href="dangky.php" class="btn btn-primary">Xem giỏ đăng ký</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Danh sách học phần -->
        <table class="table">
            <thead>
        <tr>
            <th>MaHP</th>
            <th>Tên Học Phần</th>
            <th>Số Tín Chỉ</th>
            <th>Số Lượng Còn Lại</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($courses as $course): ?>
        <tr>
            <td><?php echo htmlspecialchars($course['MaHP']); ?></td>
            <td><?php echo htmlspecialchars($course['TenHP']); ?></td>
            <td><?php echo htmlspecialchars($course['SoTinChi']); ?></td>
            <td><?php echo htmlspecialchars($course['SoLuongConLai']); ?></td>
            <td>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="mahp" value="<?php echo htmlspecialchars($course['MaHP']); ?>">
                    <button type="submit" name="register" class="btn btn-primary btn-sm"
                        <?php echo ($course['SoLuongConLai'] == 0 || in_array($course['MaHP'], array_column($_SESSION['cart'], 'MaHP'))) ? 'disabled' : ''; ?>>
                        <?php echo ($course['SoLuongConLai'] == 0) ? 'Hết chỗ' : (in_array($course['MaHP'], array_column($_SESSION['cart'], 'MaHP')) ? 'Đã thêm vào giỏ' : 'Thêm vào giỏ'); ?>
                    </button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>

        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 