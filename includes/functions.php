<?php
// Bắt đầu session nếu chưa được bắt đầu
function start_session_if_not_started() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Hàm kiểm tra đăng nhập
function check_login() {
    start_session_if_not_started();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

// Hàm kiểm tra đăng nhập admin
function check_admin() {
    start_session_if_not_started();
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
        header("Location: ../login.php");
        exit();
    }
}

// Hàm lấy thông tin người dùng hiện tại
function get_logged_in_user() {
    global $conn;
    start_session_if_not_started();
    
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $query = "SELECT * FROM users WHERE id = $user_id";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
    }
    
    return null;
}

// Hàm tạo slug từ chuỗi
function create_slug($string) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
    return $slug;
}

// Hàm upload ảnh - Đã được tối ưu hóa và sửa lỗi
function upload_image($file) {
    // Kiểm tra lỗi upload
    if (!isset($file) || $file['error'] != UPLOAD_ERR_OK) {
        $error_message = "Lỗi upload: ";
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
                $error_message .= "Kích thước file vượt quá giới hạn upload_max_filesize trong php.ini.";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $error_message .= "Kích thước file vượt quá giới hạn MAX_FILE_SIZE trong form HTML.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $error_message .= "File chỉ được tải lên một phần.";
                break;
            case UPLOAD_ERR_NO_FILE:
                $error_message .= "Không có file nào được tải lên.";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $error_message .= "Thiếu thư mục tạm.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $error_message .= "Không thể ghi file vào ổ đĩa.";
                break;
            case UPLOAD_ERR_EXTENSION:
                $error_message .= "Upload bị dừng bởi extension.";
                break;
            default:
                $error_message .= "Lỗi không xác định.";
                break;
        }
        return [
            'success' => false,
            'message' => $error_message
        ];
    }
    
    // Kiểm tra nếu file là ảnh thực
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return [
            'success' => false,
            'message' => 'File không phải là ảnh.'
        ];
    }
    
    // Kiểm tra kích thước file (giới hạn 5MB)
    if ($file["size"] > 5000000) {
        return [
            'success' => false,
            'message' => 'File quá lớn, vui lòng chọn file nhỏ hơn 5MB.'
        ];
    }
    
    // Cho phép một số định dạng file
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
        return [
            'success' => false,
            'message' => 'Chỉ chấp nhận file JPG, JPEG, PNG & GIF.'
        ];
    }
    
    // Tạo tên file mới
    $new_filename = uniqid() . '.' . $imageFileType;
    
    // Xác định đường dẫn thư mục upload
    $upload_dir = "uploads/product-images/";
    
    // Xác định nếu đang ở trong thư mục admin
    $script_path = $_SERVER['SCRIPT_NAME'];
    $is_admin = (strpos($script_path, '/admin/') !== false);
    
    // Điều chỉnh đường dẫn nếu đang ở admin
    $file_system_path = $is_admin ? "../" . $upload_dir : $upload_dir;
    
    // Đảm bảo thư mục tồn tại
    if (!file_exists($file_system_path)) {
        if (!mkdir($file_system_path, 0777, true)) {
            return [
                'success' => false,
                'message' => "Không thể tạo thư mục upload: $file_system_path"
            ];
        }
        chmod($file_system_path, 0777);
    }
    
    $target_file = $file_system_path . $new_filename;
    
    // Upload file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        // Thiết lập quyền cho file
        chmod($target_file, 0644);
        
        return [
            'success' => true,
            'filename' => $new_filename
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Có lỗi xảy ra khi upload file: ' . error_get_last()['message']
        ];
    }
}

// Hàm định dạng tiền tệ
function format_currency($amount) {
    return number_format($amount, 0, ',', '.') . ' ₫';
}

// Hàm lấy trạng thái đơn hàng
function get_order_status($status) {
    switch ($status) {
        case 'pending':
            return '<span class="badge bg-warning">Chờ xử lý</span>';
        case 'processing':
            return '<span class="badge bg-primary">Đang xử lý</span>';
        case 'shipped':
            return '<span class="badge bg-info">Đang giao hàng</span>';
        case 'delivered':
            return '<span class="badge bg-success">Đã giao hàng</span>';
        case 'cancelled':
            return '<span class="badge bg-danger">Đã hủy</span>';
        default:
            return '<span class="badge bg-secondary">Không xác định</span>';
    }
}

