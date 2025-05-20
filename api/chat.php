<?php
require_once "../includes/db.php";
require_once "../includes/functions.php";

// Check if user is admin
check_admin();

// Set content type to JSON
header('Content-Type: application/json');

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['question']) || empty($data['question'])) {
    echo json_encode([
        'error' => 'Câu hỏi không được để trống',
        'log' => 'Không nhận được question từ client.'
    ]);
    exit;
}

$question = $data['question'];

// Lấy dữ liệu chính từ DB (giới hạn số lượng để tránh quá tải prompt)
$products = mysqli_query($conn, "SELECT id, name, price, stock FROM products ORDER BY id DESC LIMIT 20");
$orders = mysqli_query($conn, "SELECT id, total_price, status, created_at FROM orders ORDER BY id DESC LIMIT 20");
$customers = mysqli_query($conn, "SELECT id, name, email FROM users WHERE role='customer' ORDER BY id DESC LIMIT 20");

$product_data = [];
while ($row = mysqli_fetch_assoc($products)) $product_data[] = $row;
$order_data = [];
while ($row = mysqli_fetch_assoc($orders)) $order_data[] = $row;
$customer_data = [];
while ($row = mysqli_fetch_assoc($customers)) $customer_data[] = $row;

// Tạo system prompt
$system_prompt = "Bạn là trợ lý AI cho admin cửa hàng. Dưới đây là dữ liệu hệ thống:\n";
$system_prompt .= "Sản phẩm: " . json_encode($product_data, JSON_UNESCAPED_UNICODE) . "\n";
$system_prompt .= "Đơn hàng: " . json_encode($order_data, JSON_UNESCAPED_UNICODE) . "\n";
$system_prompt .= "Khách hàng: " . json_encode($customer_data, JSON_UNESCAPED_UNICODE) . "\n";
$system_prompt .= "Hãy trả lời câu hỏi của admin dựa trên dữ liệu trên. Trả lời ngắn gọn, dễ hiểu, không trả về SQL.";

// Gửi lên OpenAI GPT-4o
$api_key = "________ĐIỀN API KEY CỦA BẠN________"; // Thay thế bằng API key của bạn
if (!$api_key) {
    echo json_encode(['error' => 'Chưa cấu hình API key OpenAI.', 'log' => 'API key rỗng']);
    exit;
}

$payload = [
    "model" => "gpt-4o-mini",
    "messages" => [
        ["role" => "system", "content" => $system_prompt],
        ["role" => "user", "content" => $question]
    ],
    "max_tokens" => 512,
    "temperature" => 0.2
];

$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $api_key
]);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

$log = [
    'http_code' => $http_code,
    'curl_error' => $curl_error,
    'openai_response' => $response,
    'system_prompt' => $system_prompt,
    'user_question' => $question,
    'payload' => $payload
];

if ($http_code !== 200) {
    echo json_encode([
        'error' => 'Lỗi khi gọi OpenAI API.',
        'log' => $log
    ]);
    exit;
}

$result = json_decode($response, true);
$answer = $result['choices'][0]['message']['content'] ?? 'Không có phản hồi từ AI.';

// Trả về cả answer và log để debug (có thể ẩn log ở frontend nếu muốn)
echo json_encode([
    'answer' => $answer,
    'log' => $log
]);
