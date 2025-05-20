<?php
$page_title = "Chỉnh sửa sản phẩm";
require_once "includes/header.php";

// Kiểm tra id sản phẩm
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = $_GET['id'];
$product = get_product($product_id);

if (!$product) {
    header("Location: products.php");
    exit();
}

// Xử lý cập nhật sản phẩm
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $stock = trim($_POST['stock']);
    $category_id = trim($_POST['category_id']);
    $description = trim($_POST['description']);
    $slug = create_slug($name);
    
    // Kiểm tra dữ liệu
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Tên sản phẩm không được để trống.";
    }
    
    if (empty($price) || !is_numeric($price) || $price <= 0) {
        $errors[] = "Giá sản phẩm phải là số dương.";
    }
    
    if (empty($stock) || !is_numeric($stock) || $stock < 0) {
        $errors[] = "Số lượng tồn kho phải là số không âm.";
    }
    
    if (empty($category_id)) {
        $errors[] = "Vui lòng chọn danh mục.";
    }
    
    // Kiểm tra slug đã tồn tại chưa (trừ sản phẩm hiện tại)
    $check_slug_query = "SELECT id FROM products WHERE slug = ? AND id != ?";
    $check_stmt = mysqli_prepare($conn, $check_slug_query);
    mysqli_stmt_bind_param($check_stmt, "si", $slug, $product_id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);
    
    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        $slug = $slug . '-' . time();
    }
    
    mysqli_stmt_close($check_stmt);
    
    // Xử lý upload ảnh
    $image_filename = $product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_result = upload_image($_FILES['image']);
        
        if ($upload_result['success']) {
            // Xóa ảnh cũ nếu có
            if (!empty($product['image'])) {
                $old_image_path = "../uploads/product-images/" . $product['image'];
                if (file_exists($old_image_path)) {
                    @unlink($old_image_path);
                }
            }
            
            $image_filename = $upload_result['filename'];
        } else {
            $errors[] = $upload_result['message'];
        }
    }
    
    // Nếu không có lỗi, cập nhật sản phẩm
    if (empty($errors)) {
        try {
            // Kiểm tra và chuyển đổi kiểu dữ liệu
            $price = (float) $price;
            $stock = (int) $stock;
            $category_id = (int) $category_id;
            $image_filename = (string) $image_filename;
            $name = (string) $name;
            $slug = (string) $slug;
            
            // Escape inputs to prevent SQL injection
            $name = mysqli_real_escape_string($conn, $name);
            $slug = mysqli_real_escape_string($conn, $slug);
            $description = mysqli_real_escape_string($conn, $description);
            $image_filename = mysqli_real_escape_string($conn, $image_filename);
            
            $query = "UPDATE products SET name = '$name', slug = '$slug', description = '$description', 
                      price = $price, stock = $stock, image = '$image_filename', category_id = $category_id 
                      WHERE id = $product_id";
                      
            if (!mysqli_query($conn, $query)) {
                throw new Exception("Lỗi khi thực thi câu lệnh SQL: " . mysqli_error($conn));
            }
            else {
                $success_message = "Sản phẩm đã được cập nhật thành công.";
                // Cập nhật thông tin sản phẩm
                $product = get_product($product_id);
            }
        } catch (Exception $e) {
            $errors[] = "Có lỗi xảy ra khi cập nhật sản phẩm: " . $e->getMessage();
        }
    }
}

// Lấy danh sách danh mục
$categories = get_categories();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-3">
    <h1 class="h2">Chỉnh sửa sản phẩm</h1>
    <a href="products.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Quay lại
    </a>
</div>

<?php if (isset($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $success_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-body">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $product_id; ?>" method="post" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="name" class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $product['name']; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Mô tả sản phẩm</label>
                        <textarea class="form-control" id="description" name="description" rows="5"><?php echo $product['description']; ?></textarea>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="price" class="form-label">Giá (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="price" name="price" value="<?php echo $product['price']; ?>" min="0" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="stock" class="form-label">Số lượng tồn kho <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="stock" name="stock" value="<?php echo $product['stock']; ?>" min="0" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Danh mục <span class="text-danger">*</span></label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo ($product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo $category['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">Ảnh sản phẩm</label>
                        <?php if (!empty($product['image'])): ?>
                            <div class="mb-2">
                                <img src="<?php echo get_product_image_url($product['image']); ?>" alt="<?php echo $product['name']; ?>" class="img-thumbnail" width="150">
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <div class="form-text">Chọn ảnh có kích thước tối đa 5MB. Định dạng: JPG, JPEG, PNG, GIF.</div>
                    </div>
                </div>
            </div>
            
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Cập nhật sản phẩm
                </button>
                <a href="products.php" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>

<?php require_once "includes/footer.php"; ?>
