<?php
$page_title = "Quản lý sản phẩm";
require_once "includes/header.php";

// Xử lý xóa sản phẩm
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $product_id = $_GET['delete'];
    
    // Lấy thông tin sản phẩm để xóa ảnh
    $query = "SELECT image FROM products WHERE id = $product_id";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);
        
        // Xóa ảnh nếu có
        if (!empty($product['image'])) {
            $image_path = "../uploads/product-images/" . $product['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        // Xóa sản phẩm
        $delete_query = "DELETE FROM products WHERE id = $product_id";
        if (mysqli_query($conn, $delete_query)) {
            $success_message = "Sản phẩm đã được xóa thành công.";
        } else {
            $error_message = "Có lỗi xảy ra khi xóa sản phẩm: " . mysqli_error($conn);
        }
    }
}

// Lấy danh sách sản phẩm
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          ORDER BY p.id DESC";
$result = mysqli_query($conn, $query);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-3">
    <h1 class="h2">Quản lý sản phẩm</h1>
    <a href="product-add.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Thêm sản phẩm mới
    </a>
</div>

<?php if (isset($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $success_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th width="80">Ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Tồn kho</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($product = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($product['image'])): ?>
                                        <img src="<?php echo get_product_image_url($product['image']); ?>" alt="<?php echo $product['name']; ?>" class="img-thumbnail" width="60">
                                    <?php else: ?>
                                        <img src="../assets/img/no-image.jpg" alt="No Image" class="img-thumbnail" width="60">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $product['name']; ?></td>
                                <td><?php echo $product['category_name']; ?></td>
                                <td><?php echo format_currency($product['price']); ?></td>
                                <td><?php echo $product['stock']; ?></td>
                                <td>
                                    <a href="product-edit.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i> Sửa
                                    </a>
                                    <a href="products.php?delete=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')">
                                        <i class="bi bi-trash"></i> Xóa
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Không có sản phẩm nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once "includes/footer.php"; ?>
