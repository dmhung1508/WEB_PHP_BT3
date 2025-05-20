document.addEventListener("DOMContentLoaded", () => {
  const chatMessages = document.getElementById("chat-messages")
  const userInput = document.getElementById("user-input")
  const sendButton = document.getElementById("send-button")
  // const sqlQuery = document.getElementById("sql-query")
  // const queryResults = document.getElementById("query-results")
  // const queryStatus = document.getElementById("query-status")

  // Function to add a message to the chat
  function addMessage(message, isUser = false) {
    const messageDiv = document.createElement("div")
    messageDiv.className = `message ${isUser ? "user-message" : "ai-message"}`

    const contentDiv = document.createElement("div")
    contentDiv.className = "message-content"
    contentDiv.innerHTML = `<p>${message}</p>`

    messageDiv.appendChild(contentDiv)
    chatMessages.appendChild(messageDiv)
    chatMessages.scrollTop = chatMessages.scrollHeight
  }

  // Function to send message to AI and get response
  function sendMessage() {
    const message = userInput.value.trim()
    if (message === "") return

    // Add user message to chat
    addMessage(message, true)
    userInput.value = ""

    // Show loading indicator
    const loadingDiv = document.createElement("div")
    loadingDiv.className = "message ai-message"
    loadingDiv.innerHTML = `
            <div class="message-content">
                <div class="d-flex align-items-center">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span>Đang xử lý...</span>
                </div>
            </div>
        `
    chatMessages.appendChild(loadingDiv)
    chatMessages.scrollTop = chatMessages.scrollHeight

    // Gửi API thật
    fetch("../api/chat.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ question: message }),
    })
      .then((response) => response.json())
      .then((data) => {
        chatMessages.removeChild(loadingDiv)
        if (data.answer) {
          addMessage(data.answer)
        } else if (data.error) {
          addMessage("<span style='color:red'>Lỗi: " + data.error + "</span>")
        } else {
          addMessage("<span style='color:red'>Không nhận được phản hồi từ AI.</span>")
        }
        if (data.log) {
          console.log("[AI LOG]", data.log)
        }
      })
      .catch((error) => {
        chatMessages.removeChild(loadingDiv)
        addMessage("<span style='color:red'>Lỗi kết nối: " + error.message + "</span>")
        console.error("[AI ERROR]", error)
      })
  }

  // Event listeners
  sendButton.addEventListener("click", sendMessage)

  userInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter") {
      sendMessage()
    }
  })
})
