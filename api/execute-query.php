<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Get JSON data from request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['query']) || empty($data['query'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No query provided']);
    exit;
}

$query = trim($data['query']);

// Security check: Only allow SELECT queries
if (!preg_match('/^SELECT\s/i', $query)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Only SELECT queries are allowed for security reasons']);
    exit;
}

try {
    // Execute the query
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    // Fetch all results
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return results as JSON
    header('Content-Type: application/json');
    echo json_encode(['results' => $results]);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