// Hàm lấy danh sách danh mục
function get_categories() {
    global $conn;
    $query = "SELECT * FROM categories ORDER BY name";
    $result = mysqli_query($conn, $query);
    $categories = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $categories[] = $row;
        }
    }
    
    return $categories;
}

// Hàm lấy thông tin danh mục theo ID
function get_category($id) {
    global $conn;
    $query = "SELECT * FROM categories WHERE id = $id";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

// Hàm lấy danh sách sản phẩm
function get_products($limit = null, $category_id = null) {
    global $conn;
    $query = "SELECT p.*, c.name as category_name FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id";
    
    if ($category_id) {
        $query .= " WHERE p.category_id = $category_id";
    }
    
    $query .= " ORDER BY p.id DESC";
    
    if ($limit) {
        $query .= " LIMIT $limit";
    }
    
    $result = mysqli_query($conn, $query);
    $products = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
    }
    
    return $products;
}

// Hàm lấy thông tin sản phẩm theo ID
function get_product($id) {
    global $conn;
    $query = "SELECT p.*, c.name as category_name FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.id = $id";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

// Hàm lấy đánh giá của sản phẩm
function get_product_reviews($product_id) {
    global $conn;
    $query = "SELECT r.*, u.name as user_name FROM reviews r 
              LEFT JOIN users u ON r.user_id = u.id 
              WHERE r.product_id = $product_id 
              ORDER BY r.created_at DESC";
    $result = mysqli_query($conn, $query);
    $reviews = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $reviews[] = $row;
        }
    }
    
    return $reviews;
}

// Hàm lấy đánh giá trung bình của sản phẩm
function get_product_rating($product_id) {
    global $conn;
    $query = "SELECT AVG(rating) as avg_rating FROM reviews WHERE product_id = $product_id";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return round($row['avg_rating'], 1);
    }
    
    return 0;
}

// Hàm lấy danh sách đơn hàng
function get_orders($user_id = null) {
    global $conn;
    $query = "SELECT o.*, u.name as user_name, u.email as user_email FROM orders o 
              LEFT JOIN users u ON o.user_id = u.id";
    
    if ($user_id) {
        $query .= " WHERE o.user_id = $user_id";
    }
    
    $query .= " ORDER BY o.created_at DESC";
    
    $result = mysqli_query($conn, $query);
    $orders = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $orders[] = $row;
        }
    }
    
    return $orders;
}

// Hàm lấy thông tin đơn hàng theo ID
function get_order($id) {
    global $conn;
    $query = "SELECT o.*, u.name as user_name, u.email as user_email, u.phone as user_phone, u.address as user_address 
              FROM orders o 
              LEFT JOIN users u ON o.user_id = u.id 
              WHERE o.id = $id";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $order = mysqli_fetch_assoc($result);
        
        // Lấy các sản phẩm trong đơn hàng
        $query = "SELECT oi.*, p.name as product_name, p.image as product_image 
                  FROM order_items oi 
                  LEFT JOIN products p ON oi.product_id = p.id 
                  WHERE oi.order_id = $id";
        $result = mysqli_query($conn, $query);
        $order_items = [];
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $order_items[] = $row;
            }
        }
        
        $order['items'] = $order_items;
        return $order;
    }
    
    return null;
}

// Hàm thêm sản phẩm vào giỏ hàng
function add_to_cart($product_id, $quantity = 1) {
    start_session_if_not_started();
    $product = get_product($product_id);
    if (!$product) return;
    $stock = $product['stock'];
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    // Nếu đã có trong giỏ thì chỉ tăng lên tối đa bằng tồn kho
    if (isset($_SESSION['cart'][$product_id])) {
        $current = $_SESSION['cart'][$product_id];
        $new_quantity = min($current + $quantity, $stock);
        $_SESSION['cart'][$product_id] = $new_quantity;
    } else {
        $_SESSION['cart'][$product_id] = min($quantity, $stock);
    }
}

