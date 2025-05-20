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

<style>
.admin-chat-wrapper {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  min-height: 70vh;
  width: 100%;
  margin-top: 32px;
  margin-bottom: 32px;
}
.ai-chat-container {
  width: 100%;
  max-width: 600px;
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 4px 32px 0 rgba(0,0,0,0.08);
  display: flex;
  flex-direction: column;
  min-height: 70vh;
  margin: 0 auto;
}
.ai-chat-header {
  padding: 24px 32px 16px 32px;
  border-bottom: 1px solid #f0f0f0;
  background: linear-gradient(90deg, #e3f0ff 0%, #f8fbff 100%);
  border-radius: 18px 18px 0 0;
  display: flex;
  align-items: center;
  gap: 12px;
}
.ai-chat-header h2 {
  font-weight: 700;
  font-size: 1.5rem;
  margin-bottom: 0;
  color: #0071e3;
}
.ai-chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 24px 32px;
  background: #f8fbff;
  border-radius: 0 0 0 0;
  display: flex;
  flex-direction: column;
}
.message {
  display: flex;
  margin-bottom: 18px;
}
.user-message {
  justify-content: flex-end;
}
.ai-message {
  justify-content: flex-start;
}
.message-content {
  max-width: 70%;
  padding: 14px 20px;
  border-radius: 16px;
  font-size: 1rem;
  line-height: 1.6;
  box-shadow: 0 2px 8px 0 rgba(0,0,0,0.04);
  background: #fff;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.user-message .message-content {
  background: linear-gradient(90deg, #e3f0ff 0%, #cbe5ff 100%);
  color: #1d1d1f;
  border-bottom-right-radius: 4px;
  align-items: flex-end;
}
.ai-message .message-content {
  background: #fff;
  color: #0071e3;
  border-bottom-left-radius: 4px;
  align-items: flex-start;
}
.message-content img.chat-image {
  max-width: 220px;
  max-height: 180px;
  border-radius: 10px;
  margin-top: 4px;
  box-shadow: 0 1px 6px 0 rgba(0,0,0,0.07);
  object-fit: cover;
}
.ai-chat-input {
  padding: 16px 32px;
  border-top: 1px solid #f0f0f0;
  background: #fff;
  border-radius: 0 0 18px 18px;
  display: flex;
  gap: 12px;
  align-items: center;
}
.ai-chat-input input[type="text"] {
  flex: 1;
  border: none;
  background: #f8fbff;
  border-radius: 12px;
  padding: 12px 16px;
  font-size: 1rem;
  outline: none;
  box-shadow: 0 1px 4px 0 rgba(0,0,0,0.03);
  transition: box-shadow 0.2s;
}
.ai-chat-input input[type="text"]:focus {
  box-shadow: 0 2px 8px 0 rgba(0,113,227,0.08);
}
.ai-chat-input button {
  background: #0071e3;
  color: #fff;
  border: none;
  border-radius: 12px;
  padding: 0 20px;
  font-size: 1.2rem;
  font-weight: 600;
  transition: background 0.2s;
  display: flex;
  align-items: center;
  gap: 6px;
}
.ai-chat-input button:hover {
  background: #005bb5;
}
.ai-chat-input label[for="image-input"] {
  margin: 0;
  padding: 0 10px;
  cursor: pointer;
  color: #0071e3;
  font-size: 1.4rem;
  display: flex;
  align-items: center;
  border-radius: 8px;
  transition: background 0.2s;
}
.ai-chat-input label[for="image-input"]:hover {
  background: #e3f0ff;
}
.ai-chat-input input[type="file"] {
  display: none;
}
@media (max-width: 900px) {
  .admin-chat-wrapper {
    margin-top: 8px;
    margin-bottom: 8px;
  }
  .ai-chat-container {
    max-width: 98vw;
    min-height: 60vh;
  }
  .ai-chat-header, .ai-chat-messages, .ai-chat-input {
    padding-left: 8px;
    padding-right: 8px;
  }
  .ai-chat-messages {
    padding-top: 12px;
    padding-bottom: 12px;
  }
}
</style>

<div class="admin-chat-wrapper">
  <div class="ai-chat-container">
    <div class="ai-chat-header">
      <span style="font-size:1.7rem;color:#0071e3;"><i class="bi bi-robot"></i></span>
      <h2 class="mb-0">Trợ lý AI quản trị</h2>
    </div>
    <div id="chat-messages" class="ai-chat-messages">
      <div class="message ai-message">
        <div class="message-content">
          <span style="font-size:1.2rem;">🤖</span> Xin chào! Tôi là trợ lý AI. Hãy hỏi tôi bất cứ điều gì về dữ liệu cửa hàng của bạn.<br>
          <span style="color:#888;font-size:0.95em;">Ví dụ: "Có bao nhiêu đơn hàng hôm nay?", "Sản phẩm nào còn ít hàng?", "Khách hàng mới nhất là ai?"</span>
        </div>
      </div>
    </div>
    <form class="ai-chat-input" onsubmit="return false;">
      <label for="image-input" title="Gửi ảnh"><i class="bi bi-image"></i></label>
      <input type="file" id="image-input" accept="image/*" />
      <input type="text" id="user-input" placeholder="Nhập câu hỏi..." autocomplete="off" />
      <button type="button" id="send-button"><i class="bi bi-send"></i> Gửi</button>
    </form>
  </div>
</div>

<script src="../assets/js/chat.js"></script>
<script>
// Xử lý gửi ảnh (chỉ UI, backend xử lý sau)
document.addEventListener('DOMContentLoaded', function() {
  const imageInput = document.getElementById('image-input');
  const chatMessages = document.getElementById('chat-messages');
  imageInput.addEventListener('change', function(e) {
    if (imageInput.files && imageInput.files[0]) {
      const file = imageInput.files[0];
      const reader = new FileReader();
      reader.onload = function(ev) {
        // Thêm ảnh vào chat như tin nhắn user
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message user-message';
        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';
        const img = document.createElement('img');
        img.src = ev.target.result;
        img.className = 'chat-image';
        contentDiv.appendChild(img);
        messageDiv.appendChild(contentDiv);
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
      };
      reader.readAsDataURL(file);
    }
    // Reset input để có thể gửi lại cùng 1 ảnh nếu muốn
    imageInput.value = '';
  });
});
</script>
<?php include 'includes/footer.php'; ?>
