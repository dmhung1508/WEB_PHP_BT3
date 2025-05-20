<?php
$page_title = "Cài đặt hệ thống";
require_once "includes/header.php";

// Lấy cài đặt hiện tại
$settings = [];
$settings_query = "SELECT * FROM settings";
$settings_result = mysqli_query($conn, $settings_query);

if ($settings_result && mysqli_num_rows($settings_result) > 0) {
    while ($row = mysqli_fetch_assoc($settings_result)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

// Xử lý cập nhật cài đặt
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $success_message = '';
    $error_message = '';
    
    // Cài đặt cửa hàng
    if (isset($_POST['update_store'])) {
        $store_name = trim($_POST['store_name']);
        $store_description = trim($_POST['store_description']);
        $store_address = trim($_POST['store_address']);
        $store_phone = trim($_POST['store_phone']);
        $store_email = trim($_POST['store_email']);
        
        // Cập nhật cài đặt
        update_setting('store_name', $store_name);
        update_setting('store_description', $store_description);
        update_setting('store_address', $store_address);
        update_setting('store_phone', $store_phone);
        update_setting('store_email', $store_email);
        
        // Xử lý upload logo
        if (isset($_FILES['store_logo']) && $_FILES['store_logo']['error'] == 0) {
            $upload_result = upload_image($_FILES['store_logo']);
            
            if ($upload_result['success']) {
                update_setting('store_logo', $upload_result['filename']);
            } else {
                $error_message = $upload_result['message'];
            }
        }
        
        $success_message = "Cài đặt cửa hàng đã được cập nhật thành công.";
        
        // Cập nhật lại cài đặt
        $settings_result = mysqli_query($conn, $settings_query);
        if ($settings_result && mysqli_num_rows($settings_result) > 0) {
            $settings = [];
            while ($row = mysqli_fetch_assoc($settings_result)) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        }
    }
    
    // Cài đặt email
    if (isset($_POST['update_email'])) {
        $smtp_host = trim($_POST['smtp_host']);
        $smtp_port = trim($_POST['smtp_port']);
        $smtp_username = trim($_POST['smtp_username']);
        $smtp_password = trim($_POST['smtp_password']);
        $smtp_encryption = trim($_POST['smtp_encryption']);
        
        // Cập nhật cài đặt
        update_setting('smtp_host', $smtp_host);
        update_setting('smtp_port', $smtp_port);
        update_setting('smtp_username', $smtp_username);
        
        // Chỉ cập nhật mật khẩu nếu có nhập
        if (!empty($smtp_password)) {
            update_setting('smtp_password', $smtp_password);
        }
        
        update_setting('smtp_encryption', $smtp_encryption);
        
        $success_message = "Cài đặt email đã được cập nhật thành công.";
        
        // Cập nhật lại cài đặt
        $settings_result = mysqli_query($conn, $settings_query);
        if ($settings_result && mysqli_num_rows($settings_result) > 0) {
            $settings = [];
            while ($row = mysqli_fetch_assoc($settings_result)) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        }
    }
    
    // Cài đặt thanh toán
    if (isset($_POST['update_payment'])) {
        $currency_symbol = trim($_POST['currency_symbol']);
        $enable_cod = isset($_POST['enable_cod']) ? 1 : 0;
        $enable_bank_transfer = isset($_POST['enable_bank_transfer']) ? 1 : 0;
        $bank_account_info = trim($_POST['bank_account_info']);
        
        // Cập nhật cài đặt
        update_setting('currency_symbol', $currency_symbol);
        update_setting('enable_cod', $enable_cod);
        update_setting('enable_bank_transfer', $enable_bank_transfer);
        update_setting('bank_account_info', $bank_account_info);
        
        $success_message = "Cài đặt thanh toán đã được cập nhật thành công.";
        
        // Cập nhật lại cài đặt
        $settings_result = mysqli_query($conn, $settings_query);
        if ($settings_result && mysqli_num_rows($settings_result) > 0) {
            $settings = [];
            while ($row = mysqli_fetch_assoc($settings_result)) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        }
    }
}

// Hàm cập nhật cài đặt
function update_setting($key, $value) {
    global $conn;
    
    // Kiểm tra xem cài đặt đã tồn tại chưa
    $query = "SELECT * FROM settings WHERE setting_key = '$key'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        // Cập nhật cài đặt
        $query = "UPDATE settings SET setting_value = ? WHERE setting_key = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $value, $key);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        // Thêm cài đặt mới
        $query = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $key, $value);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-3">
    <h1 class="h2">Cài đặt hệ thống</h1>
</div>

<?php if (isset($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $success_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <ul class="nav nav-tabs card-header-tabs" id="settingsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="store-tab" data-bs-toggle="tab" data-bs-target="#store" type="button" role="tab" aria-controls="store" aria-selected="true">Cửa hàng</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" type="button" role="tab" aria-controls="email" aria-selected="false">Email</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="payment-tab" data-bs-toggle="tab" data-bs-target="#payment" type="button" role="tab" aria-controls="payment" aria-selected="false">Thanh toán</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="social-tab" data-bs-toggle="tab" data-bs-target="#social" type="button" role="tab" aria-controls="social" aria-selected="false">Mạng xã hội</button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="settingsTabsContent">
            <!-- Cài đặt cửa hàng -->
            <div class="tab-pane fade show active" id="store" role="tabpanel" aria-labelledby="store-tab">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="store_name" class="form-label">Tên cửa hàng</label>
                        <input type="text" class="form-control" id="store_name" name="store_name" value="<?php echo isset($settings['store_name']) ? $settings['store_name'] : 'Tech Store'; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="store_description" class="form-label">Mô tả cửa hàng</label>
                        <textarea class="form-control" id="store_description" name="store_description" rows="3"><?php echo isset($settings['store_description']) ? $settings['store_description'] : 'Cửa hàng công nghệ hàng đầu'; ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="store_address" class="form-label">Địa chỉ</label>
                        <input type="text" class="form-control" id="store_address" name="store_address" value="<?php echo isset($settings['store_address']) ? $settings['store_address'] : '123 Đường ABC, Quận XYZ, Hà Nội'; ?>">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="store_phone" class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" id="store_phone" name="store_phone" value="<?php echo isset($settings['store_phone']) ? $settings['store_phone'] : '(84) 123 456 789'; ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="store_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="store_email" name="store_email" value="<?php echo isset($settings['store_email']) ? $settings['store_email'] : 'info@techstore.com'; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="store_logo" class="form-label">Logo cửa hàng</label>
                        <?php if (isset($settings['store_logo']) && !empty($settings['store_logo'])): ?>
                            <div class="mb-2">
                                <img src="<?php echo get_product_image_url($settings['store_logo']); ?>" alt="Store Logo" class="img-thumbnail" width="150">
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="store_logo" name="store_logo" accept="image/*">
                    </div>
                    
                    <button type="submit" name="update_store" class="btn btn-primary">Lưu cài đặt</button>
                </form>
            </div>
            
            <!-- Cài đặt email -->
            <div class="tab-pane fade" id="email" role="tabpanel" aria-labelledby="email-tab">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="mb-3">
                        <label for="smtp_host" class="form-label">SMTP Host</label>
                        <input type="text" class="form-control" id="smtp_host" name="smtp_host" value="<?php echo isset($settings['smtp_host']) ? $settings['smtp_host'] : 'smtp.gmail.com'; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="smtp_port" class="form-label">SMTP Port</label>
                        <input type="text" class="form-control" id="smtp_port" name="smtp_port" value="<?php echo isset($settings['smtp_port']) ? $settings['smtp_port'] : '587'; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="smtp_username" class="form-label">SMTP Username</label>
                        <input type="text" class="form-control" id="smtp_username" name="smtp_username" value="<?php echo isset($settings['smtp_username']) ? $settings['smtp_username'] : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="smtp_password" class="form-label">SMTP Password</label>
                        <input type="password" class="form-control" id="smtp_password" name="smtp_password" placeholder="<?php echo isset($settings['smtp_password']) && !empty($settings['smtp_password']) ? '••••••••' : 'Nhập mật khẩu'; ?>">
                        <div class="form-text">Để trống nếu không muốn thay đổi mật khẩu.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="smtp_encryption" class="form-label">SMTP Encryption</label>
                        <select class="form-select" id="smtp_encryption" name="smtp_encryption">
                            <option value="tls" <?php echo (isset($settings['smtp_encryption']) && $settings['smtp_encryption'] == 'tls') ? 'selected' : ''; ?>>TLS</option>
                            <option value="ssl" <?php echo (isset($settings['smtp_encryption']) && $settings['smtp_encryption'] == 'ssl') ? 'selected' : ''; ?>>SSL</option>
                            <option value="none" <?php echo (isset($settings['smtp_encryption']) && $settings['smtp_encryption'] == 'none') ? 'selected' : ''; ?>>None</option>
                        </select>
                    </div>
                    
                    <button type="submit" name="update_email" class="btn btn-primary">Lưu cài đặt</button>
                    <button type="button" class="btn btn-outline-secondary" id="testEmail">Kiểm tra kết nối</button>
                </form>
            </div>
            
            <!-- Cài đặt thanh toán -->
            <div class="tab-pane fade" id="payment" role="tabpanel" aria-labelledby="payment-tab">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="mb-3">
                        <label for="currency_symbol" class="form-label">Ký hiệu tiền tệ</label>
                        <input type="text" class="form-control" id="currency_symbol" name="currency_symbol" value="<?php echo isset($settings['currency_symbol']) ? $settings['currency_symbol'] : '₫'; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="enable_cod" name="enable_cod" <?php echo (isset($settings['enable_cod']) && $settings['enable_cod'] == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="enable_cod">Cho phép thanh toán khi nhận hàng (COD)</label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="enable_bank_transfer" name="enable_bank_transfer" <?php echo (isset($settings['enable_bank_transfer']) && $settings['enable_bank_transfer'] == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="enable_bank_transfer">Cho phép thanh toán chuyển khoản ngân hàng</label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="bank_account_info" class="form-label">Thông tin tài khoản ngân hàng</label>
                        <textarea class="form-control" id="bank_account_info" name="bank_account_info" rows="3"><?php echo isset($settings['bank_account_info']) ? $settings['bank_account_info'] : ''; ?></textarea>
                    </div>
                    
                    <button type="submit" name="update_payment" class="btn btn-primary">Lưu cài đặt</button>
                </form>
            </div>
            
            <!-- Cài đặt mạng xã hội -->
            <div class="tab-pane fade" id="social" role="tabpanel" aria-labelledby="social-tab">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="mb-3">
                        <label for="facebook_url" class="form-label">Facebook URL</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-facebook"></i></span>
                            <input type="text" class="form-control" id="facebook_url" name="facebook_url" value="<?php echo isset($settings['facebook_url']) ? $settings['facebook_url'] : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="instagram_url" class="form-label">Instagram URL</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-instagram"></i></span>
                            <input type="text" class="form-control" id="instagram_url" name="instagram_url" value="<?php echo isset($settings['instagram_url']) ? $settings['instagram_url'] : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="twitter_url" class="form-label">Twitter URL</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-twitter"></i></span>
                            <input type="text" class="form-control" id="twitter_url" name="twitter_url" value="<?php echo isset($settings['twitter_url']) ? $settings['twitter_url'] : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="youtube_url" class="form-label">YouTube URL</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-youtube"></i></span>
                            <input type="text" class="form-control" id="youtube_url" name="youtube_url" value="<?php echo isset($settings['youtube_url']) ? $settings['youtube_url'] : ''; ?>">
                        </div>
                    </div>
                    
                    <button type="submit" name="update_social" class="btn btn-primary">Lưu cài đặt</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('testEmail').addEventListener('click', function() {
    // Hiển thị thông báo đang kiểm tra
    alert('Chức năng kiểm tra kết nối email sẽ được triển khai sau.');
});
</script>

<?php require_once "includes/footer.php"; ?>
