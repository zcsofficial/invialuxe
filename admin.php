<?php
session_start();
require_once 'config.php';

// Admin credentials (hardcoded for simplicity)
$admin_username = "admin";
$admin_password = "Admin@123"; // In production, hash this and store in DB

// Handle admin login
if (!isset($_SESSION['admin_logged_in'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        if ($username === $admin_username && $password === $admin_password) {
            $_SESSION['admin_logged_in'] = true;
        } else {
            $login_error = "Invalid username or password";
        }
    }
}

// Handle product addition
if (isset($_SESSION['admin_logged_in']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $user_id = 1; // Default user_id (you might want to make this dynamic)
    
    // Handle image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $image_name = time() . '_' . basename($_FILES['image']['name']);
        $image_path = $upload_dir . $image_name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            $image = $image_path;
        } else {
            $product_error = "Failed to upload image";
        }
    }
    
    if (!$product_error) {
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, image, category, user_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdssi", $name, $description, $price, $image, $category, $user_id);
        if ($stmt->execute()) {
            $product_success = "Product added successfully";
        } else {
            $product_error = "Failed to add product: " . $conn->error;
        }
        $stmt->close();
    }
}

// Redirect if not logged in
if (!isset($_SESSION['admin_logged_in'])) {
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Invialuxe</title>
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
        <h1 class="font-['Pacifico'] text-3xl text-primary text-center mb-6">Admin Login</h1>
        
        <?php if (isset($login_error)): ?>
            <p class="text-red-500 text-center mb-4"><?php echo $login_error; ?></p>
        <?php endif; ?>
        
        <form method="POST" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <div class="relative">
                    <input type="text" id="username" name="username" placeholder="Enter username" 
                           class="w-full px-4 py-2 mt-1 border border-gray-200 rounded-button focus:outline-none focus:border-primary" 
                           required>
                    <i class="ri-user-line absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <div class="relative">
                    <input type="password" id="password" name="password" placeholder="Enter password" 
                           class="w-full px-4 py-2 mt-1 border border-gray-200 rounded-button focus:outline-none focus:border-primary" 
                           required>
                    <i class="ri-lock-line absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
            
            <button type="submit" name="login" 
                    class="w-full bg-primary text-white py-3 rounded-button hover:bg-primary/90 transition-colors font-medium">
                Login
            </button>
        </form>
    </div>
</body>
</html>

<?php
} else {
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Invialuxe</title>
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
<body class="bg-gray-100 min-h-screen">
    <header class="bg-white shadow-sm py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <h1 class="font-['Pacifico'] text-2xl text-primary">Invialuxe Admin</h1>
            <a href="index.php?logout=1" class="text-gray-700 hover:text-primary flex items-center">
                <i class="ri-logout-box-line mr-2"></i> Logout
            </a>
        </div>
    </header>

    <main class="max-w-4xl mx-auto mt-8 px-4 sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-3xl font-['Playfair_Display'] font-bold mb-6">Add New Product</h2>
            
            <?php if (isset($product_success)): ?>
                <p class="text-green-500 mb-4"><?php echo $product_success; ?></p>
            <?php elseif (isset($product_error)): ?>
                <p class="text-red-500 mb-4"><?php echo $product_error; ?></p>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Product Name</label>
                    <input type="text" id="name" name="name" placeholder="Enter product name" 
                           class="w-full px-4 py-2 mt-1 border border-gray-200 rounded-button focus:outline-none focus:border-primary" 
                           required>
                </div>
                
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="description" name="description" placeholder="Enter product description" 
                              class="w-full px-4 py-2 mt-1 border border-gray-200 rounded-button focus:outline-none focus:border-primary" 
                              rows="4" required></textarea>
                </div>
                
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700">Price ($)</label>
                    <input type="number" id="price" name="price" step="0.01" placeholder="Enter price" 
                           class="w-full px-4 py-2 mt-1 border border-gray-200 rounded-button focus:outline-none focus:border-primary" 
                           required>
                </div>
                
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                    <select id="category" name="category" 
                            class="w-full px-4 py-2 mt-1 border border-gray-200 rounded-button focus:outline-none focus:border-primary" 
                            required>
                        <option value="Necklaces">Necklaces</option>
                        <option value="Earrings">Earrings</option>
                        <option value="Bracelets">Bracelets</option>
                        <option value="Rings">Rings</option>
                    </select>
                </div>
                
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700">Product Image</label>
                    <input type="file" id="image" name="image" accept="image/*" 
                           class="w-full px-4 py-2 mt-1 border border-gray-200 rounded-button focus:outline-none focus:border-primary" 
                           required>
                </div>
                
                <button type="submit" name="add_product" 
                        class="w-full bg-primary text-white py-3 rounded-button hover:bg-primary/90 transition-colors font-medium">
                    Add Product
                </button>
            </form>
        </div>
    </main>

    <footer class="bg-gray-900 text-white py-12 mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
                <div>
                    <h1 class="font-['Pacifico'] text-2xl mb-4">Invialuxe</h1>
                    <p class="text-gray-400">Collaborative Luxury Jewelry Platform</p>
                </div>
                <div>
                    <h4 class="font-medium mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">About Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-medium mb-4">Follow Us</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20">
                            <i class="ri-instagram-line"></i>
                        </a>
                        <a href="#" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20">
                            <i class="ri-facebook-line"></i>
                        </a>
                    </div>
                </div>
                <div>
                    <h4 class="font-medium mb-4">Contact</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li>contact@invialuxe.com</li>
                        <li>+1 (555) 123-4567</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-12 pt-8 text-center">
                <p class="text-gray-400">© 2025 Invialuxe. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>

<?php
}
?>