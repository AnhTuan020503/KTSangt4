<?php
session_start();
require_once 'config.php';
require_once 'check_login.php';

// Fetch all students with their major information
$sql = "SELECT s.*, n.TenNganh 
        FROM SinhVien s 
        LEFT JOIN NganhHoc n ON s.MaNganh = n.MaNganh 
        ORDER BY s.MaSV";
$stmt = $conn->prepare($sql);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$currentUser = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Sinh viên</title>
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

        .user-info {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }

        .btn-logout {
            transition: all 0.3s ease;
        }

        .btn-logout:hover {
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

        .table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead th {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 15px;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background-color: #f8f9fc;
        }

        .action-buttons a {
            color: var(--primary-color);
            text-decoration: none;
            margin: 0 5px;
            transition: all 0.3s ease;
        }

        .action-buttons a:hover {
            color: #224abe;
            transform: translateY(-2px);
        }

        .student-image {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-color);
        }

        .add-student-btn {
            background: var(--success-color);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            margin-bottom: 20px;
        }

        .add-student-btn:hover {
            background: #169b6b;
            color: white;
            transform: translateY(-2px);
        }

        .page-title {
            color: var(--primary-color);
            margin-bottom: 30px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container mt-4">
        <h2 class="page-title">
            <i class="fas fa-users me-2"></i>Quản lý Sinh viên
        </h2>
        
        <?php if ($currentUser): ?>
            <div class="user-info d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-user-circle me-2"></i>
                    <span>Xin chào, <?php echo htmlspecialchars($currentUser['hoten']); ?></span>
                </div>
                <a href="logout.php" class="btn btn-outline-danger btn-sm btn-logout">
                    <i class="fas fa-sign-out-alt me-1"></i>Đăng xuất
                </a>
            </div>
        <?php endif; ?>

        <a href="create.php" class="add-student-btn">
            <i class="fas fa-plus me-2"></i>Thêm Sinh viên mới
        </a>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Mã SV</th>
                                <th>Họ Tên</th>
                                <th>Giới Tính</th>
                                <th>Ngày Sinh</th>
                                <th>Hình Ảnh</th>
                                <th>Ngành Học</th>
                                <th>Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['MaSV']); ?></td>
                                <td><?php echo htmlspecialchars($student['HoTen']); ?></td>
                                <td>
                                    <i class="fas <?php echo $student['GioiTinh'] == 'Nam' ? 'fa-male text-primary' : 'fa-female text-danger'; ?>"></i>
                                    <?php echo htmlspecialchars($student['GioiTinh']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($student['NgaySinh']); ?></td>
                                <td>
                                    <?php if ($student['Hinh']): ?>
                                        <img src="<?php echo htmlspecialchars($student['Hinh']); ?>" alt="Student photo" class="student-image">
                                    <?php else: ?>
                                        <i class="fas fa-user-circle fa-2x text-secondary"></i>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($student['TenNganh']); ?></td>
                                <td class="action-buttons">
                                    <a href="edit.php?id=<?php echo $student['MaSV']; ?>" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="detail.php?id=<?php echo $student['MaSV']; ?>" title="Chi tiết">
                                        <i class="fas fa-info-circle"></i>
                                    </a>
                                    <a href="delete.php?id=<?php echo $student['MaSV']; ?>" 
                                       onclick="return confirm('Bạn có chắc chắn muốn xóa sinh viên này?')"
                                       title="Xóa">
                                        <i class="fas fa-trash-alt text-danger"></i>
                                    </a>
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