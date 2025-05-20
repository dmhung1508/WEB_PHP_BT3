<?php
require_once "includes/db.php";
require_once "includes/functions.php";

start_session_if_not_started();

$cart_count = get_cart_count();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>DIENTHOAIRE</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="bg-white py-3 border-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <a href="index.php" class="text-decoration-none">
                        <h1 class="fs-4 fw-bold text-dark mb-0">DIENTHOAIRE</h1>
                    </a>
                </div>
                <div class="col-md-6">
                    <nav class="d-flex justify-content-center">
                        <ul class="nav">
                            <?php
                            $categories = get_categories();
                            foreach ($categories as $category): ?>
                                <li class="nav-item">
                                    <a class="nav-link text-dark px-3" href="category.php?id=<?php echo $category['id']; ?>">
                                        <?php echo $category['name']; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </nav>
                </div>
                <div class="col-md-3">
                    <div class="d-flex justify-content-end align-items-center">
                        <form class="me-3" action="search.php" method="get">
                            <div class="input-group">
                                <input class="form-control form-control-sm border-end-0 rounded-pill rounded-end" type="search" name="q" placeholder="Tìm kiếm..." aria-label="Search">
                                <button class="btn btn-sm btn-outline-secondary border-start-0 rounded-pill rounded-start" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </form>
                        <a class="text-dark me-3 position-relative" href="cart.php">
                            <i class="bi bi-bag"></i>
                            <?php if ($cart_count > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                    <?php echo $cart_count; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <div class="dropdown">
                                <a class="text-dark dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-person-circle"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><span class="dropdown-item-text">Xin chào, <?php echo $_SESSION['name']; ?></span></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="my-orders.php">Đơn hàng của tôi</a></li>
                                    <li><a class="dropdown-item" href="profile.php">Hồ sơ</a></li>
                                    <?php if ($_SESSION['role'] == 'admin'): ?>
                                        <li><a class="dropdown-item" href="admin/dashboard.php">Quản trị</a></li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="logout.php">Đăng xuất</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <a class="text-dark" href="login.php">
                                <i class="bi bi-person-circle"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Main Content -->
    <main>