// Hàm cập nhật số lượng sản phẩm trong giỏ hàng
function update_cart($product_id, $quantity) {
    start_session_if_not_started();
    $product = get_product($product_id);
    if (!$product) return;
    $stock = $product['stock'];
    if (isset($_SESSION['cart'][$product_id])) {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            $_SESSION['cart'][$product_id] = min($quantity, $stock);
        }
    }
}

// Hàm xóa sản phẩm khỏi giỏ hàng
function remove_from_cart($product_id) {
    start_session_if_not_started();
    
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
}

// Hàm lấy tổng số sản phẩm trong giỏ hàng
function get_cart_count() {
    start_session_if_not_started();
    
    if (!isset($_SESSION['cart'])) {
        return 0;
    }
    
    $count = 0;
    foreach ($_SESSION['cart'] as $quantity) {
        $count += $quantity;
    }
    
    return $count;
}

// Hàm lấy tổng giá trị giỏ hàng
function get_cart_total() {
    global $conn;
    start_session_if_not_started();
    
    if (!isset($_SESSION['cart'])) {
        return 0;
    }
    
    $total = 0;
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $product = get_product($product_id);
        if ($product) {
            $total += $product['price'] * $quantity;
        }
    }
    
    return $total;
}

// Hàm lấy nội dung giỏ hàng
function get_cart_items() {
    global $conn;
    start_session_if_not_started();
    
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return [];
    }
    
    $items = [];
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $product = get_product($product_id);
        if ($product) {
            $product['quantity'] = $quantity;
            $product['subtotal'] = $product['price'] * $quantity;
            $items[] = $product;
        }
    }
    
    return $items;
}

// Hàm xóa giỏ hàng
function clear_cart() {
    start_session_if_not_started();
    $_SESSION['cart'] = [];
}

// Hàm lấy thống kê cho dashboard
function get_dashboard_stats() {
    global $conn;
    
    // Tổng số đơn hàng
    $query = "SELECT COUNT(*) as total_orders FROM orders";
    $result = mysqli_query($conn, $query);
    $total_orders = 0;
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $total_orders = $row['total_orders'];
    }
    
    // Tổng doanh thu
    $query = "SELECT SUM(total_price) as total_revenue FROM orders WHERE status != 'cancelled'";
    $result = mysqli_query($conn, $query);
    $total_revenue = 0;
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $total_revenue = $row['total_revenue'] ? $row['total_revenue'] : 0;
    }
    
    // Tổng số sản phẩm
    $query = "SELECT COUNT(*) as total_products FROM products";
    $result = mysqli_query($conn, $query);
    $total_products = 0;
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $total_products = $row['total_products'];
    }
    
    // Tổng số người dùng
    $query = "SELECT COUNT(*) as total_users FROM users WHERE role = 'customer'";
    $result = mysqli_query($conn, $query);
    $total_users = 0;
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $total_users = $row['total_users'];
    }
    
    // Đơn hàng hôm nay
    $today = date('Y-m-d');
    $query = "SELECT COUNT(*) as today_orders FROM orders WHERE DATE(created_at) = '$today'";
    $result = mysqli_query($conn, $query);
    $today_orders = 0;
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $today_orders = $row['today_orders'];
    }
    
    // Doanh thu hôm nay
    $query = "SELECT SUM(total_price) as today_revenue FROM orders WHERE DATE(created_at) = '$today' AND status != 'cancelled'";
    $result = mysqli_query($conn, $query);
    $today_revenue = 0;
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $today_revenue = $row['today_revenue'] ? $row['today_revenue'] : 0;
    }
    
    return [
        'total_orders' => $total_orders,
        'total_revenue' => $total_revenue,
        'total_products' => $total_products,
        'total_users' => $total_users,
        'today_orders' => $today_orders,
        'today_revenue' => $today_revenue
    ];
}

// Hàm lấy dữ liệu cho biểu đồ doanh thu theo tháng
function get_monthly_revenue_data() {
    global $conn;
    
    $data = [];
    $current_year = date('Y');
    
    for ($month = 1; $month <= 12; $month++) {
        $query = "SELECT SUM(total_price) as revenue FROM orders 
                  WHERE YEAR(created_at) = $current_year 
                  AND MONTH(created_at) = $month 
                  AND status != 'cancelled'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $data[] = $row['revenue'] ? (float)$row['revenue'] : 0;
        } else {
            $data[] = 0;
        }
    }
    
    return $data;
}

