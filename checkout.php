<?php
require_once "includes/db.php";
require_once "includes/functions.php";

start_session_if_not_started();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = 'checkout.php';
    header("Location: login.php");
    exit();
}

// Kiểm tra giỏ hàng
$cart_items = get_cart_items();
if (empty($cart_items)) {
    header("Location: cart.php");
    exit();
}

$cart_total = get_cart_total();
$user = get_logged_in_user();

// Xử lý đặt hàng
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $shipping_name = trim($_POST['shipping_name']);
    $shipping_phone = trim($_POST['shipping_phone']);
    $shipping_address = trim($_POST['shipping_address']);
    $payment_method = trim($_POST['payment_method']);
    $notes = trim($_POST['notes']);
    
    $errors = [];
    
    // Kiểm tra thông tin
    if (empty($shipping_name)) {
        $errors[] = "Vui lòng nhập tên người nhận.";
    }
    
    if (empty($shipping_phone)) {
        $errors[] = "Vui lòng nhập số điện thoại.";
    }
    
    if (empty($shipping_address)) {
        $errors[] = "Vui lòng nhập địa chỉ giao hàng.";
    }
    
    if (empty($payment_method)) {
        $errors[] = "Vui lòng chọn phương thức thanh toán.";
    }
    
    // Nếu không có lỗi, tiến hành đặt hàng
    if (empty($errors)) {
        // Tạo đơn hàng
        $query = "INSERT INTO orders (user_id, total_price, status, payment_method, shipping_address, shipping_phone, shipping_name, notes) 
          VALUES (?, ?, 'pending', ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "idsssss", $_SESSION['user_id'], $cart_total, $payment_method, $shipping_address, $shipping_phone, $shipping_name, $notes);
        
        if (mysqli_stmt_execute($stmt)) {
            $order_id = mysqli_insert_id($conn);
            
            // Thêm chi tiết đơn hàng
            foreach ($cart_items as $item) {
                // Kiểm tra tồn kho trước khi trừ
                if ($item['quantity'] > $item['stock']) {
                    $errors[] = "Sản phẩm '{$item['name']}' không đủ hàng trong kho. Vui lòng giảm số lượng hoặc chọn sản phẩm khác.";
                    break;
                }
            }
            if (empty($errors)) {
                foreach ($cart_items as $item) {
                    $query = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
                    mysqli_stmt_execute($stmt);

                    // Cập nhật số lượng tồn kho, không cho âm
                    $new_stock = max(0, $item['stock'] - $item['quantity']);
                    $query = "UPDATE products SET stock = ? WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "ii", $new_stock, $item['id']);
                    mysqli_stmt_execute($stmt);

                    // Nếu tồn kho mới < 10 thì gửi mail cảnh báo
                    if ($new_stock < 10) {
                        $admin_email = 'dinhhung15082004@gmail.com'; // Thay bằng email admin thực tế
                        $subject = "[Cảnh báo] Sản phẩm '{$item['name']}' sắp hết hàng";
                        $text = "Sản phẩm '{$item['name']}' chỉ còn {$new_stock} trong kho. Vui lòng nhập thêm hàng.";
                        $product_info = [
                            'id' => $item['id'],
                            'name' => $item['name'],
                            'image' => $item['image'],
                            'stock' => $new_stock
                        ];
                        send_mailgun_email($admin_email, $subject, $text, null, $product_info);
                    }
                }

                // Xóa giỏ hàng
                clear_cart();

                // Chuyển hướng đến trang cảm ơn
                header("Location: order-success.php?id=$order_id");
                exit();
            }
        } else {
            $errors[] = "Có lỗi xảy ra khi đặt hàng. Vui lòng thử lại.";
        }
        
        mysqli_stmt_close($stmt);
    }
}

$page_title = "Thanh toán";
require_once "includes/header.php";
?>

<div class="container py-5">
    <h1 class="mb-4">Thanh toán</h1>
    
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
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Thông tin giao hàng</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="shipping_name" class="form-label">Tên người nhận <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="shipping_name" name="shipping_name" value="<?php echo $user['name']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="shipping_phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="shipping_phone" name="shipping_phone" value="<?php echo $user['phone']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="shipping_address" class="form-label">Địa chỉ giao hàng <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" required><?php echo $user['address']; ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Ghi chú</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Phương thức thanh toán</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="payment_cod" value="COD" checked>
                            <label class="form-check-label" for="payment_cod">
                                Thanh toán khi nhận hàng (COD)
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="payment_banking" value="Banking">
                            <label class="form-check-label" for="payment_banking">
                                Chuyển khoản ngân hàng
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="payment_momo" value="MoMo">
                            <label class="form-check-label" for="payment_momo">
                                Ví điện tử MoMo
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Đơn hàng của bạn</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th>Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart_items as $item): ?>
                                        <tr>
                                            <td>
                                                <?php echo $item['name']; ?> <strong>× <?php echo $item['quantity']; ?></strong>
                                            </td>
                                            <td><?php echo format_currency($item['subtotal']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Tạm tính</th>
                                        <td><?php echo format_currency($cart_total); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Phí vận chuyển</th>
                                        <td>Miễn phí</td>
                                    </tr>
                                    <tr>
                                        <th>Tổng cộng</th>
                                        <td><strong><?php echo format_currency($cart_total); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="d-grid gap-2 mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Đặt hàng
                            </button>
                            <a href="cart.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Quay lại giỏ hàng
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once "includes/footer.php"; ?>
