<?php
// Start output buffering at the very beginning
ob_start();

$page_title = "Xuất dữ liệu";
require_once "includes/header.php";

// Thêm biến báo lỗi xuất dữ liệu
$error_message = '';

// Xử lý xuất dữ liệu
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $export_type = $_POST['export_type'];
    $date_from = $_POST['date_from'];
    $date_to = $_POST['date_to'];
    
    // Nếu không có ngày, sử dụng mặc định
    if (empty($date_from)) {
        $date_from = date('Y-m-01'); // Ngày đầu tháng hiện tại
    }
    
    if (empty($date_to)) {
        $date_to = date('Y-m-d'); // Ngày hiện tại
    }
    
    // Tạo điều kiện ngày
    $date_condition = " WHERE o.created_at BETWEEN '$date_from 00:00:00' AND '$date_to 23:59:59'";
    
    // Clean any output that might have been generated
    ob_clean();
    
    // Xác định loại xuất dữ liệu
    switch ($export_type) {
        case 'orders':
            if (!export_orders($date_from, $date_to, $date_condition)) {
                $error_message = "Không có dữ liệu đơn hàng để xuất hoặc có lỗi xảy ra.";
            }
            break;
        case 'products':
            if (!export_products($date_from, $date_to)) {
                $error_message = "Không có dữ liệu sản phẩm để xuất hoặc có lỗi xảy ra.";
            }
            break;
        case 'customers':
            if (!export_customers($date_from, $date_to, $date_condition)) {
                $error_message = "Không có dữ liệu khách hàng để xuất hoặc có lỗi xảy ra.";
            }
            break;
        case 'sales':
            if (!export_sales($date_from, $date_to, $date_condition)) {
                $error_message = "Không có dữ liệu báo cáo doanh thu để xuất hoặc có lỗi xảy ra.";
            }
            break;
        default:
            $error_message = "Loại xuất dữ liệu không hợp lệ.";
            break;
    }
}
// Hàm xuất đơn hàng
function export_orders($date_from, $date_to, $date_condition) {
    global $conn;
    
    // Tạo tên file
    $filename = "orders_" . date('Ymd') . ".csv";
    
    // Thiết lập header
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Mở output stream
    $output = fopen('php://output', 'w');
    
    // Thêm BOM (Byte Order Mark) để Excel hiển thị đúng tiếng Việt
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Thêm header cho file CSV
    fputcsv($output, array('Mã đơn hàng', 'Khách hàng', 'Email', 'Tổng tiền', 'Trạng thái', 'Phương thức thanh toán', 'Ngày đặt'));
    
    // Lấy dữ liệu đơn hàng
    $query = "SELECT o.id, o.shipping_name, u.email, o.total_price, o.status, o.payment_method, o.created_at 
              FROM orders o 
              LEFT JOIN users u ON o.user_id = u.id" . $date_condition . " 
              ORDER BY o.created_at DESC";
    $result = mysqli_query($conn, $query);
    $has_data = false;
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $has_data = true;
            // Định dạng trạng thái
            switch ($row['status']) {
                case 'pending': $status = 'Chờ xử lý'; break;
                case 'processing': $status = 'Đang xử lý'; break;
                case 'shipped': $status = 'Đang giao hàng'; break;
                case 'delivered': $status = 'Đã giao hàng'; break;
                case 'cancelled': $status = 'Đã hủy'; break;
                default: $status = 'Không xác định'; break;
            }
            
            // Ghi dữ liệu vào file CSV
            fputcsv($output, array(
                '#' . $row['id'],
                $row['shipping_name'],
                $row['email'],
                number_format($row['total_price'], 0, ',', '.') . ' ₫',
                $status,
                $row['payment_method'],
                date('d/m/Y H:i', strtotime($row['created_at']))
            ));
        }
    }
    
    // Đóng file và kết thúc
    fclose($output);
    if (!$has_data) return false;
    exit;
}

