<?php
require_once "includes/db.php";
require_once "includes/functions.php";

// Lấy từ khóa tìm kiếm
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($search_query)) {
    header("Location: index.php");
    exit();
}

// Tìm kiếm sản phẩm
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.name LIKE ? OR p.description LIKE ? 
          ORDER BY p.id DESC";
$stmt = mysqli_prepare($conn, $query);
$search_param = "%$search_query%";
mysqli_stmt_bind_param($stmt, "ss", $search_param, $search_param);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$products = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
}

$page_title = "Kết quả tìm kiếm: " . $search_query;
require_once "includes/header.php";
?>

<div class="container py-5">
    <h1 class="mb-4">Kết quả tìm kiếm: "<?php echo htmlspecialchars($search_query); ?>"</h1>
    
    <?php if (empty($products)): ?>
        <div class="alert alert-info">
            Không tìm thấy sản phẩm nào phù hợp với từ khóa "<?php echo htmlspecialchars($search_query); ?>".
        </div>
        <p>Gợi ý:</p>
        <ul>
            <li>Kiểm tra lại chính tả của từ khóa tìm kiếm</li>
            <li>Thử sử dụng từ khóa khác</li>
            <li>Thử sử dụng từ khóa ngắn hơn</li>
        </ul>
    <?php else: ?>
        <p>Tìm thấy <?php echo count($products); ?> sản phẩm phù hợp.</p>
        
        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-md-3 mb-4">
                    <div class="card product-card h-100">
                        <a href="product.php?id=<?php echo $product['id']; ?>">
                            <?php if (!empty($product['image'])): ?>
                                <img src="uploads/product-images/<?php echo $product['image']; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>">
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
                            <p class="card-text text-muted"><?php echo $product['category_name']; ?></p>
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
        </div>
    <?php endif; ?>
</div>

<?php require_once "includes/footer.php"; ?>
