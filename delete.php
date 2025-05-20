<?php
// Include database connection
require_once "config.php";

// Process delete operation after confirmation
if (isset($_GET["id"]) && !empty($_GET["id"])) {
    // Get URL parameter
    $id = trim($_GET["id"]);
    
    // Prepare a delete statement
    $sql = "DELETE FROM users WHERE id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("i", $id);
        
        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // Records deleted successfully. Redirect to landing page
            header("location: index.php?success=User deleted successfully");
            exit();
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
        
        // Close statement
        $stmt->close();
    }
    
    // Close connection
    $conn->close();
} else {
    // URL doesn't contain id parameter. Redirect to error page
    header("location: error.php");
    exit();
}
?>
