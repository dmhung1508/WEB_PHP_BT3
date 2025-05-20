<?php
$page_title = "Báo cáo & Thống kê";
require_once "includes/header.php";

// Xác định khoảng thời gian báo cáo
$period = isset($_GET['period']) ? $_GET['period'] : 'month';
$current_date = date('Y-m-d');

switch ($period) {
    case 'week':
        $start_date = date('Y-m-d', strtotime('-7 days'));
        $period_title = '7 ngày qua';
        break;
    case 'month':
        $start_date = date('Y-m-01');
        $period_title = 'Tháng này';
        break;
    case 'quarter':
        $current_month = date('n');
        $current_quarter = ceil($current_month / 3);
        $first_month_of_quarter = ($current_quarter - 1) * 3 + 1;
        $start_date = date('Y-' . str_pad($first_month_of_quarter, 2, '0', STR_PAD_LEFT) . '-01');
        $period_title = 'Quý này';
        break;
    case 'year':
        $start_date = date('Y-01-01');
        $period_title = 'Năm nay';
        break;
    case 'custom':
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
        $current_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
        $period_title = 'Tùy chỉnh';
        break;
    default:
        $start_date = date('Y-m-01');
        $period_title = 'Tháng này';
        break;
}

// Lấy dữ liệu cho biểu đồ doanh thu theo ngày
$daily_revenue_data = get_daily_revenue_data($start_date, $current_date);

// Lấy dữ liệu cho biểu đồ sản phẩm bán chạy
$top_products = get_top_selling_products_by_period(10, $start_date, $current_date);

// Lấy dữ liệu cho biểu đồ trạng thái đơn hàng
$order_status_data = get_order_status_data_by_period($start_date, $current_date);

// Lấy dữ liệu cho biểu đồ doanh thu theo danh mục
$category_revenue_data = get_category_revenue_data($start_date, $current_date);

// Lấy tổng quan thống kê
$stats = get_period_stats($start_date, $current_date);

// Hàm lấy dữ liệu doanh thu theo ngày
function get_daily_revenue_data($start_date, $end_date) {
    global $conn;
    
    $data = [];
    $labels = [];
    
    $current = new DateTime($start_date);
    $end = new DateTime($end_date);
    $end->modify('+1 day'); // Để bao gồm ngày cuối
    
    $interval = DateInterval::createFromDateString('1 day');
    $period = new DatePeriod($current, $interval, $end);
    
    foreach ($period as $date) {
        $date_str = $date->format('Y-m-d');
        $labels[] = $date->format('d/m');
        
        $query = "SELECT SUM(total_price) as revenue FROM orders 
                  WHERE DATE(created_at) = '$date_str' 
                  AND status != 'cancelled'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $data[] = $row['revenue'] ? (float)$row['revenue'] : 0;
        } else {
            $data[] = 0;
        }
    }
    
    return [
        'labels' => $labels,
        'data' => $data
    ];
}

// Hàm lấy dữ liệu sản phẩm bán chạy theo khoảng thời gian
function get_top_selling_products_by_period($limit, $start_date, $end_date) {
    global $conn;
    
    $query = "SELECT p.id, p.name, SUM(oi.quantity) as total_sold, SUM(oi.quantity * oi.price) as total_revenue
              FROM products p 
              JOIN order_items oi ON p.id = oi.product_id 
              JOIN orders o ON oi.order_id = o.id 
              WHERE o.status != 'cancelled' 
              AND o.created_at BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59' 
              GROUP BY p.id 
              ORDER BY total_sold DESC 
              LIMIT $limit";
    $result = mysqli_query($conn, $query);
    
    $products = [];
    $labels = [];
    $data = [];
    $colors = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
            $labels[] = $row['name'];
            $data[] = (int)$row['total_sold'];
            
            // Tạo màu ngẫu nhiên
            $colors[] = 'rgba(' . rand(0, 200) . ',' . rand(0, 200) . ',' . rand(0, 200) . ', 0.7)';
        }
    }
    
    return [
        'products' => $products,
        'chart_data' => [
            'labels' => $labels,
            'data' => $data,
            'colors' => $colors
        ]
    ];
}

