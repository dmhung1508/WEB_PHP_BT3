<?php
require_once "includes/db.php";
require_once "includes/functions.php";

// Xử lý thêm sản phẩm vào giỏ hàng
if (isset($_GET['add']) && !empty($_GET['add'])) {
    $product_id = $_GET['add'];
    $quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;
    
    add_to_cart($product_id, $quantity);
    
    // Chuyển hướng để tránh việc thêm lại sản phẩm khi refresh trang
    header("Location: cart.php");
    exit();
}

// Xử lý xóa sản phẩm khỏi giỏ hàng
if (isset($_GET['remove']) && !empty($_GET['remove'])) {
    $product_id = $_GET['remove'];
    
    remove_from_cart($product_id);
    
    // Chuyển hướng để tránh việc xóa lại sản phẩm khi refresh trang
    header("Location: cart.php");
    exit();
}

// Xử lý cập nhật số lượng sản phẩm trong giỏ hàng
if (isset($_POST['update_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    update_cart($product_id, $quantity);
    
    // Nếu là request AJAX, trả về JSON
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $product = get_product($product_id);
        $subtotal = $product['price'] * $quantity;
        $total = get_cart_total();
        $grand_total = $total; // Nếu có phí ship hoặc giảm giá thì tính ở đây
        echo json_encode([
            'success' => true,
            'subtotal' => format_currency($subtotal),
            'total' => format_currency($total),
            'grand_total' => format_currency($grand_total)
        ]);
        exit();
    }
    
    // Chuyển hướng để tránh việc cập nhật lại khi refresh trang
    header("Location: cart.php");
    exit();
}

// Xử lý xóa toàn bộ giỏ hàng
if (isset($_GET['clear'])) {
    clear_cart();
    
    // Chuyển hướng để tránh việc xóa lại khi refresh trang
    header("Location: cart.php");
    exit();
}

$cart_items = get_cart_items();
$cart_total = get_cart_total();

$page_title = "Giỏ hàng";
require_once "includes/header.php";
?>

<div class="container py-5">
    <h1 class="mb-4">Giỏ hàng của bạn</h1>
    
    <?php if (empty($cart_items)): ?>
        <div class="alert alert-info">
            Giỏ hàng của bạn đang trống. <a href="index.php">Tiếp tục mua sắm</a>.
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th>Giá</th>
                                        <th>Số lượng</th>
                                        <th>Thành tiền</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart_items as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($item['image'])): ?>
                                                        <img src="uploads/product-images/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="cart-item-img me-3">
                                                    <?php else: ?>
                                                        <img src="assets/img/no-image.jpg" alt="No Image" class="cart-item-img me-3">
                                                    <?php endif; ?>
                                                    <div>
                                                        <h6 class="mb-0"><a href="product.php?id=<?php echo $item['id']; ?>" class="text-decoration-none text-dark"><?php echo $item['name']; ?></a></h6>
                                                        <small class="text-muted"><?php echo $item['category_name']; ?></small>
                                                        <div class="text-info small">Còn lại: <span id="stock-<?php echo $item['id']; ?>"><?php echo $item['stock']; ?></span> sản phẩm</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo format_currency($item['price']); ?></td>
                                            <td>
                                                <div class="input-group" style="width: 120px;">
                                                    <button class="btn btn-outline-secondary quantity-minus" type="button">-</button>
                                                    <input type="number" class="form-control text-center quantity-input" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" data-product-id="<?php echo $item['id']; ?>">
                                                    <button class="btn btn-outline-secondary quantity-plus" type="button">+</button>
                                                    <div class="invalid-feedback d-none" id="stock-warning-<?php echo $item['id']; ?>">Vượt quá số lượng tồn kho!</div>
                                                </div>
                                            </td>
                                            <td id="subtotal-<?php echo $item['id']; ?>"><?php echo format_currency($item['subtotal']); ?></td>
                                            <td>
                                                <a href="cart.php?remove=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-3">
                            <a href="index.php" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left"></i> Tiếp tục mua sắm
                            </a>
                            <a href="cart.php?clear=1" class="btn btn-outline-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa toàn bộ giỏ hàng?')">
                                <i class="bi bi-trash"></i> Xóa giỏ hàng
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Tổng giỏ hàng</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span>Tạm tính:</span>
                            <span id="cart-total"><?php echo format_currency($cart_total); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Phí vận chuyển:</span>
                            <span>Miễn phí</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Tổng cộng:</strong>
                            <strong id="cart-grand-total"><?php echo format_currency($cart_total); ?></strong>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="checkout.php" class="btn btn-primary">
                                <i class="bi bi-credit-card"></i> Thanh toán
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once "includes/footer.php"; ?>
