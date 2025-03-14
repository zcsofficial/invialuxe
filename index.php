<?php
session_start();
require_once 'config.php';
require_once 'products.php';

// Simple cart management
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add to cart handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    if (!in_array($product_id, $_SESSION['cart'])) {
        $_SESSION['cart'][] = $product_id;
    }
}

// Logout handler
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Product filtering and sorting
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';

$products_query = "SELECT p.*, u.username FROM products p JOIN users u ON p.user_id = u.id WHERE 1=1";
$params = [];
$types = "";

if ($search) {
    $products_query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if ($category) {
    $products_query .= " AND p.category = ?";
    $params[] = $category;
    $types .= "s";
}

switch ($sort) {
    case 'price_asc':
        $products_query .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $products_query .= " ORDER BY p.price DESC";
        break;
    case 'name_desc':
        $products_query .= " ORDER BY p.name DESC";
        break;
    default:
        $products_query .= " ORDER BY p.name ASC";
}

$stmt = $conn->prepare($products_query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invialuxe - Luxury Jewelry Platform</title>
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
            .filters {
                flex-direction: column;
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
                    <form method="GET" class="relative">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Search for jewelry..." 
                               class="w-full px-4 py-2 pl-10 pr-4 rounded-full border border-gray-200 focus:outline-none focus:border-primary text-sm">
                        <button type="submit" class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 flex items-center justify-center">
                            <i class="ri-search-line text-gray-400"></i>
                        </button>
                    </form>
                </div>
                <div class="flex items-center space-x-4 sm:space-x-6">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="?logout=1" class="w-10 h-10 flex items-center justify-center" title="Logout">
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
                            <?php echo count($_SESSION['cart']); ?>
                        </span>
                    </a>
                </div>
            </div>
            <nav class="flex flex-wrap gap-4 sm:gap-8 py-3">
                <a href="?category=Necklaces" class="text-gray-700 hover:text-primary text-sm font-medium <?php echo $category === 'Necklaces' ? 'text-primary' : ''; ?>">Necklaces</a>
                <a href="?category=Earrings" class="text-gray-700 hover:text-primary text-sm font-medium <?php echo $category === 'Earrings' ? 'text-primary' : ''; ?>">Earrings</a>
                <a href="?category=Bracelets" class="text-gray-700 hover:text-primary text-sm font-medium <?php echo $category === 'Bracelets' ? 'text-primary' : ''; ?>">Bracelets</a>
                <a href="?category=Rings" class="text-gray-700 hover:text-primary text-sm font-medium <?php echo $category === 'Rings' ? 'text-primary' : ''; ?>">Rings</a>
                <a href="index.php" class="text-gray-700 hover:text-primary text-sm font-medium">All</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="collab.php" class="text-gray-700 hover:text-primary text-sm font-medium">Collaborations</a>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="orders.php" class="text-gray-700 hover:text-primary text-sm font-medium">Orders</a>
                <?php endif; ?>
                <?php if (isset($_SESSION['admin_logged_in'])): ?>
                    <a href="admin.php" class="text-gray-700 hover:text-primary text-sm font-medium">Admin</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="mt-32 px-4 sm:px-6 lg:px-8">
        <section class="relative h-[400px] sm:h-[600px] overflow-hidden">
            <div class="absolute inset-0">
                <img src="https://public.readdy.ai/ai/img_res/add14925853e2cc91d644c9b2c40e3b1.jpg" 
                     class="w-full h-full object-cover object-center" alt="Luxury Jewelry Collection">
                <div class="absolute inset-0 bg-gradient-to-r from-black/40 to-transparent"></div>
            </div>
            <div class="relative max-w-7xl mx-auto h-full flex items-center">
                <div class="max-w-xl text-white px-4">
                    <h2 class="text-3xl sm:text-5xl font-['Playfair_Display'] font-bold mb-6">Welcome to Invialuxe</h2>
                    <p class="text-base sm:text-lg mb-8">Collaborate and Discover Unique Jewelry Creations</p>
                    <button class="bg-primary text-white px-6 sm:px-8 py-2 sm:py-3 rounded-button font-medium hover:bg-primary/90 transition-colors">
                        Explore Now
                    </button>
                </div>
            </div>
        </section>

        <section class="py-16 max-w-7xl mx-auto">
            <h3 class="text-3xl font-['Playfair_Display'] font-bold text-center mb-8">Featured Collections</h3>
            
            <div class="filters flex flex-wrap items-center justify-between mb-8 gap-4">
                <div class="flex items-center gap-2">
                    <label for="sort" class="text-sm font-medium text-gray-700">Sort by:</label>
                    <select id="sort" name="sort" onchange="this.form.submit()" 
                            form="filterForm" 
                            class="px-2 py-1 border rounded-button text-sm focus:outline-none focus:border-primary">
                        <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                        <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                        <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Price (Low to High)</option>
                        <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Price (High to Low)</option>
                    </select>
                </div>
                <form id="filterForm" method="GET" class="flex items-center gap-2">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                </form>
            </div>

            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                <div class="group">
                    <a href="product.php?id=<?php echo $product['id']; ?>" class="block">
                        <div class="relative rounded-lg overflow-hidden mb-4">
                            <img src="<?php echo $product['image']; ?>" 
                                 class="w-full h-72 object-cover" alt="<?php echo $product['name']; ?>">
                            <button class="absolute bottom-4 left-1/2 -translate-x-1/2 bg-white text-gray-900 px-6 py-2 rounded-button opacity-0 group-hover:opacity-100 transition-opacity">
                                Quick View
                            </button>
                        </div>
                        <h4 class="font-medium mb-2"><?php echo $product['name']; ?></h4>
                        <p class="text-gray-600 mb-2">$<?php echo number_format($product['price'], 2); ?></p>
                    </a>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <form method="POST" class="mb-2">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <div class="flex gap-2">
                            <input type="text" name="collaborator" placeholder="Username/Email" 
                                   class="px-2 py-1 border rounded w-full text-sm">
                            <button type="submit" name="collaborate" 
                                    class="bg-primary text-white px-2 py-1 rounded text-sm whitespace-nowrap">
                                Collaborate
                            </button>
                        </div>
                    </form>

                    <form method="POST" class="mb-2">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <div class="flex gap-2">
                            <select name="rating" class="border rounded px-2 py-1 text-sm">
                                <option value="5">5</option>
                                <option value="4">4</option>
                                <option value="3">3</option>
                                <option value="2">2</option>
                                <option value="1">1</option>
                            </select>
                            <input type="text" name="comment" placeholder="Add review" 
                                   class="px-2 py-1 border rounded flex-1 text-sm">
                            <button type="submit" name="review" 
                                    class="bg-primary text-white px-2 py-1 rounded text-sm">
                                Submit
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>

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

                    <form method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <button type="submit" name="add_to_cart" 
                                class="w-full bg-primary text-white py-2 rounded-button hover:bg-primary/90 transition-colors">
                            Add to Cart
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
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

    <script>
    // Real-time cart counter update
    document.querySelectorAll('[name="add_to_cart"]').forEach(button => {
        button.addEventListener('click', function(e) {
            setTimeout(() => {
                const counter = document.getElementById('cart-counter');
                counter.textContent = parseInt(counter.textContent) + 1;
            }, 100);
        });
    });
    </script>

    <?php
    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['collaborate']) && isset($_SESSION['user_id'])) {
            $result = addCollaboration($_POST['product_id'], $_POST['collaborator']);
            echo $result ? '<script>alert("Collaboration added successfully!");</script>' : 
                          '<script>alert("Collaboration failed or user not found!");</script>';
        }
        if (isset($_POST['review']) && isset($_SESSION['user_id'])) {
            $result = addReview($_POST['product_id'], $_POST['rating'], $_POST['comment']);
            echo $result ? '<script>alert("Review submitted successfully!");</script>' : 
                          '<script>alert("Review submission failed!");</script>';
        }
    }
    ?>
</body>
</html>