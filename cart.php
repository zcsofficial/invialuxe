<?php
session_start();
require_once 'config.php'; // Database connection
require_once 'products.php'; // Product functions

// Check if user is logged in (optional, remove if cart should be accessible to guests)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle item removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    $product_id = $_POST['product_id'];
    $key = array_search($product_id, $_SESSION['cart']);
    if ($key !== false) {
        unset($_SESSION['cart'][$key]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - Invialuxe</title>
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
        html, body {
            height: 100%;
            margin: 0;
        }
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        main {
            flex: 1 0 auto;
        }
        footer {
            flex-shrink: 0;
        }
    </style>
</head>
<body class="bg-gray-100">
    <header class="bg-white shadow-sm py-4">
        <div class="max-w-7xl mx-auto px-4 flex justify-between items-center">
            <h1 class="font-['Pacifico'] text-2xl text-primary">Invialuxe</h1>
            <div class="flex items-center space-x-6">
                <a href="index.php" class="text-gray-700 hover:text-primary">Continue Shopping</a>
                <a href="index.php?logout=1" class="text-gray-700 hover:text-primary">Logout</a>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto mt-8 px-4">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-3xl font-['Playfair_Display'] font-bold mb-6">Your Cart</h2>
            
            <?php if (empty($_SESSION['cart'])): ?>
                <p class="text-gray-600 text-center">Your cart is empty</p>
                <a href="index.php" class="block mt-4 text-center text-primary hover:underline">Start Shopping</a>
            <?php else: ?>
                <div class="space-y-4">
                    <?php
                    $products = getProducts();
                    $cart_items = array_filter($products, function($product) {
                        return in_array($product['id'], $_SESSION['cart']);
                    });
                    $total = 0;
                    
                    foreach ($cart_items as $item):
                        $total += $item['price'];
                    ?>
                    <div class="flex items-center justify-between border-b pb-4">
                        <div class="flex items-center space-x-4">
                            <img src="<?php echo $item['image']; ?>" 
                                 class="w-20 h-20 object-cover rounded" 
                                 alt="<?php echo $item['name']; ?>">
                            <div>
                                <h3 class="font-medium"><?php echo $item['name']; ?></h3>
                                <p class="text-gray-600 text-sm">By <?php echo $item['username']; ?></p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <span class="text-gray-700">$<?php echo number_format($item['price'], 2); ?></span>
                            <form method="POST">
                                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" name="remove_item" 
                                        class="text-red-500 hover:text-red-700">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-6 border-t pt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-medium">Total:</span>
                        <span class="text-lg font-medium">$<?php echo number_format($total, 2); ?></span>
                    </div>
                    <a href="checkout.php" 
                       class="block w-full mt-4 bg-primary text-white py-3 rounded-button hover:bg-primary/90 transition-colors font-medium text-center">
                        Proceed to Checkout
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-8">
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