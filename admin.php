<?php
session_start();
require_once 'config.php';

// Check if user is admin
$stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!isset($_SESSION['user_id']) || !$user['is_admin']) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Webshop</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Admin Panel</h1>
        
        <!-- Add New Product Form -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-2xl font-bold mb-4">Új termék hozzáadása</h2>
            <form action="process_product.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Termék neve</label>
                    <input type="text" name="name" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Leírás</label>
                    <textarea name="description" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 h-32"></textarea>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Ár</label>
                    <input type="number" step="0.01" name="price" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Készlet</label>
                    <input type="number" name="stock" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Méretek (vesszővel elválasztva)</label>
                    <input type="text" name="sizes" placeholder="S,M,L,XL" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Termékfotó</label>
                    <input type="file" name="image" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                </div>
                <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Termék hozzáadása</button>
            </form>
        </div>
        
        <!-- Orders List -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold mb-4">Recent Orders</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="px-4 py-2">Order ID</th>
                            <th class="px-4 py-2">User</th>
                            <th class="px-4 py-2">Date</th>
                            <th class="px-4 py-2">Total</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY order_date DESC";
                        $result = $conn->query($query);
                        while ($order = $result->fetch_assoc()):
                        ?>
                        <tr>
                            <td class="border px-4 py-2"><?php echo $order['id']; ?></td>
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($order['username']); ?></td>
                            <td class="border px-4 py-2"><?php echo $order['order_date']; ?></td>
                            <td class="border px-4 py-2">$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td class="border px-4 py-2"><?php echo ucfirst($order['status']); ?></td>
                            <td class="border px-4 py-2">
                                <button onclick="viewOrder(<?php echo $order['id']; ?>)" 
                                        class="bg-blue-500 text-white py-1 px-2 rounded hover:bg-blue-600">
                                    View Details
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function viewOrder(orderId) {
            // Implement order details view
            alert('View order details for Order #' + orderId);
        }
    </script>
</body>
</html>