<?php
    session_start(); // Biztosítjuk, hogy a session el van indítva
    include 'config.php';

    $is_logged = isset($_SESSION['is_logged']) ? $_SESSION['is_logged'] : false;
    $is_admin = isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : 0; // Admin jogosultság ellenőrzése
?>
<!DOCTYPE html>
<html lang="en">
<!-- A head rész változatlan marad -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webshop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="darkmode.css">
    <script src="darkmode.js"></script>
</head>
<body class="bg-gray-100 <?php echo $darkMode ? 'dark-mode' : ''; ?>">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex space-x-7">
                    <div class="hidden md:flex items-center space-x-4">
                        <a href="#" class="py-4 px-2 text-gray-500 font-semibold hover:text-blue-500 transition duration-300">Kezdőlap</a>
                        <a href="#" class="py-4 px-2 text-gray-500 font-semibold hover:text-blue-500 transition duration-300">Termékek</a>
                        <a href="#" class="py-4 px-2 text-gray-500 font-semibold hover:text-blue-500 transition duration-300">Kategóriák</a>
                    </div>
                </div>
                
                <!-- Logo -->
                <div class="flex items-center">
                    <img src="logo.png" alt="Logo" class="h-8 w-auto">
                </div>
                
                 <!-- Right Side Menu -->
                <div class="hidden md:flex items-center space-x-4">
                    <?php if (!$is_logged): ?>
                        <a href="login.php" class="py-2 px-2 font-medium text-gray-500 rounded hover:bg-blue-500 hover:text-white transition duration-300">Bejelentkezés</a>
                        <a href="register.php" class="py-2 px-2 font-medium text-white bg-blue-500 rounded hover:bg-blue-400 transition duration-300">Regisztráció</a>
                    <?php else: ?>
                        <?php if ($is_admin == 1): ?>
                        <!-- Admin ikon megjelenítése, ha az is_admin = 1 -->
                        <a href="admin.php" class="text-gray-500 hover:text-blue-500 transition duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </a>
                        <?php endif; ?>
                        <a href="profile.php" class="text-gray-500 hover:text-blue-500 transition duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </a>
                        <a href="logout.php" class="text-gray-500 hover:text-blue-500 transition duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </a>
                    <?php endif; ?>
                    <a href="cart.php" class="relative">
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-4 h-4 text-xs flex items-center justify-center">0</span>
                        <svg class="w-6 h-6 text-gray-500 hover:text-blue-500 transition duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </a>
                </div>
                
                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button class="outline-none mobile-menu-button">
                        <svg class="w-6 h-6 text-gray-500 hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div class="hidden mobile-menu md:hidden">
            <ul class="px-2 py-3 space-y-2">
                <li><a href="#" class="block px-2 py-2 text-gray-500 hover:bg-blue-500 hover:text-white rounded transition duration-300">Home</a></li>
                <li><a href="#" class="block px-2 py-2 text-gray-500 hover:bg-blue-500 hover:text-white rounded transition duration-300">Products</a></li>
                <li><a href="#" class="block px-2 py-2 text-gray-500 hover:bg-blue-500 hover:text-white rounded transition duration-300">Categories</a></li>
                <li><a href="#" class="block px-2 py-2 text-gray-500 hover:bg-blue-500 hover:text-white rounded transition duration-300">Login</a></li>
                <li><a href="#" class="block px-2 py-2 text-gray-500 hover:bg-blue-500 hover:text-white rounded transition duration-300">Register</a></li>
            </ul>
        </div>
    </nav>

    <!-- Fortune Wheel Popup -->
    <div id="fortuneModal" class="modal">
        <div class="modal-content">
            <h1 class="text-2xl font-bold text-center">Pörgess és nyerj!</h1>
            <span class="close-button" id="closeModal">&times;</span>
            <br>
            
            <div class="flex flex-col md:flex-row justify-between items-center">
                <!-- Wheel of Fortune -->
                <fieldset class="ui-wheel-of-fortune md:w-1/2">
                    <ul>
                        <li>-15%</li>
                        <li>-10%</li>
                        <li>-30%</li>
                        <li>Ajándék termék</li>
                        <li>Pörgess újra</li>
                        <li>Ingyenes szállítás</li>
                    </ul>
                    <button type="button" id="spinButton" <?php echo (!$is_logged) ? 'disabled' : 'data-logged-in="true"'; ?>>SPIN</button>
                </fieldset>
                
                <!-- Text next to the wheel -->
                <div class="md:w-1/2 mt-6 md:mt-0 md:ml-6">
                    <?php if (!$is_logged): ?>
                    <!-- Login Required Message -->
                    <div class="login-message p-4 border rounded bg-gray-100">
                        <p class="text-lg font-medium mb-2">A pörgetéshez előbb jelentkezzen be!</p>
                        <a href="login.php" class="login-button bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-400 inline-block">Bejelentkezés</a>
                    </div>
                    <?php else: ?>
                    <p class="text-lg font-medium mb-2" id="couponCodeText">Kuponkód:</p>
                    <div class="p-4 border rounded bg-gray-100">
                       <p id="couponCode"></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <!-- Sample Product Card -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <img src="product1.jpg" alt="Product" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="text-xl font-semibold mb-2">Product Name</h3>
                    <p class="text-gray-600 mb-4">Short product description goes here.</p>
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold">$99.99</span>
                        <button class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-400 transition duration-300">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>