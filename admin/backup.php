<?php
$page_title = "Sao lưu & Phục hồi";
require_once "includes/header.php";

// Xử lý sao lưu dữ liệu
if (isset($_POST['create_backup'])) {
    $backup_file = create_backup();
    if ($backup_file) {
        $success_message = "Đã tạo file sao lưu thành công: " . $backup_file;
    } else {
        $error_message = "Có lỗi xảy ra khi tạo file sao lưu.";
    }
}

// Xử lý phục hồi dữ liệu
if (isset($_POST['restore_backup']) && isset($_FILES['backup_file'])) {
    if ($_FILES['backup_file']['error'] == 0) {
        $result = restore_backup($_FILES['backup_file']['tmp_name']);
        if ($result === true) {
            $success_message = "Đã phục hồi dữ liệu thành công.";
        } else {
            $error_message = "Có lỗi xảy ra khi phục hồi dữ liệu: " . $result;
        }
    } else {
        $error_message = "Vui lòng chọn file sao lưu hợp lệ.";
    }
}

// Lấy danh sách file sao lưu
$backup_files = get_backup_files();

// Hàm tạo file sao lưu
function create_backup() {
    global $conn;
    
    $tables = [];
    $result = mysqli_query($conn, "SHOW TABLES");
    
    while ($row = mysqli_fetch_row($result)) {
        $tables[] = $row[0];
    }
    
    $backup_dir = "../backups";
    if (!file_exists($backup_dir)) {
        mkdir($backup_dir, 0777, true);
    }
    
    $backup_file = $backup_dir . "/backup_" . date("Y-m-d_H-i-s") . ".sql";
    $handle = fopen($backup_file, 'w');
    
    // Thêm thông tin phiên bản và thời gian
    $header = "-- Tech Store Database Backup\n";
    $header .= "-- Version: 1.0\n";
    $header .= "-- Date: " . date("Y-m-d H:i:s") . "\n\n";
    fwrite($handle, $header);
    
    foreach ($tables as $table) {
        // Lấy cấu trúc bảng
        $result = mysqli_query($conn, "SHOW CREATE TABLE $table");
        $row = mysqli_fetch_row($result);
        
        fwrite($handle, "-- Table structure for table `$table`\n");
        fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n");
        fwrite($handle, $row[1] . ";\n\n");
        
        // Lấy dữ liệu bảng
        $result = mysqli_query($conn, "SELECT * FROM $table");
        $num_fields = mysqli_num_fields($result);
        
        if (mysqli_num_rows($result) > 0) {
            fwrite($handle, "-- Dumping data for table `$table`\n");
            fwrite($handle, "INSERT INTO `$table` VALUES\n");
            
            $row_count = 0;
            while ($row = mysqli_fetch_row($result)) {
                $row_count++;
                
                fwrite($handle, "(");
                for ($i = 0; $i < $num_fields; $i++) {
                    if (is_null($row[$i])) {
                        fwrite($handle, "NULL");
                    } else {
                        fwrite($handle, "'" . mysqli_real_escape_string($conn, $row[$i]) . "'");
                    }
                    
                    if ($i < ($num_fields - 1)) {
                        fwrite($handle, ",");
                    }
                }
                
                if ($row_count < mysqli_num_rows($result)) {
                    fwrite($handle, "),\n");
                } else {
                    fwrite($handle, ");\n\n");
                }
            }
        }
    }
    
    fclose($handle);
    
    return basename($backup_file);
}

// Hàm phục hồi dữ liệu từ file sao lưu
function restore_backup($file) {
    global $conn;
    
    $sql = file_get_contents($file);
    
    // Tắt kiểm tra khóa ngoại
    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");
    
    // Thực thi từng câu lệnh SQL
    $queries = explode(';', $sql);
    
    foreach ($queries as $query) {
        $query = trim($query);
        
        if (!empty($query)) {
            $result = mysqli_query($conn, $query);
            
            if (!$result) {
                // Bật lại kiểm tra khóa ngoại
                mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");
                return mysqli_error($conn);
            }
        }
    }
    
    // Bật lại kiểm tra khóa ngoại
    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");
    
    return true;
}

// Hàm lấy danh sách file sao lưu
function get_backup_files() {
    $backup_dir = "../backups";
    $files = [];
    
    if (file_exists($backup_dir)) {
        $dir_handle = opendir($backup_dir);
        
        while ($file = readdir($dir_handle)) {
            if ($file != "." && $file != ".." && pathinfo($file, PATHINFO_EXTENSION) == "sql") {
                $files[] = [
                    'name' => $file,
                    'size' => filesize($backup_dir . "/" . $file),
                    'date' => filemtime($backup_dir . "/" . $file)
                ];
            }
        }
        
        closedir($dir_handle);
        
        // Sắp xếp theo thời gian giảm dần
        usort($files, function($a, $b) {
            return $b['date'] - $a['date'];
        });
    }
    
    return $files;
}

// Hàm định dạng kích thước file
function format_file_size($size) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    
    while ($size >= 1024 && $i < count($units) - 1) {
        $size /= 1024;
        $i++;
    }
    
    return round($size, 2) . ' ' . $units[$i];
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-3">
    <h1 class="h2">Sao lưu & Phục hồi</h1>
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

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Tạo bản sao lưu</h6>
            </div>
            <div class="card-body">
                <p>Tạo bản sao lưu cơ sở dữ liệu để đảm bảo an toàn dữ liệu của bạn. Bạn nên tạo bản sao lưu thường xuyên.</p>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <button type="submit" name="create_backup" class="btn btn-primary">
                        <i class="bi bi-download"></i> Tạo bản sao lưu mới
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Phục hồi dữ liệu</h6>
            </div>
            <div class="card-body">
                <p class="text-danger">Cảnh báo: Phục hồi dữ liệu sẽ ghi đè lên dữ liệu hiện tại. Hãy đảm bảo bạn đã sao lưu dữ liệu hiện tại trước khi thực hiện.</p>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="backup_file" class="form-label">Chọn file sao lưu</label>
                        <input type="file" class="form-control" id="backup_file" name="backup_file" accept=".sql" required>
                    </div>
                    <button type="submit" name="restore_backup" class="btn btn-warning" onclick="return confirm('Bạn có chắc chắn muốn phục hồi dữ liệu? Dữ liệu hiện tại sẽ bị ghi đè.')">
                        <i class="bi bi-upload"></i> Phục hồi dữ liệu
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách bản sao lưu</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Tên file</th>
                        <th>Kích thước</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($backup_files)): ?>
                        <?php foreach ($backup_files as $file): ?>
                            <tr>
                                <td><?php echo $file['name']; ?></td>
                                <td><?php echo format_file_size($file['size']); ?></td>
                                <td><?php echo date('d/m/Y H:i:s', $file['date']); ?></td>
                                <td>
                                    <a href="../backups/<?php echo $file['name']; ?>" class="btn btn-sm btn-primary" download>
                                        <i class="bi bi-download"></i> Tải xuống
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">Chưa có bản sao lưu nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once "includes/footer.php"; ?>
