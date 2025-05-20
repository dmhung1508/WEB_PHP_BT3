<?php
$page_title = "Trang chủ";
require_once "includes/db.php";
require_once "includes/functions.php";

// Lấy danh sách sản phẩm nổi bật
$featured_products_query = "SELECT p.*, c.name as category_name FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           ORDER BY p.id DESC LIMIT 8";
$featured_products_result = mysqli_query($conn, $featured_products_query);
$featured_products = [];

if ($featured_products_result) {
    while ($row = mysqli_fetch_assoc($featured_products_result)) {
        $featured_products[] = $row;
    }
}

// Lấy danh sách sản phẩm mới nhất
$new_products_query = "SELECT p.*, c.name as category_name FROM products p 
                      LEFT JOIN categories c ON p.category_id = c.id 
                      ORDER BY p.id DESC LIMIT 4";
$new_products_result = mysqli_query($conn, $new_products_query);
$new_products = [];

if ($new_products_result) {
    while ($row = mysqli_fetch_assoc($new_products_result)) {
        $new_products[] = $row;
    }
}

// Lấy danh sách danh mục
$categories = get_categories();

// Lấy đánh giá từ khách hàng
$testimonials_query = "SELECT r.*, u.name as user_name, p.name as product_name 
                      FROM reviews r 
                      JOIN users u ON r.user_id = u.id 
                      JOIN products p ON r.product_id = p.id 
                      WHERE r.status = 'approved' 
                      ORDER BY r.created_at DESC LIMIT 3";
$testimonials_result = mysqli_query($conn, $testimonials_query);
$testimonials = [];

if ($testimonials_result) {
    while ($row = mysqli_fetch_assoc($testimonials_result)) {
        $testimonials[] = $row;
    }
}

require_once "includes/header.php";
?>

<!-- Hero Section with Carousel -->
<div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
    </div>
    <div class="carousel-inner">
        <div class="carousel-item active bg-light">
            <div class="container py-5">
                <div class="row align-items-center min-vh-50">
                    <div class="col-md-6 text-center text-md-start">
                        <h1 class="display-4 fw-bold mb-4">iPhone 13 Pro Max</h1>
                        <p class="lead mb-4">Trải nghiệm hiệu năng vượt trội với chip A15 Bionic mạnh mẽ và màn hình Super Retina XDR.</p>
                        <a href="product.php?id=1" class="btn btn-primary btn-lg rounded-pill px-4">Mua ngay</a>
                    </div>
                    <div class="col-md-6 text-center">
                        <img src="assets/img/hero-iphone.png" alt="iPhone 13 Pro Max" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
        <div class="carousel-item bg-light">
            <div class="container py-5">
                <div class="row align-items-center min-vh-50">
                    <div class="col-md-6 text-center text-md-start">
                        <h1 class="display-4 fw-bold mb-4">MacBook Pro M1</h1>
                        <p class="lead mb-4">Sức mạnh đột phá với chip M1, thời lượng pin lên đến 20 giờ và màn hình Retina sắc nét.</p>
                        <a href="product.php?id=3" class="btn btn-primary btn-lg rounded-pill px-4">Khám phá ngay</a>
                    </div>
                    <div class="col-md-6 text-center">
                        <img src="assets/img/hero-macbook.png" alt="MacBook Pro M1" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
        <div class="carousel-item bg-light">
            <div class="container py-5">
                <div class="row align-items-center min-vh-50">
                    <div class="col-md-6 text-center text-md-start">
                        <h1 class="display-4 fw-bold mb-4">AirPods Pro</h1>
                        <p class="lead mb-4">Âm thanh đỉnh cao với khả năng chống ồn chủ động và chế độ xuyên âm thông minh.</p>
                        <a href="product.php?id=9" class="btn btn-primary btn-lg rounded-pill px-4">Mua ngay</a>
                    </div>
                    <div class="col-md-6 text-center">
                        <img src="assets/img/hero-airpods.png" alt="AirPods Pro" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>

