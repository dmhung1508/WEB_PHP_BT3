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

// Lấy danh sách đơn hàng của người dùng
$orders = get_orders($_SESSION['user_id']);

$page_title = "Đơn hàng của tôi";
require_once "includes/header.php";
?>

<div class="container py-5">
    <h1 class="mb-4">Đơn hàng của tôi</h1>
    
    <?php if (empty($orders)): ?>
        <div class="alert alert-info">
            Bạn chưa có đơn hàng nào. <a href="index.php">Bắt đầu mua sắm</a>.
        </div>
    <?php else: ?>
        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Mã đơn hàng</th>
                                <th>Ngày đặt</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                    <td><?php echo format_currency($order['total_price']); ?></td>
                                    <td><?php echo get_order_status($order['status']); ?></td>
                                    <td>
                                        <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> Xem chi tiết
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once "includes/footer.php"; ?>
