<?php
$page_title = "Chỉnh sửa người dùng";
require_once "includes/header.php";

// Kiểm tra id người dùng
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$user_id = $_GET['id'];

// Lấy thông tin người dùng
$query = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: users.php");
    exit();
}

$user = mysqli_fetch_assoc($result);

// Xử lý cập nhật người dùng
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $role = trim($_POST['role']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    $errors = [];
    
    // Kiểm tra dữ liệu
    if (empty($name)) {
        $errors[] = "Vui lòng nhập họ tên.";
    }
    
    if (empty($email)) {
        $errors[] =  {
        $errors[] = "Vui lòng nhập họ tên.";
    }
    
    if (empty($email)) {
        $errors[] = "Vui lòng nhập email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ.";
    } else {
        // Kiểm tra email đã tồn tại chưa (trừ người dùng hiện tại)
        $query = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "si", $email, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = "Email đã được sử dụng bởi người dùng khác.";
        }
        
        mysqli_stmt_close($stmt);
    }
    
    if (empty($role)) {
        $errors[] = "Vui lòng chọn vai trò.";
    }
    
    // Kiểm tra mật khẩu nếu có nhập
    if (!empty($password)) {
        if (strlen($password) < 6) {
            $errors[] = "Mật khẩu phải có ít nhất 6 ký tự.";
        } elseif ($password != $confirm_password) {
            $errors[] = "Xác nhận mật khẩu không khớp.";
        }
    }
    
    // Nếu không có lỗi, cập nhật người dùng
    if (empty($errors)) {
        if (!empty($password)) {
            // Cập nhật cả mật khẩu
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "UPDATE users SET name = ?, email = ?, phone = ?, address = ?, role = ?, password = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "ssssssi", $name, $email, $phone, $address, $role, $hashed_password, $user_id);
        } else {
            // Không cập nhật mật khẩu
            $query = "UPDATE users SET name = ?, email = ?, phone = ?, address = ?, role = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "sssssi", $name, $email, $phone, $address, $role, $user_id);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Người dùng đã được cập nhật thành công.";
            // Cập nhật thông tin người dùng
            $query = "SELECT * FROM users WHERE id = $user_id";
            $result = mysqli_query($conn, $query);
            $user = mysqli_fetch_assoc($result);
        } else {
            $errors[] = "Có lỗi xảy ra khi cập nhật người dùng: " . mysqli_error($conn);
        }
        
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-3">
    <h1 class="h2">Chỉnh sửa người dùng</h1>
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
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $user_id; ?>">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Họ tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $user['name']; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control" id="password" name="password">
                        <div class="form-text">Để trống nếu không muốn thay đổi mật khẩu.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="phone" class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $user['phone']; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Địa chỉ</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo $user['address']; ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Vai trò <span class="text-danger">*</span></label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="customer" <?php echo ($user['role'] == 'customer') ? 'selected' : ''; ?>>Khách hàng</option>
                            <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Quản trị viên</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Cập nhật người dùng
                </button>
                <a href="users.php" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>

<?php require_once "includes/footer.php"; ?>
