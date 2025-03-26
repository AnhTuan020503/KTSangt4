<?php
require_once 'config.php';

// Get student ID from URL
$masv = isset($_GET['id']) ? $_GET['id'] : '';

if (!$masv) {
    header("Location: index.php");
    exit();
}

// Fetch student data
$sql = "SELECT s.*, n.TenNganh 
        FROM SinhVien s 
        LEFT JOIN NganhHoc n ON s.MaNganh = n.MaNganh 
        WHERE s.MaSV = :masv";
$stmt = $conn->prepare($sql);
$stmt->execute([':masv' => $masv]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // First, get the student's image path
        $sql = "SELECT Hinh FROM SinhVien WHERE MaSV = :masv";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':masv' => $masv]);
        $studentImage = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Delete the student
        $sql = "DELETE FROM SinhVien WHERE MaSV = :masv";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':masv' => $masv]);
        
        // Delete the image file if it exists
        if ($studentImage && $studentImage['Hinh'] && file_exists($studentImage['Hinh'])) {
            unlink($studentImage['Hinh']);
        }
        
        header("Location: index.php");
        exit();
    } catch(PDOException $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test1 - Xóa Sinh viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .nav-link {
            color: #fff !important;
        }
        .nav-link:hover {
            color: #f8f9fa !important;
        }
        .info-group {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
        }
        .info-group label {
            width: 100px;
            text-align: right;
            margin-right: 10px;
            font-weight: normal;
        }
        .student-image {
            max-width: 200px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
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
                        <a class="nav-link active" href="index.php">Sinh Viên</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Học Phần</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Đăng Kí ()</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Đăng Nhập</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>XÓA THÔNG TIN</h2>
        <h3>Are you sure you want to delete this?</h3>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="info-group">
            <label>HoTen</label>
            <span><?php echo htmlspecialchars($student['HoTen']); ?></span>
        </div>

        <div class="info-group">
            <label>GioiTinh</label>
            <span><?php echo htmlspecialchars($student['GioiTinh']); ?></span>
        </div>

        <div class="info-group">
            <label>NgaySinh</label>
            <span><?php echo htmlspecialchars($student['NgaySinh']); ?></span>
        </div>

        <div class="info-group">
            <label>Hinh</label>
            <?php if ($student['Hinh']): ?>
                <img src="<?php echo htmlspecialchars($student['Hinh']); ?>" alt="Student photo" class="student-image">
            <?php endif; ?>
        </div>

        <div class="info-group">
            <label>MaNganh</label>
            <span><?php echo htmlspecialchars($student['MaNganh']); ?></span>
        </div>

        <form method="POST" style="display: inline-block;">
            <button type="submit" class="btn btn-danger">Delete</button>
        </form>
        <span> | </span>
        <a href="index.php">Back to List</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 