// Hàm lấy dữ liệu trạng thái đơn hàng theo khoảng thời gian
function get_order_status_data_by_period($start_date, $end_date) {
    global $conn;
    
    $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    $data = [];
    $colors = [
        'pending' => '#f6c23e',
        'processing' => '#4e73df',
        'shipped' => '#36b9cc',
        'delivered' => '#1cc88a',
        'cancelled' => '#e74a3b'
    ];
    
    foreach ($statuses as $status) {
        $query = "SELECT COUNT(*) as count FROM orders 
                  WHERE status = '$status' 
                  AND created_at BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $data[$status] = (int)$row['count'];
        } else {
            $data[$status] = 0;
        }
    }
    
    return [
        'data' => array_values($data),
        'labels' => ['Chờ xử lý', 'Đang xử lý', 'Đang giao hàng', 'Đã giao hàng', 'Đã hủy'],
        'colors' => array_values($colors)
    ];
}

// Hàm lấy dữ liệu doanh thu theo danh mục
function get_category_revenue_data($start_date, $end_date) {
    global $conn;
    
    $query = "SELECT c.name, SUM(oi.quantity * oi.price) as revenue
              FROM categories c
              JOIN products p ON c.id = p.category_id
              JOIN order_items oi ON p.id = oi.product_id
              JOIN orders o ON oi.order_id = o.id
              WHERE o.status != 'cancelled'
              AND o.created_at BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'
              GROUP BY c.id
              ORDER BY revenue DESC";
    $result = mysqli_query($conn, $query);
    
    $labels = [];
    $data = [];
    $colors = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $labels[] = $row['name'];
            $data[] = (float)$row['revenue'];
            
            // Tạo màu ngẫu nhiên
            $colors[] = 'rgba(' . rand(0, 200) . ',' . rand(0, 200) . ',' . rand(0, 200) . ', 0.7)';
        }
    }
    
    return [
        'labels' => $labels,
        'data' => $data,
        'colors' => $colors
    ];
}

// Hàm lấy thống kê tổng quan theo khoảng thời gian
function get_period_stats($start_date, $end_date) {
    global $conn;
    
    // Tổng số đơn hàng
    $query = "SELECT COUNT(*) as total_orders FROM orders 
              WHERE created_at BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'";
    $result = mysqli_query($conn, $query);
    $total_orders = 0;
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $total_orders = $row['total_orders'];
    }
    
    // Tổng doanh thu
    $query = "SELECT SUM(total_price) as total_revenue FROM orders 
              WHERE status != 'cancelled' 
              AND created_at BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'";
    $result = mysqli_query($conn, $query);
    $total_revenue = 0;
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $total_revenue = $row['total_revenue'] ? $row['total_revenue'] : 0;
    }
    
    // Số sản phẩm đã bán
    $query = "SELECT SUM(oi.quantity) as total_items_sold 
              FROM order_items oi 
              JOIN orders o ON oi.order_id = o.id 
              WHERE o.status != 'cancelled' 
              AND o.created_at BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'";
    $result = mysqli_query($conn, $query);
    $total_items_sold = 0;
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $total_items_sold = $row['total_items_sold'] ? $row['total_items_sold'] : 0;
    }
    
    // Số khách hàng mới
    $query = "SELECT COUNT(*) as new_customers FROM users 
              WHERE role = 'customer' 
              AND created_at BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'";
    $result = mysqli_query($conn, $query);
    $new_customers = 0;
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $new_customers = $row['new_customers'];
    }
    
    // Giá trị đơn hàng trung bình
    $avg_order_value = $total_orders > 0 ? $total_revenue / $total_orders : 0;
    
    return [
        'total_orders' => $total_orders,
        'total_revenue' => $total_revenue,
        'total_items_sold' => $total_items_sold,
        'new_customers' => $new_customers,
        'avg_order_value' => $avg_order_value
    ];
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-3">
    <h1 class="h2">Báo cáo & Thống kê</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="export.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-download"></i> Xuất dữ liệu
            </a>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer"></i> In báo cáo
            </button>
        </div>
    </div>
</div>

