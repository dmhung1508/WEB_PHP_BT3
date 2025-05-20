<?php
require_once "includes/db.php";
require_once "includes/functions.php";

start_session_if_not_started();

// Kiểm tra id đơn hàng
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$order_id = $_GET['id'];
$order = get_order($order_id);

// Kiểm tra đơn hàng tồn tại và thuộc về người dùng hiện tại
if (!$order || (isset($_SESSION['user_id']) && $order['user_id'] != $_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$page_title = "Đặt hàng thành công";
require_once "includes/header.php";
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                    <h1 class="mt-3">Đặt hàng thành công!</h1>
                    <p class="lead">Cảm ơn bạn đã đặt hàng. Đơn hàng của bạn đã được tiếp nhận và đang được xử lý.</p>
                    <p>Mã đơn hàng: <strong>#<?php echo $order_id; ?></strong></p>
                    <p>Bạn sẽ nhận được email xác nhận đơn hàng trong thời gian sớm nhất.</p>
                    <div class="mt-4">
                        <a href="my-orders.php" class="btn btn-primary">Xem đơn hàng của tôi</a>
                        <a href="index.php" class="btn btn-outline-primary ms-2">Tiếp tục mua sắm</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once "includes/footer.php"; ?>
