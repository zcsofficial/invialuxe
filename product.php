<?php
session_start();
require_once 'config.php';
require_once 'products.php';

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$product_id = $_GET['id'];

// Fetch product details
$stmt = $conn->prepare("SELECT p.*, u.username FROM products p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    header("Location: index.php");
    exit;
}

// Add to cart handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    if (!in_array($product_id, $_SESSION['cart'])) {
        $_SESSION['cart'][] = $product_id;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Invialuxe</title>
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
    <style>
        @media (max-width: 640px) {
            .product-container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body class="bg-white">
    <header class="fixed top-0 left-0 right-0 bg-white shadow-sm z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex-shrink-0">
                    <h1 class="font-['Pacifico'] text-2xl text-primary">Invialuxe</h1>
                </div>
                <div class="flex items-center space-x-4 sm:space-x-6">
                    <a href="index.php" class="w-10 h-10 flex items-center justify-center" title="Home">
                        <i class="ri-home-line text-xl"></i>
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="index.php?logout=1" class="w-10 h-10 flex items-center justify-center" title="Logout">
                            <i class="ri-logout-box-line text-xl"></i>
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="w-10 h-10 flex items-center justify-center" title="Login">
                            <i class="ri-user-line text-xl"></i>
                        </a>
                    <?php endif; ?>
                    <a href="cart.php" class="w-10 h-10 flex items-center justify-center relative">
                        <i class="ri-shopping-bag-line text-xl"></i>
                        <span id="cart-counter" class="absolute top-0 right-0 w-5 h-5 bg-primary text-white rounded-full text-xs flex items-center justify-center">
                            <?php echo count($_SESSION['cart'] ?? []); ?>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="mt-24 px-4 sm:px-6 lg:px-8">
        <section class="max-w-7xl mx-auto py-16">
            <div class="product-container flex flex-col sm:flex-row gap-8">
                <div class="w-full sm:w-1/2">
                    <img src="<?php echo $product['image']; ?>" 
                         class="w-full h-auto rounded-lg object-cover" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                <div class="w-full sm:w-1/2">
                    <h1 class="text-3xl font-['Playfair_Display'] font-bold mb-4"><?php echo htmlspecialchars($product['name']); ?></h1>
                    <p class="text-gray-600 mb-4">$<?php echo number_format($product['price'], 2); ?></p>
                    <p class="text-gray-700 mb-4"><?php echo htmlspecialchars($product['description']); ?></p>
                    <p class="text-gray-500 mb-6">Created by: <?php echo htmlspecialchars($product['username']); ?></p>
                    
                    <form method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <button type="submit" name="add_to_cart" 
                                class="w-full bg-primary text-white py-3 rounded-button hover:bg-primary/90 transition-colors font-medium">
                            Add to Cart
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-gray-900 text-white py-12">
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