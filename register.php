<?php
require_once "includes/db.php";
require_once "includes/functions.php";

start_session_if_not_started();

// Kiểm tra nếu đã đăng nhập thì chuyển hướng đến trang chủ
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$name = $email = $password = $confirm_password = $phone = $address = '';
$errors = [];

// Xử lý đăng ký
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    // Kiểm tra tên
    if (empty($name)) {
        $errors[] = "Vui lòng nhập họ tên.";
    }
    
    // Kiểm tra email
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
    
    // Kiểm tra mật khẩu
    if (empty($password)) {
        $errors[] = "Vui lòng nhập mật khẩu.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Mật khẩu phải có ít nhất 6 ký tự.";
    }
    
    // Kiểm tra xác nhận mật khẩu
    if ($password != $confirm_password) {
        $errors[] = "Xác nhận mật khẩu không khớp.";
    }
    
    // Nếu không có lỗi, tiến hành đăng ký
    if (empty($errors)) {
        // Mã hóa mật khẩu
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Thêm người dùng vào database
        $query = "INSERT INTO users (name, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, 'customer')";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $hashed_password, $phone, $address);
        
        if (mysqli_stmt_execute($stmt)) {
            // Đăng ký thành công, tự động đăng nhập
            $user_id = mysqli_insert_id($conn);
            
            $_SESSION['user_id'] = $user_id;
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = 'customer';
            
            // Chuyển hướng đến trang chủ
            header("Location: index.php");
            exit();
        } else {
            $errors[] = "Có lỗi xảy ra khi đăng ký. Vui lòng thử lại.";
        }
        
        mysqli_stmt_close($stmt);
    }
}

$page_title = "Đăng ký";
require_once "includes/header.php";
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Đăng ký tài khoản</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="mb-3">
                            <label for="name" class="form-label">Họ tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo $name; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="form-text">Mật khẩu phải có ít nhất 6 ký tự.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $phone; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Địa chỉ</label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo $address; ?></textarea>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Đăng ký</button>
                        </div>
                    </form>
                    
                    <div class="mt-3 text-center">
                        <p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once "includes/footer.php"; ?>
