<?php
require_once 'config.php'; // Include database connection

// Get all products
function getProducts() {
    global $conn;
    $query = "SELECT p.*, u.username 
             FROM products p 
             JOIN users u ON p.user_id = u.id";
    $result = mysqli_query($conn, $query);
    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Add a collaboration
function addCollaboration($product_id, $username_or_email) {
    global $conn;
    
    // Find user by username or email
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username_or_email, $username_or_email);
    $stmt->execute();
    $result = $stmt->get_result();
    $collaborator = $result->fetch_assoc();
    
    if ($collaborator) {
        // Check if collaboration already exists
        $check_stmt = $conn->prepare("SELECT id FROM collaborations WHERE product_id = ? AND collaborator_id = ?");
        $check_stmt->bind_param("ii", $product_id, $collaborator['id']);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows == 0) {
            // Add new collaboration
            $insert_stmt = $conn->prepare("INSERT INTO collaborations (product_id, collaborator_id) VALUES (?, ?)");
            $insert_stmt->bind_param("ii", $product_id, $collaborator['id']);
            $success = $insert_stmt->execute();
            $insert_stmt->close();
            $check_stmt->close();
            $stmt->close();
            return $success;
        }
        $check_stmt->close();
        $stmt->close();
        return false; // Collaboration already exists
    }
    $stmt->close();
    return false; // Collaborator not found
}

// Add a review
function addReview($product_id, $rating, $comment) {
    global $conn;
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $product_id, $user_id, $rating, $comment);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

// Get videos for a product (for experts)
function getVideos($product_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT v.*, u.role 
                           FROM videos v 
                           JOIN users u ON v.user_id = u.id 
                           WHERE v.product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $videos = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $videos;
}
?>