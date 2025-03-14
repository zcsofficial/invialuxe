<?php
session_start();
require_once 'config.php'; // Database connection

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = isset($_POST['role']) && $_POST['role'] === 'expert' ? 'expert' : 'customer';
    
    // Basic validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Username or email already taken";
        } else {
            // Hash password and insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $insert_stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
            
            if ($insert_stmt->execute()) {
                // Auto-login after registration
                $user_id = $insert_stmt->insert_id;
                $_SESSION['user_id'] = $user_id;
                $_SESSION['role'] = $role;
                header("Location: index.php");
                exit;
            } else {
                $error = "Registration failed: " . $conn->error;
            }
            $insert_stmt->close();
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Invialuxe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#9F7E69',
                    secondary: '#E6D5C9'
                },
                borderRadius: {
                    'button': '8px'
                }
            }
        }
    }
    </script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h1 class="font-['Pacifico'] text-3xl text-primary text-center mb-6">Invialuxe Register</h1>
        
        <?php if (isset($error)): ?>
            <p class="text-red-500 text-center mb-4"><?php echo $error; ?></p>
        <?php endif; ?>
        
        <form method="POST" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <div class="relative">
                    <input type="text" id="username" name="username" placeholder="Enter your username" 
                           class="w-full px-4 py-2 mt-1 border border-gray-200 rounded-button focus:outline-none focus:border-primary" 
                           required>
                    <i class="ri-user-line absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <div class="relative">
                    <input type="email" id="email" name="email" placeholder="Enter your email" 
                           class="w-full px-4 py-2 mt-1 border border-gray-200 rounded-button focus:outline-none focus:border-primary" 
                           required>
                    <i class="ri-mail-line absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <div class="relative">
                    <input type="password" id="password" name="password" placeholder="Enter your password" 
                           class="w-full px-4 py-2 mt-1 border border-gray-200 rounded-button focus:outline-none focus:border-primary" 
                           required>
                    <i class="ri-lock-line absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                <div class="flex items-center space-x-4">
                    <label class="flex items-center">
                        <input type="radio" name="role" value="customer" class="mr-2" checked>
                        <span>Customer</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="role" value="expert" class="mr-2">
                        <span>Expert</span>
                    </label>
                </div>
            </div>
            
            <button type="submit" 
                    class="w-full bg-primary text-white py-3 rounded-button hover:bg-primary/90 transition-colors font-medium">
                Register
            </button>
        </form>
        
        <p class="mt-4 text-center text-sm text-gray-600">
            Already have an account? 
            <a href="login.php" class="text-primary hover:underline">Login here</a>
        </p>
        
        <a href="index.php" class="block mt-4 text-center text-sm text-primary hover:underline">
            Back to Home
        </a>
    </div>
</body>
</html>