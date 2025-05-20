<?php
require_once "includes/db.php";
require_once "includes/functions.php";

start_session_if_not_started();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = 'my-orders.php';
    header("Location: login.php");
    exit();
}

// Kiểm tra id đơn hàng
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: my-orders.php");
    exit();
}

$order_id = $_GET['id'];
$order = get_order($order_id);

// Kiểm tra đơn hàng tồn tại và thuộc về người dùng hiện tại
if (!$order || $order['user_id'] != $_SESSION['user_id']) {
    header("Location: my-orders.php");
    exit();
}

$page_title = "Chi tiết đơn hàng #" . $order_id;
require_once "includes/header.php";
?>

<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="my-orders.php">Đơn hàng của tôi</a></li>
            <li class="breadcrumb-item active" aria-current="page">Chi tiết đơn hàng #<?php echo $order_id; ?></li>
        </ol>
    </nav>
    
    <h1 class="mb-4">Chi tiết đơn hàng #<?php echo $order_id; ?></h1>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Thông tin đơn hàng</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Mã đơn hàng:</strong> #<?php echo $order['id']; ?></p>
                            <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                            <p><strong>Trạng thái:</strong> <?php echo get_order_status($order['status']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Phương thức thanh toán:</strong> <?php echo $order['payment_method']; ?></p>
                            <p><strong>Tổng tiền:</strong> <?php echo format_currency($order['total_price']); ?></p>
                            <?php if (!empty($order['notes'])): ?>
                                <p><strong>Ghi chú:</strong> <?php echo $order['notes']; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <h6 class="mb-3">Sản phẩm đã đặt</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Giá</th>
                                    <th>Số lượng</th>
                                    <th>Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order['items'] as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($item['product_image'])): ?>
                                                    <img src="uploads/product-images/<?php echo $item['product_image']; ?>" alt="<?php echo $item['product_name']; ?>" class="cart-item-img me-3">
                                                <?php else: ?>
                                                    <img src="assets/img/no-image.jpg" alt="No Image" class="cart-item-img me-3">
                                                <?php endif; ?>
                                                <div>
                                                    <h6 class="mb-0"><?php echo $item['product_name']; ?></h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo format_currency($item['price']); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><?php echo format_currency($item['price'] * $item['quantity']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Tổng cộng:</strong></td>
                                    <td><strong><?php echo format_currency($order['total_price']); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Thông tin giao hàng</h5>
                </div>
                <div class="card-body">
                    <p><strong>Tên người nhận:</strong> <?php echo $order['shipping_name']; ?></p>
                    <p><strong>Số điện thoại:</strong> <?php echo $order['shipping_phone']; ?></p>
                    <p><strong>Địa chỉ giao hàng:</strong> <?php echo $order['shipping_address']; ?></p>
                </div>
            </div>
            
            <div class="card shadow">
                <div class="card-body">
                    <a href="my-orders.php" class="btn btn-primary w-100">
                        <i class="bi bi-arrow-left"></i> Quay lại danh sách đơn hàng
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once "includes/footer.php"; ?>
