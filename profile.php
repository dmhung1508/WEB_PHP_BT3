<?php
require_once "includes/db.php";
require_once "includes/functions.php";

start_session_if_not_started();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = 'profile.php';
    header("Location: login.php");
    exit();
}

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

$page_title = "Hồ sơ của tôi";
require_once "includes/header.php";
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="display-1 mb-3">
                            <i class="bi bi-person-circle"></i>
                        </div>
                        <h5 class="mb-0"><?php echo $user['name']; ?></h5>
                        <p class="text-muted"><?php echo $user['email']; ?></p>
                    </div>
                    <hr>
                    <div class="list-group list-group-flush">
                        <a href="profile.php" class="list-group-item list-group-item-action active">
                            <i class="bi bi-person me-2"></i> Thông tin cá nhân
                        </a>
                        <a href="my-orders.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-box me-2"></i> Đơn hàng của tôi
                        </a>
                        <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-9">
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
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Thông tin cá nhân</h5>
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
            
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Đổi mật khẩu</h5>
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
</div>

<?php require_once "includes/footer.php"; ?>
