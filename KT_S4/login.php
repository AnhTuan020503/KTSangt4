<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $masv = $_POST['MaSV'];
    
    // Check if student exists
    $sql = "SELECT * FROM SinhVien WHERE MaSV = :masv";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':masv' => $masv]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($student) {
        $_SESSION['masv'] = $student['MaSV'];
        $_SESSION['hoten'] = $student['HoTen'];
        header("Location: index.php");
        exit();
    } else {
        $error = "Mã sinh viên không tồn tại!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test1 - Đăng nhập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .nav-link {
            color: #fff !important;
        }
        .nav-link:hover {
            color: #f8f9fa !important;
        }
        .form-group {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .form-group label {
            width: 100px;
            text-align: right;
            margin-right: 10px;
        }
        .form-group input {
            width: 250px;
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
                        <a class="nav-link" href="index.php">Sinh Viên</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="hocphan.php">Học Phần</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Đăng Kí ()</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="login.php">Đăng Nhập</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>ĐĂNG NHẬP</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="MaSV">MaSV</label>
                <input type="text" class="form-control" id="MaSV" name="MaSV" required>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary" style="margin-left: 110px;">Đăng Nhập</button>
            </div>
        </form>

        <div class="mt-3">
            <a href="index.php">Back to List</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 