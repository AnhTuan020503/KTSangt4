<?php
require_once 'config.php';

// Get student ID from URL
$masv = isset($_GET['id']) ? $_GET['id'] : '';

if (!$masv) {
    header("Location: index.php");
    exit();
}

// Fetch student data with major information
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

// Fetch student's registrations with course details
$sql = "SELECT dk.MaDK, dk.NgayDK, hp.MaHP, hp.TenHP, hp.SoTinChi
        FROM DangKy dk
        JOIN ChiTietDangKy ctdk ON dk.MaDK = ctdk.MaDK
        JOIN HocPhan hp ON ctdk.MaHP = hp.MaHP
        WHERE dk.MaSV = :masv
        ORDER BY dk.NgayDK DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([':masv' => $masv]);
$registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test1 - Thông tin chi tiết sinh viên</title>
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
        <h2>Thông tin chi tiết</h2>
        <p>SinhVien</p>

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

        <div class="mt-3">
            <a href="edit.php?id=<?php echo $student['MaSV']; ?>">Edit</a> |
            <a href="index.php">Back to List</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 