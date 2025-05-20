document.addEventListener("DOMContentLoaded", () => {
  // Auto hide alerts after 5 seconds
  const alerts = document.querySelectorAll(".alert")
  alerts.forEach((alert) => {
    setTimeout(() => {
      const bsAlert = new bootstrap.Alert(alert)
      bsAlert.close()
    }, 5000)
  })

  // Quantity input controls
  const quantityInputs = document.querySelectorAll(".quantity-input")
  quantityInputs.forEach((input) => {
    const minusBtn = input.previousElementSibling
    const plusBtn = input.nextElementSibling

    if (minusBtn && minusBtn.classList.contains("quantity-minus")) {
      minusBtn.addEventListener("click", () => {
        if (input.value > 1) {
          input.value = Number.parseInt(input.value) - 1
          if (input.hasAttribute("data-product-id")) {
            updateCartQuantity(input)
          }
        }
      })
    }

    if (plusBtn && plusBtn.classList.contains("quantity-plus")) {
      plusBtn.addEventListener("click", () => {
        const maxStock = parseInt(input.getAttribute("max")) || 9999;
        const warning = document.getElementById(`stock-warning-${input.getAttribute("data-product-id")}`);
        if (Number.parseInt(input.value) < maxStock) {
          input.value = Number.parseInt(input.value) + 1
          if (warning) warning.classList.add("d-none");
          if (input.hasAttribute("data-product-id")) {
            updateCartQuantity(input)
          }
        } else {
          if (warning) warning.classList.remove("d-none");
        }
      })
    }

    input.addEventListener("change", () => {
      const maxStock = parseInt(input.getAttribute("max")) || 9999;
      const warning = document.getElementById(`stock-warning-${input.getAttribute("data-product-id")}`);
      if (Number.parseInt(input.value) < 1) {
        input.value = 1
      }
      if (Number.parseInt(input.value) > maxStock) {
        input.value = maxStock;
        if (warning) warning.classList.remove("d-none");
      } else {
        if (warning) warning.classList.add("d-none");
      }
      if (input.hasAttribute("data-product-id")) {
        updateCartQuantity(input)
      }
    })
  })

  // Function to update cart quantity
  function updateCartQuantity(input) {
    const productId = input.getAttribute("data-product-id")
    const quantity = input.value

    // Create form data
    const formData = new FormData()
    formData.append("product_id", productId)
    formData.append("quantity", quantity)
    formData.append("update_cart", true)

    // Send AJAX request
    fetch("cart.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Update subtotal
          const subtotalElement = document.querySelector(`#subtotal-${productId}`)
          if (subtotalElement) {
            subtotalElement.textContent = data.subtotal
          }

          // Update cart total (tạm tính)
          const cartTotalElement = document.querySelector("#cart-total")
          if (cartTotalElement) {
            cartTotalElement.textContent = data.total
          }
          // Update grand total (tổng cộng)
          const cartGrandTotalElement = document.querySelector("#cart-grand-total")
          if (cartGrandTotalElement) {
            cartGrandTotalElement.textContent = data.grand_total
          }
        }
      })
      .catch((error) => {
        console.error("Error:", error)
      })
  }
})
