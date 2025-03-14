<?php
session_start();
require_once 'config.php';
require_once 'products.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user's orders
$stmt = $conn->prepare("SELECT o.*, COUNT(oi.id) as item_count 
                        FROM orders o 
                        LEFT JOIN order_items oi ON o.id = oi.order_id 
                        WHERE o.user_id = ? 
                        GROUP BY o.id 
                        ORDER BY o.created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Orders - Invialuxe</title>
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
        .order-details {
            display: none;
        }
        .order-details.active {
            display: block;
        }
        @media (max-width: 640px) {
            .order-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <header class="bg-white shadow-sm py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <h1 class="font-['Pacifico'] text-2xl text-primary">Invialuxe</h1>
            <div class="flex items-center space-x-4 sm:space-x-6">
                <a href="index.php" class="text-gray-700 hover:text-primary">Home</a>
                <a href="cart.php" class="text-gray-700 hover:text-primary">Cart</a>
                <a href="index.php?logout=1" class="text-gray-700 hover:text-primary">Logout</a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto mt-8 px-4 sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-3xl font-['Playfair_Display'] font-bold mb-6">Your Orders</h2>
            
            <?php if (empty($orders)): ?>
                <p class="text-gray-600 text-center">You have no orders yet.</p>
                <a href="index.php" class="block mt-4 text-center text-primary hover:underline">Start Shopping</a>
            <?php else: ?>
                <div class="order-table w-full">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b">
                                <th class="py-3 px-4 text-sm font-medium text-gray-700">Order ID</th>
                                <th class="py-3 px-4 text-sm font-medium text-gray-700">Date</th>
                                <th class="py-3 px-4 text-sm font-medium text-gray-700">Items</th>
                                <th class="py-3 px-4 text-sm font-medium text-gray-700">Total</th>
                                <th class="py-3 px-4 text-sm font-medium text-gray-700">Status</th>
                                <th class="py-3 px-4 text-sm font-medium text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr class="border-b">
                                    <td class="py-3 px-4">#<?php echo $order['id']; ?></td>
                                    <td class="py-3 px-4"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td class="py-3 px-4"><?php echo $order['item_count']; ?></td>
                                    <td class="py-3 px-4">$<?php echo number_format($order['total'], 2); ?></td>
                                    <td class="py-3 px-4">
                                        <span class="inline-block px-2 py-1 text-xs font-medium rounded-full 
                                            <?php echo $order['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                      ($order['status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <button onclick="toggleDetails(<?php echo $order['id']; ?>)" 
                                                class="text-primary hover:underline text-sm">
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                                <tr class="order-details" id="details-<?php echo $order['id']; ?>">
                                    <td colspan="6" class="py-3 px-4">
                                        <?php
                                        $item_stmt = $conn->prepare("SELECT oi.*, p.name, p.image 
                                                                    FROM order_items oi 
                                                                    JOIN products p ON oi.product_id = p.id 
                                                                    WHERE oi.order_id = ?");
                                        $item_stmt->bind_param("i", $order['id']);
                                        $item_stmt->execute();
                                        $items = $item_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                        $item_stmt->close();
                                        ?>
                                        <div class="space-y-4">
                                            <h4 class="font-medium">Order Items</h4>
                                            <?php foreach ($items as $item): ?>
                                                <div class="flex items-center space-x-4">
                                                    <img src="<?php echo $item['image']; ?>" 
                                                         class="w-16 h-16 object-cover rounded" 
                                                         alt="<?php echo $item['name']; ?>">
                                                    <div>
                                                        <p class="font-medium"><?php echo $item['name']; ?></p>
                                                        <p class="text-gray-600">$<?php echo number_format($item['price'], 2); ?></p>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                            <div class="mt-4">
                                                <p><strong>Billing Name:</strong> <?php echo $order['billing_name']; ?></p>
                                                <p><strong>Address:</strong> <?php echo $order['billing_address']; ?>, 
                                                    <?php echo $order['billing_city']; ?>, <?php echo $order['billing_state']; ?>, 
                                                    <?php echo $order['billing_zip']; ?>, <?php echo $order['billing_country']; ?></p>
                                                <p><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
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

    <script>
    function toggleDetails(orderId) {
        const details = document.getElementById(`details-${orderId}`);
        details.classList.toggle('active');
    }
    </script>
</body>
</html>