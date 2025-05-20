<?php
$page_title = "Hồ sơ cá nhân";
require_once "includes/header.php";

$user = get_logged_in_user();
$success_message = '';
$error_message = '';

// Xử lý cập nhật thông tin
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    // Kiểm tra dữ liệu
    if (empty($name)) {
        $error_message = "Vui lòng nhập họ tên.";
    } else {
        // Cập nhật thông tin
        $query = "UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssi", $name, $phone, $address, $_SESSION['user_id']);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Thông tin đã được cập nhật thành công.";
            $_SESSION['name'] = $name;
            $user = get_logged_in_user();
        } else {
            $error_message = "Có lỗi xảy ra khi cập nhật thông tin: " . mysqli_error($conn);
        }
        
        mysqli_stmt_close($stmt);
    }
}

// Xử lý đổi mật khẩu
if (isset($_POST['change_password'])) {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Kiểm tra mật khẩu hiện tại
    if (!password_verify($current_password, $user['password'])) {
        $error_message = "Mật khẩu hiện tại không đúng.";
    } elseif (empty($new_password)) {
        $error_message = "Vui lòng nhập mật khẩu mới.";
    } elseif (strlen($new_password) < 6) {
        $error_message = "Mật khẩu mới phải có ít nhất 6 ký tự.";
    } elseif ($new_password != $confirm_password) {
        $error_message = "Xác nhận mật khẩu không khớp.";
    } else {
        // Cập nhật mật khẩu
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "si", $hashed_password, $_SESSION['user_id']);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Mật khẩu đã được cập nhật thành công.";
        } else {
            $error_message = "Có lỗi xảy ra khi cập nhật mật khẩu: " . mysqli_error($conn);
        }
        
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-3">
    <h1 class="h2">Hồ sơ cá nhân</h1>
</div>

<?php if (!empty($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $success_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Thông tin cá nhân</h6>
            </div>
            <div class="card-body">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="mb-3">
                        <label for="name" class="form-label">Họ tên</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $user['name']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" value="<?php echo $user['email']; ?>" disabled>
                        <div class="form-text">Email không thể thay đổi.</div>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $user['phone']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Địa chỉ</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo $user['address']; ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Cập nhật thông tin</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Đổi mật khẩu</h6>
            </div>
            <div class="card-body">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Mật khẩu mới</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <div class="form-text">Mật khẩu phải có ít nhất 6 ký tự.</div>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Xác nhận mật khẩu mới</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-primary">Đổi mật khẩu</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once "includes/footer.php"; ?>
