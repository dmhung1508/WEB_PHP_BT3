<?php
$page_title = "Dashboard";
require_once "includes/header.php";

// Lấy thống kê
$stats = get_dashboard_stats();
$monthly_revenue = get_monthly_revenue_data();
$top_products = get_top_selling_products(5);
$order_status_data = get_order_status_data();
?>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Đơn hàng hôm nay</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['today_orders']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-cart3 fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Doanh thu hôm nay</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo format_currency($stats['today_revenue']); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-currency-dollar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Tổng sản phẩm</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_products']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-box-seam fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Tổng người dùng</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_users']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Doanh thu theo tháng</h6>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Trạng thái đơn hàng</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie">
                    <canvas id="orderStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Sản phẩm bán chạy</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Số lượng đã bán</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_products as $product): ?>
                            <tr>
                                <td><?php echo $product['name']; ?></td>
                                <td><?php echo $product['total_sold']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Đơn hàng gần đây</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Khách hàng</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Ngày đặt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $recent_orders_query = "SELECT o.*, u.name as user_name FROM orders o 
                                                   LEFT JOIN users u ON o.user_id = u.id 
                                                   ORDER BY o.created_at DESC LIMIT 5";
                            $recent_orders_result = mysqli_query($conn, $recent_orders_query);
                            
                            if ($recent_orders_result && mysqli_num_rows($recent_orders_result) > 0) {
                                while ($order = mysqli_fetch_assoc($recent_orders_result)) {
                            ?>
                            <tr>
                                <td><a href="order-view.php?id=<?php echo $order['id']; ?>">#<?php echo $order['id']; ?></a></td>
                                <td><?php echo $order['shipping_name']; ?></td>
                                <td><?php echo format_currency($order['total_price']); ?></td>
                                <td><?php echo get_order_status($order['status']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                            </tr>
                            <?php 
                                }
                            } else {
                                echo '<tr><td colspan="5" class="text-center">Không có đơn hàng nào.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Biểu đồ doanh thu theo tháng
var ctx = document.getElementById('revenueChart').getContext('2d');
var revenueChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: <?php echo json_encode($monthly_revenue); ?>,
            backgroundColor: 'rgba(78, 115, 223, 0.05)',
            borderColor: 'rgba(78, 115, 223, 1)',
            pointRadius: 3,
            pointBackgroundColor: 'rgba(78, 115, 223, 1)',
            pointBorderColor: 'rgba(78, 115, 223, 1)',
            pointHoverRadius: 3,
            pointHoverBackgroundColor: 'rgba(78, 115, 223, 1)',
            pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
            pointHitRadius: 10,
            pointBorderWidth: 2,
            tension: 0.3
        }]
    },
    options: {
        maintainAspectRatio: false,
        layout: {
            padding: {
                left: 10,
                right: 25,
                top: 25,
                bottom: 0
            }
        },
        scales: {
            x: {
                grid: {
                    display: false,
                    drawBorder: false
                }
            },
            y: {
                ticks: {
                    callback: function(value) {
                        return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + ' ₫';
                    }
                }
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        var label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.y !== null) {
                            label += new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(context.parsed.y);
                        }
                        return label;
                    }
                }
            }
        }
    }
});

// Biểu đồ trạng thái đơn hàng
var orderStatusData = <?php echo json_encode($order_status_data); ?>;
var orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
var orderStatusChart = new Chart(orderStatusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Chờ xử lý', 'Đang xử lý', 'Đang giao hàng', 'Đã giao hàng', 'Đã hủy'],
        datasets: [{
            data: [
                orderStatusData.pending,
                orderStatusData.processing,
                orderStatusData.shipped,
                orderStatusData.delivered,
                orderStatusData.cancelled
            ],
            backgroundColor: ['#f6c23e', '#4e73df', '#36b9cc', '#1cc88a', '#e74a3b'],
            hoverBackgroundColor: ['#f6c23e', '#4e73df', '#36b9cc', '#1cc88a', '#e74a3b'],
            hoverBorderColor: 'rgba(234, 236, 244, 1)',
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        },
        cutout: '70%'
    }
});
</script>

<?php require_once "includes/footer.php"; ?>
