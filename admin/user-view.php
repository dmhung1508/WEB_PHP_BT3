<?php
$page_title = "Chi tiết người dùng";
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

// Lấy danh sách đơn hàng của người dùng
$orders_query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
$orders_result = mysqli_query($conn, $orders_query);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-3">
    <h1 class="h2">Chi tiết người dùng</h1>
    <a href="users.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Quay lại
    </a>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Thông tin người dùng</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="fw-bold">ID:</label>
                    <p class="form-control"><?php echo $user['id']; ?></p>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Họ tên:</label>
                    <p class="form-control"><?php echo $user['name']; ?></p>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Email:</label>
                    <p class="form-control"><?php echo $user['email']; ?></p>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Số điện thoại:</label>
                    <p class="form-control"><?php echo !empty($user['phone']) ? $user['phone'] : 'Chưa cập nhật'; ?></p>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Địa chỉ:</label>
                    <p class="form-control"><?php echo !empty($user['address']) ? $user['address'] : 'Chưa cập nhật'; ?></p>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Ngày đăng ký:</label>
                    <p class="form-control"><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Thống kê</h6>
            </div>
            <div class="card-body">
                <?php
                // Tổng số đơn hàng
                $total_orders = mysqli_num_rows($orders_result);
                
                // Tổng chi tiêu
                $total_spent_query = "SELECT SUM(total_price) as total FROM orders WHERE user_id = $user_id AND status != 'cancelled'";
                $total_spent_result = mysqli_query($conn, $total_spent_query);
                $total_spent = 0;
                
                if ($total_spent_result && mysqli_num_rows($total_spent_result) > 0) {
                    $row = mysqli_fetch_assoc($total_spent_result);
                    $total_spent = $row['total'] ? $row['total'] : 0;
                }
                
                // Số đơn hàng theo trạng thái
                $status_query = "SELECT status, COUNT(*) as count FROM orders WHERE user_id = $user_id GROUP BY status";
                $status_result = mysqli_query($conn, $status_query);
                $status_counts = [];
                
                if ($status_result) {
                    while ($row = mysqli_fetch_assoc($status_result)) {
                        $status_counts[$row['status']] = $row['count'];
                    }
                }
                ?>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Tổng đơn hàng</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_orders; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-cart3 fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Tổng chi tiêu</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo format_currency($total_spent); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-currency-dollar fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <h6 class="font-weight-bold">Đơn hàng theo trạng thái</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Trạng thái</th>
                                    <th>Số lượng</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Chờ xử lý</td>
                                    <td><?php echo isset($status_counts['pending']) ? $status_counts['pending'] : 0; ?></td>
                                </tr>
                                <tr>
                                    <td>Đang xử lý</td>
                                    <td><?php echo isset($status_counts['processing']) ? $status_counts['processing'] : 0; ?></td>
                                </tr>
                                <tr>
                                    <td>Đang giao hàng</td>
                                    <td><?php echo isset($status_counts['shipped']) ? $status_counts['shipped'] : 0; ?></td>
                                </tr>
                                <tr>
                                    <td>Đã giao hàng</td>
                                    <td><?php echo isset($status_counts['delivered']) ? $status_counts['delivered'] : 0; ?></td>
                                </tr>
                                <tr>
                                    <td>Đã hủy</td>
                                    <td><?php echo isset($status_counts['cancelled']) ? $status_counts['cancelled'] : 0; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Lịch sử đơn hàng</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orders_result && mysqli_num_rows($orders_result) > 0): ?>
                        <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                <td><?php echo format_currency($order['total_price']); ?></td>
                                <td><?php echo get_order_status($order['status']); ?></td>
                                <td>
                                    <a href="order-view.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> Xem
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Người dùng chưa có đơn hàng nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once "includes/footer.php"; ?>
