<?php
$page_title = "Quản lý đánh giá";
require_once "includes/header.php";

// Xử lý cập nhật trạng thái đánh giá
if (isset($_GET['approve']) && !empty($_GET['approve'])) {
    $review_id = $_GET['approve'];
    
    $query = "UPDATE reviews SET status = 'approved' WHERE id = $review_id";
    if (mysqli_query($conn, $query)) {
        $success_message = "Đánh giá đã được phê duyệt.";
    } else {
        $error_message = "Có lỗi xảy ra khi phê duyệt đánh giá: " . mysqli_error($conn);
    }
}

if (isset($_GET['reject']) && !empty($_GET['reject'])) {
    $review_id = $_GET['reject'];
    
    $query = "UPDATE reviews SET status = 'rejected' WHERE id = $review_id";
    if (mysqli_query($conn, $query)) {
        $success_message = "Đánh giá đã bị từ chối.";
    } else {
        $error_message = "Có lỗi xảy ra khi từ chối đánh giá: " . mysqli_error($conn);
    }
}

if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $review_id = $_GET['delete'];
    
    $query = "DELETE FROM reviews WHERE id = $review_id";
    if (mysqli_query($conn, $query)) {
        $success_message = "Đánh giá đã được xóa.";
    } else {
        $error_message = "Có lỗi xảy ra khi xóa đánh giá: " . mysqli_error($conn);
    }
}

// Lấy danh sách đánh giá
$query = "SELECT r.*, p.name as product_name, u.name as user_name 
          FROM reviews r 
          LEFT JOIN products p ON r.product_id = p.id 
          LEFT JOIN users u ON r.user_id = u.id 
          ORDER BY r.created_at DESC";
$result = mysqli_query($conn, $query);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-3">
    <h1 class="h2">Quản lý đánh giá</h1>
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
                        <th>ID</th>
                        <th>Sản phẩm</th>
                        <th>Người dùng</th>
                        <th>Đánh giá</th>
                        <th>Nội dung</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($review = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $review['id']; ?></td>
                                <td><?php echo $review['product_name']; ?></td>
                                <td><?php echo $review['user_name']; ?></td>
                                <td>
                                    <?php
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $review['rating']) {
                                            echo '<i class="bi bi-star-fill text-warning"></i>';
                                        } else {
                                            echo '<i class="bi bi-star text-muted"></i>';
                                        }
                                    }
                                    ?>
                                </td>
                                <td><?php echo $review['comment']; ?></td>
                                <td>
                                    <?php
                                    switch ($review['status']) {
                                        case 'approved':
                                            echo '<span class="badge bg-success">Đã duyệt</span>';
                                            break;
                                        case 'pending':
                                            echo '<span class="badge bg-warning">Chờ duyệt</span>';
                                            break;
                                        case 'rejected':
                                            echo '<span class="badge bg-danger">Đã từ chối</span>';
                                            break;
                                    }
                                    ?>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($review['created_at'])); ?></td>
                                <td>
                                    <?php if ($review['status'] != 'approved'): ?>
                                        <a href="reviews.php?approve=<?php echo $review['id']; ?>" class="btn btn-sm btn-success">
                                            <i class="bi bi-check-circle"></i> Duyệt
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($review['status'] != 'rejected'): ?>
                                        <a href="reviews.php?reject=<?php echo $review['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="bi bi-x-circle"></i> Từ chối
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="reviews.php?delete=<?php echo $review['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa đánh giá này?')">
                                        <i class="bi bi-trash"></i> Xóa
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">Không có đánh giá nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once "includes/footer.php"; ?>
