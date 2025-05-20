<?php
require_once "includes/db.php";
require_once "includes/functions.php";

// Kiểm tra id danh mục
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$category_id = $_GET['id'];
$category = get_category($category_id);

if (!$category) {
    header("Location: index.php");
    exit();
}

$page_title = $category['name'];
require_once "includes/header.php";

// Lấy danh sách sản phẩm theo danh mục
$products = get_products(null, $category_id);
?>

<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo $category['name']; ?></li>
        </ol>
    </nav>
    
    <h1 class="mb-4"><?php echo $category['name']; ?></h1>
    
    <div class="row">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
                <div class="col-md-3 mb-4">
                    <div class="card product-card h-100">
                        <a href="product.php?id=<?php echo $product['id']; ?>">
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?php echo get_product_image_url($product['image']); ?>" class="card-img-top" alt="<?php echo $product['name']; ?>">
                            <?php else: ?>
                                <img src="assets/img/no-image.jpg" class="card-img-top" alt="No Image">
                            <?php endif; ?>
                        </a>
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="text-decoration-none text-dark">
                                    <?php echo $product['name']; ?>
                                </a>
                            </h5>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="product-price"><?php echo format_currency($product['price']); ?></span>
                                <div class="product-rating">
                                    <?php
                                    $rating = get_product_rating($product['id']);
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
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0">
                            <div class="d-grid">
                                <a href="cart.php?add=<?php echo $product['id']; ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">
                    Không có sản phẩm nào trong danh mục này.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once "includes/footer.php"; ?>
