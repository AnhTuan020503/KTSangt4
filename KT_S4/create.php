<?php
require_once 'config.php';

// Fetch all majors for dropdown
$sql = "SELECT * FROM NganhHoc ORDER BY TenNganh";
$stmt = $conn->prepare($sql);
$stmt->execute();
$majors = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $masv = $_POST['MaSV'];
    $hoten = $_POST['HoTen'];
    $gioitinh = $_POST['GioiTinh'];
    $ngaysinh = $_POST['NgaySinh'];
    $manganh = $_POST['MaNganh'];
    
    // Handle file upload
    $hinh = '';
    if (isset($_FILES['Hinh']) && $_FILES['Hinh']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = strtolower(pathinfo($_FILES["Hinh"]["name"], PATHINFO_EXTENSION));
        $hinh = $target_dir . $masv . '.' . $file_extension;
        move_uploaded_file($_FILES["Hinh"]["tmp_name"], $hinh);
    }

    try {
        $sql = "INSERT INTO SinhVien (MaSV, HoTen, GioiTinh, NgaySinh, Hinh, MaNganh) 
                VALUES (:masv, :hoten, :gioitinh, :ngaysinh, :hinh, :manganh)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':masv' => $masv,
            ':hoten' => $hoten,
            ':gioitinh' => $gioitinh,
            ':ngaysinh' => $ngaysinh,
            ':hinh' => $hinh,
            ':manganh' => $manganh
        ]);
        
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
    <title>Thêm sinh viên mới</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
        }
        
        body {
            background-color: #f8f9fc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color), #224abe);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .nav-link {
            color: #fff !important;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: #f8f9fa !important;
            transform: translateY(-2px);
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background: linear-gradient(135deg, var(--success-color), #169b6b);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 15px 20px;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #d1d3e2;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        .form-label {
            font-weight: 500;
            color: var(--secondary-color);
        }

        .btn {
            border-radius: 8px;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-primary {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .page-title {
            color: var(--success-color);
            margin-bottom: 30px;
            font-weight: 600;
        }

        .image-preview {
            width: 150px;
            height: 150px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid var(--success-color);
            margin-top: 10px;
        }

        .image-upload-container {
            border: 2px dashed #d1d3e2;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .image-upload-container:hover {
            border-color: var(--success-color);
            background-color: #f8f9fc;
        }

        .image-upload-icon {
            font-size: 2em;
            color: var(--secondary-color);
            margin-bottom: 10px;
        }

        .back-link {
            color: var(--success-color);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: #169b6b;
            transform: translateX(-5px);
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container mt-4">
        <h2 class="page-title">
            <i class="fas fa-user-plus me-2"></i>Thêm sinh viên mới
        </h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <i class="fas fa-user-graduate me-2"></i>Thông tin sinh viên
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="MaSV" class="form-label">Mã sinh viên</label>
                            <input type="text" class="form-control" id="MaSV" name="MaSV" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="HoTen" class="form-label">Họ và tên</label>
                            <input type="text" class="form-control" id="HoTen" name="HoTen" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="GioiTinh" class="form-label">Giới tính</label>
                            <select class="form-select" id="GioiTinh" name="GioiTinh" required>
                                <option value="">Chọn giới tính</option>
                                <option value="Nam">Nam</option>
                                <option value="Nữ">Nữ</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="NgaySinh" class="form-label">Ngày sinh</label>
                            <input type="date" class="form-control" id="NgaySinh" name="NgaySinh" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="MaNganh" class="form-label">Ngành học</label>
                            <select class="form-select" id="MaNganh" name="MaNganh" required>
                                <option value="">Chọn ngành học</option>
                                <?php foreach($majors as $major): ?>
                                    <option value="<?php echo htmlspecialchars($major['MaNganh']); ?>">
                                        <?php echo htmlspecialchars($major['TenNganh']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label class="form-label">Hình ảnh</label>
                            <div class="image-upload-container" onclick="document.getElementById('Hinh').click()">
                                <i class="fas fa-camera image-upload-icon"></i>
                                <p class="mb-0">Nhấp để chọn ảnh</p>
                                <input type="file" class="d-none" id="Hinh" name="Hinh" accept="image/*" 
                                       onchange="previewImage(this)">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <a href="index.php" class="back-link">
                            <i class="fas fa-arrow-left me-1"></i>Quay lại danh sách
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Thêm sinh viên
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var container = document.querySelector('.image-upload-container');
                    container.innerHTML = `<img src="${e.target.result}" alt="Preview" class="image-preview">`;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html> 