<!-- Filter Controls -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="period" class="form-label">Khoảng thời gian</label>
                <select class="form-select" id="period" name="period" onchange="toggleCustomDateInputs()">
                    <option value="week" <?php echo $period == 'week' ? 'selected' : ''; ?>>7 ngày qua</option>
                    <option value="month" <?php echo $period == 'month' ? 'selected' : ''; ?>>Tháng này</option>
                    <option value="quarter" <?php echo $period == 'quarter' ? 'selected' : ''; ?>>Quý này</option>
                    <option value="year" <?php echo $period == 'year' ? 'selected' : ''; ?>>Năm nay</option>
                    <option value="custom" <?php echo $period == 'custom' ? 'selected' : ''; ?>>Tùy chỉnh</option>
                </select>
            </div>
            
            <div class="col-md-3 custom-date-input" style="display: <?php echo $period == 'custom' ? 'block' : 'none'; ?>">
                <label for="start_date" class="form-label">Từ ngày</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
            </div>
            
            <div class="col-md-3 custom-date-input" style="display: <?php echo $period == 'custom' ? 'block' : 'none'; ?>">
                <label for="end_date" class="form-label">Đến ngày</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $current_date; ?>">
            </div>
            
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-filter"></i> Lọc
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Stats Overview -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Doanh thu (<?php echo $period_title; ?>)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo format_currency($stats['total_revenue']); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-currency-dollar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Đơn hàng (<?php echo $period_title; ?>)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_orders']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-cart3 fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Sản phẩm đã bán</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_items_sold']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-box-seam fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Khách hàng mới</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['new_customers']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Doanh thu theo ngày</h6>
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
                <h6 class="m-0 font-weight-bold text-primary">Top 10 sản phẩm bán chạy</h6>
            </div>
            <div class="card-body">
                <div class="chart-bar">
                    <canvas id="topProductsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Doanh thu theo danh mục</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie">
                    <canvas id="categoryRevenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Products Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Chi tiết sản phẩm bán chạy</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Số lượng đã bán</th>
                        <th>Doanh thu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($top_products['products'] as $product): ?>
                    <tr>
                        <td><?php echo $product['name']; ?></td>
                        <td><?php echo $product['total_sold']; ?></td>
                        <td><?php echo format_currency($product['total_revenue']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Hiển thị/ẩn trường ngày tùy chỉnh
function toggleCustomDateInputs() {
    const periodSelect = document.getElementById('period');
    const customDateInputs = document.querySelectorAll('.custom-date-input');
    
    if (periodSelect.value === 'custom') {
        customDateInputs.forEach(input => input.style.display = 'block');
    } else {
        customDateInputs.forEach(input => input.style.display = 'none');
    }
}

// Biểu đồ doanh thu theo ngày
var ctx = document.getElementById('revenueChart').getContext('2d');
var revenueChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($daily_revenue_data['labels']); ?>,
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: <?php echo json_encode($daily_revenue_data['data']); ?>,
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
var orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
var orderStatusChart = new Chart(orderStatusCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($order_status_data['labels']); ?>,
        datasets: [{
            data: <?php echo json_encode($order_status_data['data']); ?>,
            backgroundColor: <?php echo json_encode($order_status_data['colors']); ?>,
            hoverBackgroundColor: <?php echo json_encode($order_status_data['colors']); ?>,
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

// Biểu đồ sản phẩm bán chạy
var topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
var topProductsChart = new Chart(topProductsCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($top_products['chart_data']['labels']); ?>,
        datasets: [{
            label: 'Số lượng đã bán',
            data: <?php echo json_encode($top_products['chart_data']['data']); ?>,
            backgroundColor: <?php echo json_encode($top_products['chart_data']['colors']); ?>,
            borderWidth: 1
        }]
    },
    options: {
        maintainAspectRatio: false,
        scales: {
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// Biểu đồ doanh thu theo danh mục
var categoryRevenueCtx = document.getElementById('categoryRevenueChart').getContext('2d');
var categoryRevenueChart = new Chart(categoryRevenueCtx, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode($category_revenue_data['labels']); ?>,
        datasets: [{
            data: <?php echo json_encode($category_revenue_data['data']); ?>,
            backgroundColor: <?php echo json_encode($category_revenue_data['colors']); ?>,
            hoverBackgroundColor: <?php echo json_encode($category_revenue_data['colors']); ?>,
            hoverBorderColor: 'rgba(234, 236, 244, 1)',
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        var label = context.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed !== null) {
                            label += new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(context.parsed);
                        }
                        return label;
                    }
                }
            }
        }
    }
});
</script>

<?php require_once "includes/footer.php"; ?>
