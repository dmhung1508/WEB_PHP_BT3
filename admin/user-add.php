<?php
$page_title = "Thêm người dùng mới";
require_once "includes/header.php";

// Xử lý thêm người dùng
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $role = trim($_POST['role']);
    
    $errors = [];
    
    // Kiểm tra dữ liệu
    if (empty($name)) {
        $errors[] = "Vui lòng nhập họ tên.";
    }
    
    if (empty($email)) {
        $errors[] = "Vui lòng nhập email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ.";
    } else {
        // Kiểm tra email đã tồn tại chưa
        $query = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = "Email đã được sử dụng.";
        }
        
        mysqli_stmt_close($stmt);
    }
    
    if (empty($password)) {
        $errors[] = "Vui lòng nhập mật khẩu.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Mật khẩu phải có ít nhất 6 ký tự.";
    }
    
    if ($password != $confirm_password) {
        $errors[] = "Xác nhận mật khẩu không khớp.";
    }
    
    if (empty($role)) {
        $errors[] = "Vui lòng chọn vai trò.";
    }
    
    // Nếu không có lỗi, thêm người dùng
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO users (name, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssssss", $name, $email, $hashed_password, $phone, $address, $role);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Người dùng đã được thêm thành công.";
            // Reset form
            $name = $email = $phone = $address = '';
            $role = 'customer';
        } else {
            $errors[] = "Có lỗi xảy ra khi thêm người dùng: " . mysqli_error($conn);
        }
        
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-3">
    <h1 class="h2">Thêm người dùng mới</h1>
    <a href="users.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Quay lại
    </a>
</div>

<?php if (isset($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $success_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-body">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Họ tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($name) ? $name : ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="form-text">Mật khẩu phải có ít nhất 6 ký tự.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="phone" class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo isset($phone) ? $phone : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Địa chỉ</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo isset($address) ? $address : ''; ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Vai trò <span class="text-danger">*</span></label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="customer" <?php echo (isset($role) && $role == 'customer') ? 'selected' : ''; ?>>Khách hàng</option>
                            <option value="admin" <?php echo (isset($role) && $role == 'admin') ? 'selected' : ''; ?>>Quản trị viên</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Lưu người dùng
                </button>
                <a href="users.php" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>

<?php require_once "includes/footer.php"; ?>
