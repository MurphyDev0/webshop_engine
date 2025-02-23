<?php
    include 'config.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webshop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-gray-100">
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
                    <a href="login.php" class="py-2 px-2 font-medium text-gray-500 rounded hover:bg-blue-500 hover:text-white transition duration-300">Bejelentkezés</a>
                    <a href="register.php" class="py-2 px-2 font-medium text-white bg-blue-500 rounded hover:bg-blue-400 transition duration-300">Regisztráció</a>
                    <div class="relative">
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-4 h-4 text-xs flex items-center justify-center">0</span>
                        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
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

    <!-- Wheel of Fortune Modal -->
    <div class="wheel-container">
        <div class="wheel">
            <div class="wheel-inner"></div>
        </div>
        <button id="spin-btn">Pörgetés</button>
        <div id="result"></div>
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