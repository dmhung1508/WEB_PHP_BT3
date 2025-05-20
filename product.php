<?php
require_once "includes/db.php";
require_once "includes/functions.php";

// Kiểm tra id sản phẩm
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$product_id = $_GET['id'];
$product = get_product($product_id);

if (!$product) {
    header("Location: index.php");
    exit();
}

// Xử lý thêm vào giỏ hàng
if (isset($_POST['add_to_cart'])) {
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    add_to_cart($product_id, $quantity);
    header("Location: cart.php");
    exit();
}

// Xử lý gửi đánh giá
if (isset($_POST['submit_review'])) {
    start_session_if_not_started();
    
    if (!isset($_SESSION['user_id'])) {
        $review_error = "Vui lòng đăng nhập để gửi đánh giá.";
    } else {
        $user_id = $_SESSION['user_id'];
        $rating = $_POST['rating'];
        $comment = trim($_POST['comment']);
        
        if (empty($rating)) {
            $review_error = "Vui lòng chọn số sao đánh giá.";
        } elseif (empty($comment)) {
            $review_error = "Vui lòng nhập nội dung đánh giá.";
        } else {
            // Kiểm tra xem người dùng đã đánh giá sản phẩm này chưa
            $check_query = "SELECT id FROM reviews WHERE product_id = $product_id AND user_id = $user_id";
            $check_result = mysqli_query($conn, $check_query);
            
            if (mysqli_num_rows($check_result) > 0) {
                // Cập nhật đánh giá
                $review = mysqli_fetch_assoc($check_result);
                $review_id = $review['id'];
                
                $query = "UPDATE reviews SET rating = ?, comment = ?, status = 'pending', created_at = NOW() WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "isi", $rating, $comment, $review_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $review_success = "Cảm ơn bạn đã cập nhật đánh giá. Đánh giá của bạn sẽ được hiển thị sau khi được phê duyệt.";
                } else {
                    $review_error = "Có lỗi xảy ra khi cập nhật đánh giá.";
                }
                
                mysqli_stmt_close($stmt);
            } else {
                // Thêm đánh giá mới
                $query = "INSERT INTO reviews (product_id, user_id, rating, comment, status) VALUES (?, ?, ?, ?, 'pending')";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "iiis", $product_id, $user_id, $rating, $comment);
                
                if (mysqli_stmt_execute($stmt)) {
                    $review_success = "Cảm ơn bạn đã gửi đánh giá. Đánh giá của bạn sẽ được hiển thị sau khi được phê duyệt.";
                } else {
                    $review_error = "Có lỗi xảy ra khi gửi đánh giá.";
                }
                
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Lấy đánh giá của sản phẩm
$reviews = get_product_reviews($product_id);
$approved_reviews = array_filter($reviews, function($review) {
    return $review['status'] == 'approved';
});

$page_title = $product['name'];
require_once "includes/header.php";
?>

<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="category.php?id=<?php echo $product['category_id']; ?>"><?php echo $product['category_name']; ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo $product['name']; ?></li>
        </ol>
    </nav>
    
    <div class="row">
        <div class="col-md-5 mb-4">
            <div class="card shadow">
                <div class="card-body text-center">
                    <?php if (!empty($product['image'])): ?>
                        <img src="<?php echo get_product_image_url($product['image']); ?>" alt="<?php echo $product['name']; ?>" class="img-fluid product-detail-img">
                    <?php else: ?>
                        <img src="assets/img/no-image.jpg" alt="No Image" class="img-fluid product-detail-img">
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-7">
            <div class="card shadow">
                <div class="card-body">
                    <h1 class="mb-3"><?php echo $product['name']; ?></h1>
                    
                    <div class="mb-3">
                        <div class="product-rating">
                            <?php
                            $rating = get_product_rating($product_id);
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $rating) {
                                    echo '<i class="bi bi-star-fill"></i>';
                                } elseif ($i - 0.5 <= $rating) {
                                    echo '<i class="bi bi-star-half"></i>';
                                } else {
                                    echo '<i class="bi bi-star"></i>';
                                }
                            }
                            ?>
                            <span class="ms-2"><?php echo $rating; ?>/5 (<?php echo count($approved_reviews); ?> đánh giá)</span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h3 class="product-price text-danger"><?php echo format_currency($product['price']); ?></h3>
                    </div>
                    
                    <div class="mb-3">
                        <p><strong>Danh mục:</strong> <a href="category.php?id=<?php echo $product['category_id']; ?>"><?php echo $product['category_name']; ?></a></p>
                        <p><strong>Tình trạng:</strong> <?php echo $product['stock'] > 0 ? '<span class="text-success">Còn hàng</span>' : '<span class="text-danger">Hết hàng</span>'; ?></p>
                        <div class="text-info small">Còn lại: <span id="stock-detail"><?php echo $product['stock']; ?></span> sản phẩm</div>
                    </div>
                    
                    <?php if (!empty($product['description'])): ?>
                        <div class="mb-4">
                            <h5>Mô tả sản phẩm</h5>
                            <p><?php echo nl2br($product['description']); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($product['stock'] > 0): ?>
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $product_id; ?>">
                            <div class="row align-items-center mb-4">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <button class="btn btn-outline-secondary quantity-minus" type="button">-</button>
                                        <input type="number" class="form-control text-center quantity-input" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                                        <button class="btn btn-outline-secondary quantity-plus" type="button">+</button>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <button type="submit" name="add_to_cart" class="btn btn-primary">
                                        <i class="bi bi-cart-plus"></i> Thêm vào giỏ hàng
                                    </button>
                                    <a href="cart.php?add=<?php echo $product_id; ?>&quantity=1" class="btn btn-success">
                                        <i class="bi bi-lightning-fill"></i> Mua ngay
                                    </a>
                                </div>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            Sản phẩm hiện đang hết hàng.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-5">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Đánh giá sản phẩm</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($review_success)): ?>
                        <div class="alert alert-success"><?php echo $review_success; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($review_error)): ?>
                        <div class="alert alert-danger"><?php echo $review_error; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $product_id; ?>" class="mb-4">
                            <div class="mb-3">
                                <label for="rating" class="form-label">Đánh giá của bạn</label>
                                <div>
                                    <div class="rating">
                                        <input type="radio" id="star5" name="rating" value="5" /><label for="star5"></label>
                                        <input type="radio" id="star4" name="rating" value="4" /><label for="star4"></label>
                                        <input type="radio" id="star3" name="rating" value="3" /><label for="star3"></label>
                                        <input type="radio" id="star2" name="rating" value="2" /><label for="star2"></label>
                                        <input type="radio" id="star1" name="rating" value="1" /><label for="star1"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="comment" class="form-label">Nhận xét của bạn</label>
                                <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
                            </div>
                            <button type="submit" name="submit_review" class="btn btn-primary">Gửi đánh giá</button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info">
                            Vui lòng <a href="login.php">đăng nhập</a> để gửi đánh giá.
                        </div>
                    <?php endif; ?>
                    
                    <h5 class="mb-3">Đánh giá từ khách hàng</h5>
                    
                    <?php if (empty($approved_reviews)): ?>
                        <div class="alert alert-info">
                            Chưa có đánh giá nào cho sản phẩm này.
                        </div>
                    <?php else: ?>
                        <?php foreach ($approved_reviews as $review): ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <div>
                                            <h6 class="mb-0"><?php echo $review['user_name']; ?></h6>
                                            <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($review['created_at'])); ?></small>
                                        </div>
                                        <div class="product-rating">
                                            <?php
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $review['rating']) {
                                                    echo '<i class="bi bi-star-fill"></i>';
                                                } else {
                                                    echo '<i class="bi bi-star"></i>';
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <p class="mb-0"><?php echo nl2br($review['comment']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
}
.rating:not(:checked) > input {
    position: absolute;
    clip: rect(0,0,0,0);
}
.rating:not(:checked) > label {
    float: right;
    width: 1em;
    padding: 0 .1em;
    overflow: hidden;
    white-space: nowrap;
    cursor: pointer;
    font-size: 2em;
    line-height: 1.2;
    color: #ddd;
}
.rating:not(:checked) > label:before {
    content: '★ ';
}
.rating > input:checked ~ label {
    color: #ffc700;
}
.rating:not(:checked) > label:hover,
.rating:not(:checked) > label:hover ~ label {
    color: #ffc700;
}
.rating > input:checked + label:hover,
.rating > input  > label:hover ~ label {
    color: #ffc700;
}
.rating > input:checked + label:hover,
.rating > input:checked + label:hover ~ label,
.rating > input:checked ~ label:hover,
.rating > input:checked ~ label:hover ~ label,
.rating > label:hover ~ input:checked ~ label {
    color: #ffc700;
}
</style>

<?php require_once "includes/footer.php"; ?>
