<?php
$page_title = "Nhật ký hệ thống";
require_once "includes/header.php";

// Xử lý xóa nhật ký
if (isset($_POST['clear_logs'])) {
    $query = "TRUNCATE TABLE system_logs";
    if (mysqli_query($conn, $query)) {
        $success_message = "Đã xóa toàn bộ nhật ký hệ thống.";
    } else {
        $error_message = "Có lỗi xảy ra khi xóa nhật ký: " . mysqli_error($conn);
    }
}

// Lọc nhật ký
$filter_type = isset($_GET['type']) ? $_GET['type'] : '';
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';
$filter_user = isset($_GET['user']) ? $_GET['user'] : '';

// Xây dựng câu truy vấn
$query = "SELECT l.*, u.name as user_name FROM system_logs l 
          LEFT JOIN users u ON l.user_id = u.id";

$where_clauses = [];

if (!empty($filter_type)) {
    $where_clauses[] = "l.log_type = '$filter_type'";
}

if (!empty($filter_date)) {
    $where_clauses[] = "DATE(l.created_at) = '$filter_date'";
}

if (!empty($filter  {
    $where_clauses[] = "DATE(l.created_at) = '$filter_date'";
}

if (!empty($filter_user)) {
    $where_clauses[] = "l.user_id = '$filter_user'";
}

if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
}

$query .= " ORDER BY l.created_at DESC";

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$count_query = str_replace("l.*, u.name as user_name", "COUNT(*) as total", $query);
$count_result = mysqli_query($conn, $count_query);
$total_rows = 0;

if ($count_result && mysqli_num_rows($count_result) > 0) {
    $row = mysqli_fetch_assoc($count_result);
    $total_rows = $row['total'];
}

$total_pages = ceil($total_rows / $limit);

$query .= " LIMIT $offset, $limit";
$result = mysqli_query($conn, $query);

// Lấy danh sách loại nhật ký
$log_types_query = "SELECT DISTINCT log_type FROM system_logs";
$log_types_result = mysqli_query($conn, $log_types_query);
$log_types = [];

if ($log_types_result) {
    while ($row = mysqli_fetch_assoc($log_types_result)) {
        $log_types[] = $row['log_type'];
    }
}

// Lấy danh sách người dùng
$users_query = "SELECT DISTINCT u.id, u.name FROM system_logs l 
                JOIN users u ON l.user_id = u.id";
$users_result = mysqli_query($conn, $users_query);
$users = [];

if ($users_result) {
    while ($row = mysqli_fetch_assoc($users_result)) {
        $users[$row['id']] = $row['name'];
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-3">
    <h1 class="h2">Nhật ký hệ thống</h1>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="return confirm('Bạn có chắc chắn muốn xóa toàn bộ nhật ký?');">
        <button type="submit" name="clear_logs" class="btn btn-danger">
            <i class="bi bi-trash"></i> Xóa nhật ký
        </button>
    </form>
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

<!-- Bộ lọc -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Bộ lọc</h6>
    </div>
    <div class="card-body">
        <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="row g-3">
            <div class="col-md-3">
                <label for="type" class="form-label">Loại nhật ký</label>
                <select class="form-select" id="type" name="type">
                    <option value="">Tất cả</option>
                    <?php foreach ($log_types as $type): ?>
                        <option value="<?php echo $type; ?>" <?php echo ($filter_type == $type) ? 'selected' : ''; ?>>
                            <?php echo ucfirst($type); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="date" class="form-label">Ngày</label>
                <input type="date" class="form-control" id="date" name="date" value="<?php echo $filter_date; ?>">
            </div>
            
            <div class="col-md-3">
                <label for="user" class="form-label">Người dùng</label>
                <select class="form-select" id="user" name="user">
                    <option value="">Tất cả</option>
                    <?php foreach ($users as $id => $name): ?>
                        <option value="<?php echo $id; ?>" <?php echo ($filter_user == $id) ? 'selected' : ''; ?>>
                            <?php echo $name; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-filter"></i> Lọc
                </button>
                <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Xóa bộ lọc
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Danh sách nhật ký -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách nhật ký</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Loại</th>
                        <th>Nội dung</th>
                        <th>Người dùng</th>
                        <th>IP</th>
                        <th>Thời gian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($log = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $log['id']; ?></td>
                                <td>
                                    <?php
                                    $badge_class = 'bg-secondary';
                                    switch ($log['log_type']) {
                                        case 'login':
                                            $badge_class = 'bg-success';
                                            break;
                                        case 'logout':
                                            $badge_class = 'bg-info';
                                            break;
                                        case 'error':
                                            $badge_class = 'bg-danger';
                                            break;
                                        case 'warning':
                                            $badge_class = 'bg-warning';
                                            break;
                                        case 'info':
                                            $badge_class = 'bg-primary';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($log['log_type']); ?></span>
                                </td>
                                <td><?php echo $log['message']; ?></td>
                                <td><?php echo !empty($log['user_name']) ? $log['user_name'] : 'N/A'; ?></td>
                                <td><?php echo $log['ip_address']; ?></td>
                                <td><?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Không có nhật ký nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Phân trang -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?page=<?php echo $page - 1; ?>&type=<?php echo $filter_type; ?>&date=<?php echo $filter_date; ?>&user=<?php echo $filter_user; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?page=<?php echo $i; ?>&type=<?php echo $filter_type; ?>&date=<?php echo $filter_date; ?>&user=<?php echo $filter_user; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?page=<?php echo $page + 1; ?>&type=<?php echo $filter_type; ?>&date=<?php echo $filter_date; ?>&user=<?php echo $filter_user; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php require_once "includes/footer.php"; ?>
