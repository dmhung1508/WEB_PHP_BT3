<?php
$page_title = "Chi tiết đơn hàng";
require_once "includes/header.php";

// Kiểm tra id đơn hàng
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: orders.php");
    exit();
}

$order_id = $_GET['id'];
$order = get_order($order_id);

if (!$order) {
    header("Location: orders.php");
    exit();
}

// Xử lý cập nhật trạng thái đơn hàng
if (isset($_POST['update_status'])) {
    $status = $_POST['status'];
    
    $query = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $status, $order_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $success_message = "Trạng thái đơn hàng đã được cập nhật thành công.";
        // Cập nhật thông tin đơn hàng
        $order = get_order($order_id);
    } else {
        $error_message = "Có lỗi xảy ra khi cập nhật trạng thái đơn hàng: " . mysqli_error($conn);
    }
    
    mysqli_stmt_close($stmt);
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-3">
    <h1 class="h2">Chi tiết đơn hàng #<?php echo $order_id; ?></h1>
    <a href="orders.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Quay lại
    </a>
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

<div class="row">
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Thông tin đơn hàng</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Mã đơn hàng:</strong> #<?php echo $order['id']; ?></p>
                        <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                        <p><strong>Trạng thái:</strong> <?php echo get_order_status($order['status']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Tổng tiền:</strong> <?php echo format_currency($order['total_price']); ?></p>
                        <p><strong>Phương thức thanh toán:</strong> <?php echo $order['payment_method']; ?></p>
                        <p><strong>Ghi chú:</strong> <?php echo !empty($order['notes']) ? $order['notes'] : 'Không có'; ?></p>
                    </div>
                </div>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $order_id; ?>" method="post" class="mb-4">
                    <div class="row align-items-end">
                        <div class="col-md-6">
                            <label for="status" class="form-label">Cập nhật trạng thái</label>
                            <select class="form-select" id="status" name="status">
                                <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                                <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                                <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Đang giao hàng</option>
                                <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Đã giao hàng</option>
                                <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" name="update_status" class="btn btn-primary">
                                <i class="bi bi-save"></i> Cập nhật trạng thái
                            </button>
                        </div>
                    </div>
                </form>
                
                <h6 class="font-weight-bold">Sản phẩm trong đơn hàng</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th width="80">Ảnh</th>
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
                                        <?php if (!empty($item['product_image'])): ?>
                                            <img src="<?php echo get_product_image_url($item['product_image']); ?>" alt="<?php echo $item['product_name']; ?>" class="img-thumbnail" width="60">
                                        <?php else: ?>
                                            <img src="../assets/img/no-image.jpg" alt="No Image" class="img-thumbnail" width="60">
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $item['product_name']; ?></td>
                                    <td><?php echo format_currency($item['price']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><?php echo format_currency($item['price'] * $item['quantity']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Tổng cộng:</strong></td>
                                <td><strong><?php echo format_currency($order['total_price']); ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Thông tin khách hàng</h6>
            </div>
            <div class="card-body">
                <p><strong>Tên:</strong> <?php echo $order['shipping_name']; ?></p>
                <p><strong>Số điện thoại:</strong> <?php echo $order['shipping_phone']; ?></p>
                <p><strong>Địa chỉ:</strong> <?php echo $order['shipping_address']; ?></p>
                <?php if (!empty($order['user_email'])): ?>
                    <p><strong>Email:</strong> <?php echo $order['user_email']; ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Thao tác</h6>
            </div>
            <div class="card-body">
                <a href="#" class="btn btn-primary btn-block mb-2" onclick="window.print();">
                    <i class="bi bi-printer"></i> In đơn hàng
                </a>
                <a href="orders.php" class="btn btn-secondary btn-block">
                    <i class="bi bi-arrow-left"></i> Quay lại danh sách
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once "includes/footer.php"; ?>
