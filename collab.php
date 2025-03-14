<?php
session_start();
require_once 'config.php'; // Database connection
require_once 'products.php'; // Product functions

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get collaborated products
$user_id = $_SESSION['user_id'];
$query = "SELECT p.*, u.username 
          FROM products p 
          JOIN collaborations c ON p.id = c.product_id 
          JOIN users u ON p.user_id = u.id 
          WHERE c.collaborator_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$collaborated_products = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collaborations - Invialuxe</title>
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
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
        }
        .video-carousel {
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
        }
        .video-slide {
            scroll-snap-align: start;
        }
        @media (max-width: 640px) {
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 1rem;
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
                <div class="flex-1 max-w-2xl mx-4 sm:mx-8">
                    <form method="GET" action="index.php" class="relative">
                        <input type="text" name="search" placeholder="Search for jewelry..." 
                               class="w-full px-4 py-2 pl-10 pr-4 rounded-full border border-gray-200 focus:outline-none focus:border-primary text-sm">
                        <button type="submit" class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 flex items-center justify-center">
                            <i class="ri-search-line text-gray-400"></i>
                        </button>
                    </form>
                </div>
                <div class="flex items-center space-x-4 sm:space-x-6">
                    <a href="index.php" class="w-10 h-10 flex items-center justify-center" title="Home">
                        <i class="ri-home-line text-xl"></i>
                    </a>
                    <a href="?logout=1" class="w-10 h-10 flex items-center justify-center" title="Logout">
                        <i class="ri-logout-box-line text-xl"></i>
                    </a>
                    <a href="cart.php" class="w-10 h-10 flex items-center justify-center relative">
                        <i class="ri-shopping-bag-line text-xl"></i>
                        <span id="cart-counter" class="absolute top-0 right-0 w-5 h-5 bg-primary text-white rounded-full text-xs flex items-center justify-center">
                            <?php echo count($_SESSION['cart'] ?? []); ?>
                        </span>
                    </a>
                </div>
            </div>
            <nav class="flex flex-wrap gap-4 sm:gap-8 py-3">
                <a href="index.php?category=Necklaces" class="text-gray-700 hover:text-primary text-sm font-medium">Necklaces</a>
                <a href="index.php?category=Earrings" class="text-gray-700 hover:text-primary text-sm font-medium">Earrings</a>
                <a href="index.php?category=Bracelets" class="text-gray-700 hover:text-primary text-sm font-medium">Bracelets</a>
                <a href="index.php?category=Rings" class="text-gray-700 hover:text-primary text-sm font-medium">Rings</a>
                <a href="index.php" class="text-gray-700 hover:text-primary text-sm font-medium">All</a>
            </nav>
        </div>
    </header>

    <main class="mt-32 px-4 sm:px-6 lg:px-8">
        <section class="py-16 max-w-7xl mx-auto">
            <h2 class="text-3xl font-['Playfair_Display'] font-bold text-center mb-8">Your Collaborations</h2>
            
            <?php if (empty($collaborated_products)): ?>
                <p class="text-gray-600 text-center">You haven't collaborated on any items yet.</p>
                <a href="index.php" class="block mt-4 text-center text-primary hover:underline">Start Collaborating</a>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($collaborated_products as $product): ?>
                    <div class="group">
                        <div class="relative rounded-lg overflow-hidden mb-4">
                            <img src="<?php echo $product['image']; ?>" 
                                 class="w-full h-72 object-cover" alt="<?php echo $product['name']; ?>">
                            <button class="absolute bottom-4 left-1/2 -translate-x-1/2 bg-white text-gray-900 px-6 py-2 rounded-button opacity-0 group-hover:opacity-100 transition-opacity">
                                Quick View
                            </button>
                        </div>
                        <h4 class="font-medium mb-2"><?php echo $product['name']; ?></h4>
                        <p class="text-gray-600 mb-2">$<?php echo number_format($product['price'], 2); ?></p>
                        <p class="text-gray-500 text-sm mb-2">Created by: <?php echo $product['username']; ?></p>
                        
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'expert'): ?>
                            <div class="video-carousel flex overflow-x-auto space-x-4 mb-2">
                                <?php 
                                $videos = getVideos($product['id']);
                                foreach ($videos as $video): ?>
                                    <video class="video-slide w-48 h-32 object-cover" controls>
                                        <source src="<?php echo $video['video_url']; ?>" type="video/mp4">
                                    </video>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="index.php">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" name="add_to_cart" 
                                    class="w-full bg-primary text-white py-2 rounded-button hover:bg-primary/90 transition-colors">
                                Add to Cart
                            </button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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