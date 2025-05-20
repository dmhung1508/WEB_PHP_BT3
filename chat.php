<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$page_title = "Chat với AI";
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Chat với AI</h1>
            </div>

            <div class="row">
                <!-- Chat Interface -->
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Trợ lý AI</h5>
                        </div>
                        <div class="card-body">
                            <div id="chat-messages" class="chat-container mb-3" style="height: 400px; overflow-y: auto;">
                                <div class="message ai-message">
                                    <div class="message-content">
                                        <p>Xin chào! Tôi là trợ lý AI. Bạn có thể hỏi tôi về dữ liệu của bạn và tôi sẽ giúp bạn tạo các truy vấn SQL.</p>
                                        <p>Ví dụ:</p>
                                        <ul>
                                            <li>Hiển thị 10 sản phẩm bán chạy nhất</li>
                                            <li>Doanh thu theo tháng trong năm nay</li>
                                            <li>Danh sách khách hàng đã mua hàng trong tuần qua</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="input-group">
                                <input type="text" id="user-input" class="form-control" placeholder="Nhập câu hỏi của bạn...">
                                <button class="btn btn-primary" type="button" id="send-button">
                                    <i class="bi bi-send"></i> Gửi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SQL Query and Results -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">SQL Query</h5>
                            <span id="query-status" class="badge bg-secondary">Chờ truy vấn</span>
                        </div>
                        <div class="card-body">
                            <pre id="sql-query" class="bg-light p-3 rounded mb-3" style="max-height: 150px; overflow-y: auto;">-- SQL query sẽ hiển thị ở đây</pre>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Kết quả</h5>
                        </div>
                        <div class="card-body">
                            <div id="query-results" style="max-height: 250px; overflow-y: auto;">
                                <div class="text-center text-muted py-5">
                                    <i class="bi bi-database-fill" style="font-size: 2rem;"></i>
                                    <p class="mt-2">Kết quả truy vấn sẽ hiển thị ở đây</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="../assets/js/chat.js"></script>
<?php include 'includes/footer.php'; ?>