// Hàm xuất sản phẩm
function export_products($date_from, $date_to) {
    global $conn;
    
    // Tạo tên file
    $filename = "products_" . date('Ymd') . ".csv";
    
    // Thiết lập header
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Mở output stream
    $output = fopen('php://output', 'w');
    
    // Thêm BOM (Byte Order Mark) để Excel hiển thị đúng tiếng Việt
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Thêm header cho file CSV
    fputcsv($output, array('ID', 'Tên sản phẩm', 'Danh mục', 'Giá', 'Tồn kho', 'Đã bán', 'Doanh thu'));
    
    // Lấy dữ liệu sản phẩm
    $query = "SELECT p.id, p.name, c.name as category_name, p.price, p.stock, 
              COALESCE(SUM(oi.quantity), 0) as total_sold,
              COALESCE(SUM(oi.quantity * oi.price), 0) as total_revenue
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id
              LEFT JOIN order_items oi ON p.id = oi.product_id
              LEFT JOIN orders o ON oi.order_id = o.id AND o.created_at BETWEEN '$date_from 00:00:00' AND '$date_to 23:59:59'
              GROUP BY p.id
              ORDER BY total_sold DESC";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Ghi dữ liệu vào file CSV
            fputcsv($output, array(
                $row['id'],
                $row['name'],
                $row['category_name'],
                number_format($row['price'], 0, ',', '.') . ' ₫',
                $row['stock'],
                $row['total_sold'],
                number_format($row['total_revenue'], 0, ',', '.') . ' ₫'
            ));
        }
    }
    
    // Đóng file và kết thúc
    fclose($output);
    exit;
}

// Hàm xuất khách hàng
function export_customers($date_from, $date_to, $date_condition) {
    global $conn;
    
    // Tạo tên file
    $filename = "customers_" . date('Ymd') . ".csv";
    
    // Thiết lập header
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Mở output stream
    $output = fopen('php://output', 'w');
    
    // Thêm BOM (Byte Order Mark) để Excel hiển thị đúng tiếng Việt
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Thêm header cho file CSV
    fputcsv($output, array('ID', 'Tên khách hàng', 'Email', 'Số điện thoại', 'Địa chỉ', 'Số đơn hàng', 'Tổng chi tiêu', 'Ngày đăng ký'));
    
    // Lấy dữ liệu khách hàng
    $query = "SELECT u.id, u.name, u.email, u.phone, u.address, u.created_at,
              COUNT(o.id) as total_orders,
              COALESCE(SUM(o.total_price), 0) as total_spent
              FROM users u
              LEFT JOIN orders o ON u.id = o.user_id" . str_replace("created_at", "o.created_at", $date_condition) . "
              WHERE u.role = 'customer'
              GROUP BY u.id
              ORDER BY total_spent DESC";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Ghi dữ liệu vào file CSV
            fputcsv($output, array(
                $row['id'],
                $row['name'],
                $row['email'],
                $row['phone'],
                $row['address'],
                $row['total_orders'],
                number_format($row['total_spent'], 0, ',', '.') . ' ₫',
                date('d/m/Y', strtotime($row['created_at']))
            ));
        }
    }
    
    // Đóng file và kết thúc
    fclose($output);
    exit;
}

// Hàm xuất báo cáo doanh thu
function export_sales($date_from, $date_to, $date_condition) {
    global $conn;
    
    // Tạo tên file
    $filename = "sales_report_" . date('Ymd') . ".csv";
    
    // Thiết lập header
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Mở output stream
    $output = fopen('php://output', 'w');
    
    // Thêm BOM (Byte Order Mark) để Excel hiển thị đúng tiếng Việt
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Thêm header cho file CSV
    fputcsv($output, array('Ngày', 'Số đơn hàng', 'Doanh thu', 'Sản phẩm đã bán'));
    
    // Lấy dữ liệu doanh thu theo ngày
    $query = "SELECT DATE(o.created_at) as order_date,
              COUNT(DISTINCT o.id) as order_count,
              SUM(o.total_price) as revenue,
              SUM(oi.quantity) as items_sold
              FROM orders o
              JOIN order_items oi ON o.id = oi.order_id
              WHERE o.status != 'cancelled' AND o.created_at BETWEEN '$date_from 00:00:00' AND '$date_to 23:59:59'
              GROUP BY DATE(o.created_at)
              ORDER BY order_date DESC";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Ghi dữ liệu vào file CSV
            fputcsv($output, array(
                date('d/m/Y', strtotime($row['order_date'])),
                $row['order_count'],
                number_format($row['revenue'], 0, ',', '.') . ' ₫',
                $row['items_sold']
            ));
        }
    }
    
    // Đóng file và kết thúc
    fclose($output);
    exit;
}
?>