// Hàm lấy dữ liệu cho biểu đồ sản phẩm bán chạy
function get_top_selling_products($limit = 5) {
    global $conn;
    
    $query = "SELECT p.id, p.name, SUM(oi.quantity) as total_sold 
              FROM products p 
              JOIN order_items oi ON p.id = oi.product_id 
              JOIN orders o ON oi.order_id = o.id 
              WHERE o.status != 'cancelled' 
              GROUP BY p.id 
              ORDER BY total_sold DESC 
              LIMIT $limit";
    $result = mysqli_query($conn, $query);
    $products = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
    }
    
    return $products;
}

// Hàm lấy dữ liệu cho biểu đồ trạng thái đơn hàng
function get_order_status_data() {
    global $conn;
    
    $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    $data = [];
    
    foreach ($statuses as $status) {
        $query = "SELECT COUNT(*) as count FROM orders WHERE status = '$status'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $data[$status] = (int)$row['count'];
        } else {
            $data[$status] = 0;
        }
    }
    
    return $data;
}

// Hàm lấy đường dẫn ảnh sản phẩm
function get_product_image_url($image_filename) {
    if (empty($image_filename)) {
        return "assets/img/no-image.jpg";
    }
    
    // Xác định nếu đang ở trong thư mục admin
    $script_path = $_SERVER['SCRIPT_NAME'];
    $is_admin = (strpos($script_path, '/admin/') !== false);
    
    // Trả về đường dẫn tương đối phù hợp
    return $is_admin ? "../uploads/product-images/" . $image_filename : "uploads/product-images/" . $image_filename;
}

// Hàm gửi mail qua Mailgun
function send_mailgun_email($to, $subject, $text, $html = null, $product = null) {
    $api_key = '___API_MAIL_GUN__'; // Thay bằng API key thực tế
    $domain = 'mail.dinhmanhhung.net'; // Thay bằng domain đã cấu hình mailgun
    $from = 'Cảnh báo tồn kho <noreply@' . $domain . '>';
    
    $url = "https://api.mailgun.net/v3/$domain/messages";
    $postData = [
        'from' => $from,
        'to' => $to,
        'subject' => $subject,
        'text' => $text
    ];
    // Nếu có thông tin sản phẩm, tạo HTML đẹp
    if ($product) {
        $product_link = (isset($product['id'])) ? 'http://localhost:8504/product.php?id=' . $product['id'] : '#';
        $img_url = (isset($product['image'])) ? 'http://localhost:8504/uploads/product-images/' . $product['image'] : 'http://localhost:8504/assets/img/no-image.jpg';
        $html = '<div style="font-family:Arial,sans-serif;max-width:480px;margin:auto;background:#f8fbff;border-radius:12px;padding:24px;box-shadow:0 2px 12px 0 rgba(0,0,0,0.07);">
            <h2 style="color:#d7263d;text-align:center;margin-bottom:16px;">⚠️ Sản phẩm sắp hết hàng!</h2>
            <div style="display:flex;align-items:center;gap:16px;background:#fff;border-radius:8px;padding:16px;box-shadow:0 1px 6px 0 rgba(0,0,0,0.04);">
                <img src="'.$img_url.'" alt="'.$product['name'].'" style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid #eee;">
                <div>
                    <a href="'.$product_link.'" style="font-size:18px;font-weight:bold;color:#1a237e;text-decoration:none;">'.$product['name'].'</a><br>
                    <span style="color:#333;font-size:15px;">Còn lại: <b style="color:#d7263d;font-size:18px;">'.$product['stock'].'</b> sản phẩm</span>
                </div>
            </div>
            <div style="margin-top:24px;text-align:center;">
                <a href="'.$product_link.'" style="display:inline-block;padding:10px 24px;background:#1976d2;color:#fff;border-radius:6px;text-decoration:none;font-weight:bold;">Xem chi tiết sản phẩm</a>
            </div>
            <div style="margin-top:32px;font-size:13px;color:#888;text-align:center;">Vui lòng nhập thêm hàng để tránh hết kho!</div>
        </div>';
        $postData['html'] = $html;
    } elseif ($html) {
        $postData['html'] = $html;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, 'api:' . $api_key);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    $result = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $http_status === 200;
}
?>