<!-- Featured Categories -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Danh mục sản phẩm</h2>
            <p class="text-muted">Khám phá các sản phẩm công nghệ hàng đầu</p>
        </div>
        
        <div class="row g-4 justify-content-center">
            <?php foreach ($categories as $category): ?>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="category.php?id=<?php echo $category['id']; ?>" class="text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm text-center">
                            <div class="card-body py-4">
                                <div class="mb-3">
                                    <?php
                                    $icon_class = '';
                                    switch(strtolower($category['name'])) {
                                        case 'điện thoại': $icon_class = 'bi-phone'; break;
                                        case 'laptop': $icon_class = 'bi-laptop'; break;
                                        case 'máy tính bảng': $icon_class = 'bi-tablet'; break;
                                        case 'phụ kiện': $icon_class = 'bi-headphones'; break;
                                        case 'đồng hồ thông minh': $icon_class = 'bi-smartwatch'; break;
                                        default: $icon_class = 'bi-box'; break;
                                    }
                                    ?>
                                    <i class="bi <?php echo $icon_class; ?> fs-1"></i>
                                </div>
                                <h5 class="card-title"><?php echo $category['name']; ?></h5>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Sản phẩm nổi bật</h2>
                <p class="text-muted">Những sản phẩm được yêu thích nhất</p>
            </div>
            <a href="index.php" class="btn btn-outline-primary rounded-pill">Xem tất cả</a>
        </div>
        
        <div class="row g-4">
            <?php foreach ($featured_products as $product): ?>
                <div class="col-6 col-md-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="position-relative">
                            <a href="product.php?id=<?php echo $product['id']; ?>">
                                <?php if (!empty($product['image'])): ?>
                                    <img src="<?php echo get_product_image_url($product['image']); ?>" class="card-img-top p-3" alt="<?php echo $product['name']; ?>">
                                <?php else: ?>
                                    <img src="assets/img/no-image.jpg" class="card-img-top p-3" alt="No Image">
                                <?php endif; ?>
                            </a>
                            <span class="position-absolute top-0 end-0 badge bg-primary m-2 px-2 py-1 rounded-pill">Hot</span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="text-decoration-none text-dark">
                                    <?php echo $product['name']; ?>
                                </a>
                            </h5>
                            <p class="card-text text-muted small"><?php echo $product['category_name']; ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold"><?php echo format_currency($product['price']); ?></span>
                                <div class="text-warning">
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
                        <div class="card-footer bg-white border-top-0 d-flex justify-content-between">
                            <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i> Chi tiết
                            </a>
                            <a href="cart.php?add=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Promo Banner -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6 mb-4 mb-md-0">
                <div class="card bg-primary text-white rounded-4 overflow-hidden position-relative h-100">
                    <div class="card-body p-4 p-md-5">
                        <h3 class="display-6 fw-bold mb-3">iPhone 13 Pro Max</h3>
                        <p class="lead mb-4">Giảm giá lên đến 10% cho đơn hàng đầu tiên</p>
                        <a href="product.php?id=1" class="btn btn-light rounded-pill px-4">Mua ngay</a>
                        <img src="assets/img/promo-iphone.png" alt="iPhone Promo" class="position-absolute end-0 bottom-0" style="max-height: 250px;">
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-success text-white rounded-4 overflow-hidden position-relative h-100">
                    <div class="card-body p-4 p-md-5">
                        <h3 class="display-6 fw-bold mb-3">MacBook Pro</h3>
                        <p class="lead mb-4">Trả góp 0% lãi suất trong 12 tháng</p>
                        <a href="product.php?id=3" class="btn btn-light rounded-pill px-4">Tìm hiểu thêm</a>
                        <img src="assets/img/promo-macbook.png" alt="MacBook Promo" class="position-absolute end-0 bottom-0" style="max-height: 250px;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- New Arrivals -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Sản phẩm mới</h2>
                <p class="text-muted">Khám phá những sản phẩm mới nhất của chúng tôi</p>
            </div>
            <a href="index.php" class="btn btn-outline-primary rounded-pill">Xem tất cả</a>
        </div>
        
        <div class="row g-4">
            <?php foreach ($new_products as $product): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="position-relative">
                            <a href="product.php?id=<?php echo $product['id']; ?>">
                                <?php if (!empty($product['image'])): ?>
                                    <img src="<?php echo get_product_image_url($product['image']); ?>" class="card-img-top p-3" alt="<?php echo $product['name']; ?>">
                                <?php else: ?>
                                    <img src="assets/img/no-image.jpg" class="card-img-top p-3" alt="No Image">
                                <?php endif; ?>
                            </a>
                            <span class="position-absolute top-0 end-0 badge bg-success m-2 px-2 py-1 rounded-pill">New</span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="text-decoration-none text-dark">
                                    <?php echo $product['name']; ?>
                                </a>
                            </h5>
                            <p class="card-text text-muted small"><?php echo $product['category_name']; ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold"><?php echo format_currency($product['price']); ?></span>
                                <div class="text-warning">
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
                        <div class="card-footer bg-white border-top-0 d-flex justify-content-between">
                            <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i> Chi tiết
                            </a>
                            <a href="cart.php?add=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Khách hàng nói gì về chúng tôi</h2>
            <p class="text-muted">Đánh giá từ những khách hàng đã mua sắm tại Tech Store</p>
        </div>
        
        <div class="row g-4">
            <?php if (!empty($testimonials)): ?>
                <?php foreach ($testimonials as $testimonial): ?>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="d-flex mb-3 text-warning">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star-fill me-1"></i>
                                    <?php endfor; ?>
                                </div>
                                <p class="card-text mb-4">"<?php echo substr($testimonial['comment'], 0, 150); ?><?php echo (strlen($testimonial['comment']) > 150) ? '...' : ''; ?>"</p>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <?php echo substr($testimonial['user_name'], 0, 1); ?>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0"><?php echo $testimonial['user_name']; ?></h6>
                                        <p class="text-muted small mb-0">Về sản phẩm: <?php echo $testimonial['product_name']; ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p>Chưa có đánh giá nào.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Features -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-3">
                <div class="text-center">
                    <i class="bi bi-truck display-4 text-primary mb-3"></i>
                    <h5>Giao hàng miễn phí</h5>
                    <p class="text-muted">Cho đơn hàng từ 500.000đ</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <i class="bi bi-shield-check display-4 text-primary mb-3"></i>
                    <h5>Bảo hành chính hãng</h5>
                    <p class="text-muted">12 tháng bảo hành</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <i class="bi bi-arrow-repeat display-4 text-primary mb-3"></i>
                    <h5>Đổi trả dễ dàng</h5>
                    <p class="text-muted">Trong vòng 7 ngày</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <i class="bi bi-headset display-4 text-primary mb-3"></i>
                    <h5>Hỗ trợ 24/7</h5>
                    <p class="text-muted">Hotline: 1900 1234</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Facebook & Map Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6 mb-4 mb-md-0">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-facebook text-primary me-2"></i>Kết nối với chúng tôi</h5>
                    </div>
                    <div class="card-body">
                        <div class="fb-page" 
                             data-href="https://www.facebook.com/facebook" 
                             data-tabs="timeline" 
                             data-width="500" 
                             data-height="400" 
                             data-small-header="false" 
                             data-adapt-container-width="true" 
                             data-hide-cover="false" 
                             data-show-facepile="true">
                            <blockquote cite="https://www.facebook.com/facebook" class="fb-xfbml-parse-ignore">
                                <a href="https://www.facebook.com/facebook">Facebook</a>
                            </blockquote>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-geo-alt text-primary me-2"></i>Vị trí cửa hàng</h5>
                    </div>
                    <div class="card-body">
                        <div id="map" style="height: 400px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <h2 class="mb-3">Đăng ký nhận tin</h2>
                <p class="mb-4">Nhận thông tin về sản phẩm mới và khuyến mãi đặc biệt</p>
                <form class="newsletter-form">
                    <div class="input-group mb-3">
                        <input type="email" class="form-control form-control-lg" placeholder="Email của bạn" aria-label="Email của bạn">
                        <button class="btn btn-light" type="button">Đăng ký</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Facebook SDK -->
<div id="fb-root"></div>
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/vi_VN/sdk.js#xfbml=1&version=v14.0" nonce="random123"></script>

<!-- Google Maps API -->
<script>
function initMap() {
    // Vị trí cửa hàng (ví dụ: Hà Nội)
    const storeLocation = { lat: 21.0285, lng: 105.8542 };
    
    // Tạo bản đồ
    const map = new google.maps.Map(document.getElementById("map"), {
        zoom: 15,
        center: storeLocation,
    });
    
    // Thêm marker
    const marker = new google.maps.Marker({
        position: storeLocation,
        map: map,
        title: "Tech Store",
    });
    
    // Thêm info window
    const infowindow = new google.maps.InfoWindow({
        content: "<strong>Tech Store</strong><br>123 Đường ABC, Quận XYZ, Hà Nội<br>Điện thoại: (84) 123 456 789",
    });
    
    marker.addListener("click", () => {
        infowindow.open(map, marker);
    });
    
    // Mở info window mặc định
    infowindow.open(map, marker);
}
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap"></script>

<?php require_once "includes/footer.php"; ?>
