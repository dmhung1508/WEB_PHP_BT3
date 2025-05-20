<?php
$page_title = "Quản lý danh mục";
require_once "includes/header.php";

// Xử lý thêm danh mục
if (isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    $slug = create_slug($name);
    
    if (empty($name)) {
        $error_message = "Tên danh mục không được để trống.";
    } else {
        // Kiểm tra slug đã tồn tại chưa
        $check_slug_query = "SELECT id FROM categories WHERE slug = '$slug'";
        $check_slug_result = mysqli_query($conn, $check_slug_query);
        
        if (mysqli_num_rows($check_slug_result) > 0) {
            $slug = $slug . '-' . time();
        }
        
        $query = "INSERT INTO categories (name, slug) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $name, $slug);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Danh mục đã được thêm thành công.";
        } else {
            $error_message = "Có lỗi xảy ra khi thêm danh mục: " . mysqli_error($conn);
        }
        
        mysqli_stmt_close($stmt);
    }
}

// Xử lý cập nhật danh mục
if (isset($_POST['update_category'])) {
    $category_id = $_POST['category_id'];
    $name = trim($_POST['name']);
    $slug = create_slug($name);
    
    if (empty($name)) {
        $error_message = "Tên danh mục không được để trống.";
    } else {
        // Kiểm tra slug đã tồn tại chưa (trừ danh mục hiện tại)
        $check_slug_query = "SELECT id FROM categories WHERE slug = '$slug' AND id != $category_id";
        $check_slug_result = mysqli_query($conn, $check_slug_query);
        
        if (mysqli_num_rows($check_slug_result) > 0) {
            $slug = $slug . '-' . time();
        }
        
        $query = "UPDATE categories SET name = ?, slug = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssi", $name, $slug, $category_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Danh mục đã được cập nhật thành công.";
        } else {
            $error_message = "Có lỗi xảy ra khi cập nhật danh mục: " . mysqli_error($conn);
        }
        
        mysqli_stmt_close($stmt);
    }
}

// Xử lý xóa danh mục
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $category_id = $_GET['delete'];
    
    // Kiểm tra xem danh mục có sản phẩm không
    $check_products_query = "SELECT COUNT(*) as product_count FROM products WHERE category_id = $category_id";
    $check_products_result = mysqli_query($conn, $check_products_query);
    $product_count = 0;
    
    if ($check_products_result) {
        $row = mysqli_fetch_assoc($check_products_result);
        $product_count = $row['product_count'];
    }
    
    if ($product_count > 0) {
        $error_message = "Không thể xóa danh mục này vì có $product_count sản phẩm thuộc danh mục.";
    } else {
        $query = "DELETE FROM categories WHERE id = $category_id";
        
        if (mysqli_query($conn, $query)) {
            $success_message = "Danh mục đã được xóa thành công.";
        } else {
            $error_message = "Có lỗi xảy ra khi xóa danh mục: " . mysqli_error($conn);
        }
    }
}

// Lấy danh sách danh mục
$categories = get_categories();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-3">
    <h1 class="h2">Quản lý danh mục</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <i class="bi bi-plus-circle"></i> Thêm danh mục mới
    </button>
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
                        <th width="50">ID</th>
                        <th>Tên danh mục</th>
                        <th>Slug</th>
                        <th width="150">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo $category['id']; ?></td>
                                <td><?php echo $category['name']; ?></td>
                                <td><?php echo $category['slug']; ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary edit-category" 
                                            data-bs-toggle="modal" data-bs-target="#editCategoryModal"
                                            data-id="<?php echo $category['id']; ?>"
                                            data-name="<?php echo $category['name']; ?>">
                                        <i class="bi bi-pencil"></i> Sửa
                                    </button>
                                    <a href="categories.php?delete=<?php echo $category['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này?')">
                                        <i class="bi bi-trash"></i> Xóa
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">Không có danh mục nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal thêm danh mục -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCategoryModalLabel">Thêm danh mục mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" name="add_category" class="btn btn-primary">Thêm danh mục</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal sửa danh mục -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoryModalLabel">Sửa danh mục</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="modal-body">
                    <input type="hidden" id="edit_category_id" name="category_id">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" name="update_category" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Xử lý sự kiện khi nhấn nút sửa danh mục
    const editButtons = document.querySelectorAll('.edit-category');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const categoryId = this.getAttribute('data-id');
            const categoryName = this.getAttribute('data-name');
            
            document.getElementById('edit_category_id').value = categoryId;
            document.getElementById('edit_name').value = categoryName;
        });
    });
});
</script>

<?php require_once "includes/footer.php"; ?>