<style>
.export-center-wrapper {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  min-height: 80vh;
  width: 100%;
}
.export-form-card {
  width: 100%;
  max-width: 500px;
  margin: 0 auto;
  border-radius: 16px;
  box-shadow: 0 4px 24px 0 rgba(0,0,0,0.08);
  background: #fff;
  padding: 32px 24px;
}
@media (max-width: 600px) {
  .export-form-card { padding: 16px 4px; }
}
.export-loading {
  display: none;
  text-align: center;
  margin-top: 16px;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const form = document.querySelector('form');
  if (form) {
    form.addEventListener('submit', function() {
      const loading = document.getElementById('export-loading');
      if (loading) loading.style.display = 'block';
      setTimeout(function() {
        if (loading) loading.style.display = 'none';
      }, 3000); // 3 giây sau sẽ tự ẩn loading
    });
  }
});
</script>

<div class="export-center-wrapper">
  <div class="export-form-card">
    <h1 class="h4 mb-4 text-center">Xuất dữ liệu</h1>
    <?php if (isset($error_message) && $error_message): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
      <div class="mb-3">
        <label for="export_type" class="form-label">Loại dữ liệu <span class="text-danger">*</span></label>
        <select class="form-select" id="export_type" name="export_type" required>
          <option value="">-- Chọn loại dữ liệu --</option>
          <option value="orders">Đơn hàng</option>
          <option value="products">Sản phẩm</option>
          <option value="customers">Khách hàng</option>
          <option value="sales">Báo cáo doanh thu</option>
        </select>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="date_from" class="form-label">Từ ngày</label>
          <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo date('Y-m-01'); ?>">
        </div>
        <div class="col-md-6 mb-3">
          <label for="date_to" class="form-label">Đến ngày</label>
          <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo date('Y-m-d'); ?>">
        </div>
      </div>
      <div class="d-grid">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-download"></i> Xuất dữ liệu
        </button>
      </div>
      <div class="export-loading" id="export-loading">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Đang xuất dữ liệu...</span>
        </div>
        <div class="mt-2">Đang xuất dữ liệu, vui lòng chờ...</div>
      </div>
    </form>
    <div class="mt-4">
      <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> Dữ liệu sẽ được xuất ra file CSV, có thể mở bằng Excel hoặc các phần mềm tương tự.
      </div>
      <ul>
        <li><b>Đơn hàng:</b> Xuất danh sách đơn hàng trong khoảng thời gian đã chọn, bao gồm thông tin khách hàng, tổng tiền, trạng thái và phương thức thanh toán.</li>
        <li><b>Sản phẩm:</b> Xuất danh sách sản phẩm cùng với số lượng đã bán và doanh thu trong khoảng thời gian đã chọn.</li>
        <li><b>Khách hàng:</b> Xuất danh sách khách hàng cùng với số đơn hàng và tổng chi tiêu trong khoảng thời gian đã chọn.</li>
        <li><b>Báo cáo doanh thu:</b> Xuất báo cáo doanh thu theo ngày trong khoảng thời gian đã chọn, bao gồm số đơn hàng, doanh thu và số sản phẩm đã bán.</li>
      </ul>
    </div>
  </div>
</div>

<?php require_once "includes/footer.php"; ?>
