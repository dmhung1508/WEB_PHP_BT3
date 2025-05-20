<?php
// Include database connection
require_once "config.php";

// Check existence of id parameter before processing further
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    // Get URL parameter
    $id = trim($_GET["id"]);
    
    // Prepare a select statement
    $sql = "SELECT * FROM users WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("i", $id);
        
        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                /* Fetch result row as an associative array. Since the result set
                contains only one row, we don't need to use while loop */
                $row = $result->fetch_array(MYSQLI_ASSOC);
                
                // Retrieve individual field value
                $name = $row["name"];
                $email = $row["email"];
                $phone = $row["phone"];
                $created_at = $row["created_at"];
            } else {
                // URL doesn't contain valid id parameter. Redirect to error page
                header("location: error.php");
                exit();
            }
            
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
        
        // Close statement
        $stmt->close();
    }
    
} else {
    // URL doesn't contain id parameter. Redirect to error page
    header("location: error.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View User - PHP Bootstrap MySQL Example</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">PHP MySQL App</a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h4>View User</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="fw-bold">Name:</label>
                            <p class="form-control"><?php echo $name; ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Email:</label>
                            <p class="form-control"><?php echo $email; ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Phone:</label>
                            <p class="form-control"><?php echo $phone; ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Created At:</label>
                            <p class="form-control"><?php echo $created_at; ?></p>
                        </div>
                        <p>
                            <a href="index.php" class="btn btn-primary">Back</a>
                            <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-warning">Edit</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
