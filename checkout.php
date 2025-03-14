<?php
session_start();
require_once 'config.php';
require_once 'products.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if cart is empty
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

// Handle checkout form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_purchase'])) {
    $user_id = $_SESSION['user_id'];
    $billing_name = $_POST['billing_name'];
    $billing_address = $_POST['billing_address'];
    $billing_city = $_POST['billing_city'];
    $billing_state = $_POST['billing_state'];
    $billing_zip = $_POST['billing_zip'];
    $billing_country = $_POST['billing_country'];
    $payment_method = $_POST['payment_method'];

    // Calculate total
    $products = getProducts();
    $cart_items = array_filter($products, function($product) {
        return in_array($product['id'], $_SESSION['cart']);
    });
    $total = 0;
    foreach ($cart_items as $item) {
        $total += $item['price'];
    }

    // Insert order into database
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total, billing_name, billing_address, billing_city, billing_state, billing_zip, billing_country, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("idsssssss", $user_id, $total, $billing_name, $billing_address, $billing_city, $billing_state, $billing_zip, $billing_country, $payment_method);
    
    if ($stmt->execute()) {
        $order_id = $stmt->insert_id;
        
        // Insert order items
        $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, price) VALUES (?, ?, ?)");
        foreach ($cart_items as $item) {
            $item_stmt->bind_param("iid", $order_id, $item['id'], $item['price']);
            $item_stmt->execute();
        }
        $item_stmt->close();
        
        // Clear cart
        $_SESSION['cart'] = [];
        $checkout_success = "Order placed successfully! Order ID: #$order_id";
    } else {
        $checkout_error = "Failed to place order: " . $conn->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Invialuxe</title>
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
        @media (max-width: 640px) {
            .checkout-container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <header class="bg-white shadow-sm py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <h1 class="font-['Pacifico'] text-2xl text-primary">Invialuxe</h1>
            <div class="flex items-center space-x-4 sm:space-x-6">
                <a href="cart.php" class="text-gray-700 hover:text-primary">Back to Cart</a>
                <a href="index.php?logout=1" class="text-gray-700 hover:text-primary">Logout</a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto mt-8 px-4 sm:px-6 lg:px-8">
        <div class="checkout-container flex flex-col sm:flex-row gap-8">
            <div class="w-full sm:w-2/3 bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-3xl font-['Playfair_Display'] font-bold mb-6">Checkout</h2>
                
                <?php if (isset($checkout_success)): ?>
                    <p class="text-green-500 mb-4"><?php echo $checkout_success; ?></p>
                    <a href="index.php" class="block text-primary hover:underline">Continue Shopping</a>
                <?php elseif (isset($checkout_error)): ?>
                    <p class="text-red-500 mb-4"><?php echo $checkout_error; ?></p>
                <?php else: ?>
                    <form method="POST" class="space-y-6">
                        <h3 class="text-xl font-medium mb-4">Billing & Shipping Information</h3>
                        <div>
                            <label for="billing_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                            <input type="text" id="billing_name" name="billing_name" placeholder="Enter your full name" 
                                   class="w-full px-4 py-2 mt-1 border border-gray-200 rounded-button focus:outline-none focus:border-primary" 
                                   required>
                        </div>
                        <div>
                            <label for="billing_address" class="block text-sm font-medium text-gray-700">Address</label>
                            <input type="text" id="billing_address" name="billing_address" placeholder="Enter your address" 
                                   class="w-full px-4 py-2 mt-1 border border-gray-200 rounded-button focus:outline-none focus:border-primary" 
                                   required>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label for="billing_city" class="block text-sm font-medium text-gray-700">City</label>
                                <input type="text" id="billing_city" name="billing_city" placeholder="City" 
                                       class="w-full px-4 py-2 mt-1 border border-gray-200 rounded-button focus:outline-none focus:border-primary" 
                                       required>
                            </div>
                            <div>
                                <label for="billing_state" class="block text-sm font-medium text-gray-700">State</label>
                                <input type="text" id="billing_state" name="billing_state" placeholder="State" 
                                       class="w-full px-4 py-2 mt-1 border border-gray-200 rounded-button focus:outline-none focus:border-primary" 
                                       required>
                            </div>
                            <div>
                                <label for="billing_zip" class="block text-sm font-medium text-gray-700">ZIP Code</label>
                                <input type="text" id="billing_zip" name="billing_zip" placeholder="ZIP" 
                                       class="w-full px-4 py-2 mt-1 border border-gray-200 rounded-button focus:outline-none focus:border-primary" 
                                       required>
                            </div>
                        </div>
                        <div>
                            <label for="billing_country" class="block text-sm font-medium text-gray-700">Country</label>
                            <input type="text" id="billing_country" name="billing_country" placeholder="Country" 
                                   class="w-full px-4 py-2 mt-1 border border-gray-200 rounded-button focus:outline-none focus:border-primary" 
                                   required>
                        </div>
                        
                        <h3 class="text-xl font-medium mb-4 mt-6">Payment Method</h3>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="radio" name="payment_method" value="credit_card" class="mr-2" required>
                                <span>Credit/Debit Card</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="payment_method" value="paypal" class="mr-2">
                                <span>PayPal</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="payment_method" value="bank_transfer" class="mr-2">
                                <span>Bank Transfer</span>
                            </label>
                        </div>
                        
                        <button type="submit" name="complete_purchase" 
                                class="w-full mt-6 bg-primary text-white py-3 rounded-button hover:bg-primary/90 transition-colors font-medium">
                            Complete Purchase
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="w-full sm:w-1/3 bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-['Playfair_Display'] font-bold mb-4">Order Summary</h3>
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
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <img src="<?php echo $item['image']; ?>" 
                                 class="w-12 h-12 object-cover rounded" 
                                 alt="<?php echo $item['name']; ?>">
                            <span class="text-sm"><?php echo $item['name']; ?></span>
                        </div>
                        <span class="text-gray-700">$<?php echo number_format($item['price'], 2); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-4 border-t pt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-medium">Total:</span>
                        <span class="text-lg font-medium">$<?php echo number_format($total, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
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