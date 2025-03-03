<?php
// Include config file which contains database connection
require_once 'config.php';

// Fetch statistics for cards
$monthlyOrdersQuery = "SELECT SUM(total_amount) as monthly_total FROM orders WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
$yearlyOrdersQuery = "SELECT SUM(total_amount) as yearly_total FROM orders WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
$totalOrdersQuery = "SELECT SUM(total_amount) as all_time_total FROM orders";
$activeOrdersQuery = "SELECT COUNT(*) as active_count FROM orders WHERE status IN ('Függőben', 'Kiszállítás', 'Feldolgozva')";

// Previous period comparisons
$prevMonthOrdersQuery = "SELECT SUM(total_amount) as prev_month_total FROM orders 
                        WHERE order_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 2 MONTH) AND DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
$prevYearOrdersQuery = "SELECT SUM(total_amount) as prev_year_total FROM orders 
                       WHERE order_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 2 YEAR) AND DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
$prevWeekActiveOrdersQuery = "SELECT COUNT(*) as prev_week_active FROM orders 
                             WHERE status IN ('Függőben', 'Kiszállítás', 'Feldolgozva') 
                             AND order_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 2 WEEK) AND DATE_SUB(CURDATE(), INTERVAL 1 WEEK)";

// Execute queries
$monthlyResult = $conn->query($monthlyOrdersQuery)->fetch_assoc();
$yearlyResult = $conn->query($yearlyOrdersQuery)->fetch_assoc();
$totalResult = $conn->query($totalOrdersQuery)->fetch_assoc();
$activeResult = $conn->query($activeOrdersQuery)->fetch_assoc();

$prevMonthResult = $conn->query($prevMonthOrdersQuery)->fetch_assoc();
$prevYearResult = $conn->query($prevYearOrdersQuery)->fetch_assoc();
$prevWeekActiveResult = $conn->query($prevWeekActiveOrdersQuery)->fetch_assoc();

// Calculate percentage changes
$monthlyChange = 0;
if ($prevMonthResult['prev_month_total'] > 0) {
    $monthlyChange = (($monthlyResult['monthly_total'] - $prevMonthResult['prev_month_total']) / $prevMonthResult['prev_month_total']) * 100;
}

$yearlyChange = 0;
if ($prevYearResult['prev_year_total'] > 0) {
    $yearlyChange = (($yearlyResult['yearly_total'] - $prevYearResult['prev_year_total']) / $prevYearResult['prev_year_total']) * 100;
}

// Calculate total average annual growth
$averageGrowth = 5.7; // You may want to calculate this based on historical data

// Calculate active orders change
$activeChange = $activeResult['active_count'] - $prevWeekActiveResult['prev_week_active'];

// Format numbers for display
$monthlyTotal = number_format($monthlyResult['monthly_total'], 0, '.', ',');
$yearlyTotal = number_format($yearlyResult['yearly_total'], 0, '.', ',');
$allTimeTotal = number_format($totalResult['all_time_total'], 0, '.', ',');
$activeCount = $activeResult['active_count'];

// Chart data - Weekly revenue 
$revenueChartQuery = "SELECT 
                        DAYOFWEEK(order_date) as day_num, 
                        SUM(total_amount) as daily_total 
                      FROM orders 
                      WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK) 
                      GROUP BY DAYOFWEEK(order_date)
                      ORDER BY day_num";
$revenueResult = $conn->query($revenueChartQuery);

$weekDays = ['H', 'K', 'Sze', 'Cs', 'P', 'Szo', 'V'];
$revenueData = array_fill(0, 7, 0); // Initialize with zeros

while ($row = $revenueResult->fetch_assoc()) {
    // DAYOFWEEK returns 1 for Sunday, 2 for Monday, etc., but our array is 0-based
    $index = ($row['day_num'] + 5) % 7; // Convert to match our array (0 for Monday)
    $revenueData[$index] = (int)$row['daily_total'];
}

// Order types chart
$orderTypesQuery = "SELECT category, COUNT(*) as category_count FROM orders GROUP BY category";
$orderTypesResult = $conn->query($orderTypesQuery);

$categories = [];
$categoryCounts = [];

while ($row = $orderTypesResult->fetch_assoc()) {
    $categories[] = $row['category'];
    $categoryCounts[] = $row['category_count'];
}

// Recent orders
$recentOrdersQuery = "SELECT 
                        id, 
                        user_name, 
                        order_date, 
                        total_amount, 
                        status 
                      FROM orders 
                      ORDER BY order_date DESC 
                      LIMIT 5";
$recentOrdersResult = $conn->query($recentOrdersQuery);

// Define status classes for UI
$statusClasses = [
    'Feldolgozva' => 'bg-green-100 text-green-800',
    'Kiszállítás' => 'bg-blue-100 text-blue-800',
    'Függőben' => 'bg-yellow-100 text-yellow-800',
    'Teljesítve' => 'bg-green-100 text-green-800',
    'Visszavonva' => 'bg-red-100 text-red-800'
];
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <!-- Fontos: A legfrissebb Chart.js könyvtár teljes elérési úttal -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.bundle.min.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="darkmode.css">
  <script src="darkmode.js"></script>
</head>
<body class="bg-gray-100 <?php echo $darkMode ? 'dark-mode' : ''; ?>">
  <div class="flex flex-col md:flex-row min-h-screen">
    <!-- Sidebar -->
    <div class="w-full md:w-64 bg-gray-800 text-white flex flex-col md:h-screen">
      <div class="p-4 bg-gray-900">
        <h2 class="text-2xl font-bold">AdminPanel</h2>
      </div>
      <nav class="flex-grow p-4">
        <ul class="space-y-2">
          <li>
            <a href="#" class="flex items-center p-2 bg-blue-600 rounded text-white">
              <span class="mr-2">🏠</span>
              <span>Vezérlőpult</span>
            </a>
          </li>
          <li>
            <a href="#" class="flex items-center p-2 hover:bg-gray-700 rounded">
              <span class="mr-2">📦</span>
              <span>Rendelések</span>
            </a>
          </li>
          <li>
            <a href="#" class="flex items-center p-2 hover:bg-gray-700 rounded">
              <span class="mr-2">👥</span>
              <span>Ügyfelek</span>
            </a>
          </li>
          <li>
            <a href="#" class="flex items-center p-2 hover:bg-gray-700 rounded">
              <span class="mr-2">📊</span>
              <span>Jelentések</span>
            </a>
          </li>
          <li>
            <a href="#" class="flex items-center p-2 hover:bg-gray-700 rounded">
              <span class="mr-2">⚙️</span>
              <span>Beállítások</span>
            </a>
          </li>
        </ul>
      </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-grow">
      <header class="bg-white shadow p-4">
        <div class="flex justify-between items-center">
          <h1 class="text-xl font-bold">Vezérlőpult</h1>
          <div class="flex items-center">
            <span>Admin Felhasználó</span>
          </div>
        </div>
      </header>

      <main class="p-4">
        <!-- Filter Section -->
        <div class="bg-white rounded shadow p-4 mb-6">
          <div class="flex flex-wrap gap-4">
            <div class="w-full md:w-auto">
              <label class="block text-sm text-gray-600 mb-1">Dátum</label>
              <select class="w-full border p-2 rounded" id="dateFilter">
                <option value="7">Utolsó 7 nap</option>
                <option value="30">Utolsó 30 nap</option>
                <option value="month">Ez a hónap</option>
                <option value="year">Ez az év</option>
              </select>
            </div>
            <div class="w-full md:w-auto">
              <label class="block text-sm text-gray-600 mb-1">Kategória</label>
              <select class="w-full border p-2 rounded" id="categoryFilter">
                <option value="all">Összes kategória</option>
                <?php foreach ($categories as $category): ?>
                  <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="w-full md:w-auto md:ml-auto md:self-end">
              <button class="bg-blue-600 text-white p-2 rounded w-full" id="applyFilter">
                Szűrés alkalmazása
              </button>
            </div>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          <!-- Monthly Orders -->
          <div class="bg-white p-4 rounded shadow">
            <h3 class="text-gray-500 text-sm mb-1">Havi rendelés összeg</h3>
            <p class="text-2xl font-bold mb-2"><?php echo $monthlyTotal; ?> Ft</p>
            <p class="text-sm <?php echo $monthlyChange >= 0 ? 'text-green-500' : 'text-red-500'; ?>">
              <?php echo ($monthlyChange >= 0 ? '+' : '') . number_format($monthlyChange, 1); ?>% az előző hónaphoz képest
            </p>
          </div>
          
          <!-- Yearly Orders -->
          <div class="bg-white p-4 rounded shadow">
            <h3 class="text-gray-500 text-sm mb-1">Éves rendelés összeg</h3>
            <p class="text-2xl font-bold mb-2"><?php echo $yearlyTotal; ?> Ft</p>
            <p class="text-sm <?php echo $yearlyChange >= 0 ? 'text-green-500' : 'text-red-500'; ?>">
              <?php echo ($yearlyChange >= 0 ? '+' : '') . number_format($yearlyChange, 1); ?>% az előző évhez képest
            </p>
          </div>
          
          <!-- Total Orders -->
          <div class="bg-white p-4 rounded shadow">
            <h3 class="text-gray-500 text-sm mb-1">Teljes rendelés összeg</h3>
            <p class="text-2xl font-bold mb-2"><?php echo $allTimeTotal; ?> Ft</p>
            <p class="text-sm <?php echo $averageGrowth >= 0 ? 'text-green-500' : 'text-red-500'; ?>">
              <?php echo ($averageGrowth >= 0 ? '+' : '') . number_format($averageGrowth, 1); ?>% összesen
            </p>
          </div>
          
          <!-- Active Orders -->
          <div class="bg-white p-4 rounded shadow">
            <h3 class="text-gray-500 text-sm mb-1">Aktív rendelések</h3>
            <p class="text-2xl font-bold mb-2"><?php echo $activeCount; ?></p>
            <p class="text-sm <?php echo $activeChange >= 0 ? 'text-green-500' : 'text-red-500'; ?>">
              <?php echo ($activeChange >= 0 ? '+' : '') . $activeChange; ?> az előző héthez képest
            </p>
          </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
          <!-- Revenue Chart -->
          <div class="bg-white p-4 rounded shadow lg:col-span-2">
            <div class="flex justify-between items-center mb-4">
              <h3 class="font-bold">Bevételek alakulása</h3>
              <div class="flex space-x-2">
                <button class="px-2 py-1 text-sm bg-gray-200 rounded period-btn" data-period="daily">Napi</button>
                <button class="px-2 py-1 text-sm bg-blue-600 text-white rounded period-btn" data-period="weekly">Heti</button>
                <button class="px-2 py-1 text-sm bg-gray-200 rounded period-btn" data-period="monthly">Havi</button>
              </div>
            </div>
            <div style="height: 300px;">
              <canvas id="revenueChart"></canvas>
            </div>
          </div>
          
          <!-- Order Types Chart -->
          <div class="bg-white p-4 rounded shadow">
            <h3 class="font-bold mb-4">Rendelés típusok</h3>
            <div style="height: 300px;">
              <canvas id="orderTypesChart"></canvas>
            </div>
          </div>
        </div>

        <!-- Recent Orders -->
        <div class="bg-white rounded shadow mb-6">
          <div class="flex justify-between items-center p-4 border-b">
            <h3 class="font-bold">Legutóbbi rendelések</h3>
            <a href="#" class="text-blue-600">Összes megtekintése</a>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full">
              <thead>
                <tr class="bg-gray-50">
                  <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Azonosító</th>
                  <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ügyfél</th>
                  <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dátum</th>
                  <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Összeg</th>
                  <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Állapot</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <?php while ($order = $recentOrdersResult->fetch_assoc()): ?>
                <tr>
                  <td class="p-3 text-sm">#R-<?php echo htmlspecialchars($order['id']); ?></td>
                  <td class="p-3 text-sm"><?php echo htmlspecialchars($order['user_name']); ?></td>
                  <td class="p-3 text-sm"><?php echo date('Y.m.d', strtotime($order['order_date'])); ?></td>
                  <td class="p-3 text-sm"><?php echo number_format($order['total_amount'], 0, '.', ','); ?> Ft</td>
                  <td class="p-3 text-sm">
                    <span class="px-2 py-1 text-xs rounded-full <?php echo $statusClasses[$order['status']] ?? 'bg-gray-100 text-gray-800'; ?>">
                      <?php echo htmlspecialchars($order['status']); ?>
                    </span>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script>
    // Chart.js inicializálása - ellenőrizzük, hogy betöltődött-e a könyvtár
    window.onload = function() {
      // Csak akkor folytassuk, ha a Chart objektum elérhető
      if (typeof Chart === 'undefined') {
        console.error('Chart.js könyvtár nem töltődött be! Ellenőrizd a script tag-et és az URL-t.');
        alert('A grafikonok nem jeleníthetők meg, mert a Chart.js könyvtár nem töltődött be.');
        return;
      }
      
      console.log('Chart.js könyvtár sikeresen betöltve');
      
      // Chart colors
      const chartColors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'];
      
      // Revenue chart data from PHP
      const revenueData = <?php echo json_encode($revenueData); ?>;
      const weekLabels = <?php echo json_encode($weekDays); ?>;
      
      // Categories from PHP
      const categoryLabels = <?php echo json_encode($categories); ?>;
      const categoryData = <?php echo json_encode($categoryCounts); ?>;
      
      // Revenue chart
      try {
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
          type: 'line',
          data: {
            labels: weekLabels,
            datasets: [{
              label: 'Bevétel (Ft)',
              data: revenueData,
              backgroundColor: 'rgba(59, 130, 246, 0.2)',
              borderColor: 'rgba(59, 130, 246, 1)',
              borderWidth: 2,
              pointBackgroundColor: 'rgba(59, 130, 246, 1)',
              tension: 0.3
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
              yAxes: [{
                ticks: {
                  beginAtZero: true
                }
              }]
            }
          }
        });
        console.log('Revenue chart létrehozva');
      } catch (err) {
        console.error('Hiba a bevételi grafikon létrehozásakor:', err);
      }

      // Order types chart
      try {
        const orderTypesCtx = document.getElementById('orderTypesChart').getContext('2d');
        const orderTypesChart = new Chart(orderTypesCtx, {
          type: 'doughnut',
          data: {
            labels: categoryLabels,
            datasets: [{
              data: categoryData,
              backgroundColor: chartColors,
              borderWidth: 0
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false
          }
        });
        console.log('Order types chart létrehozva');
      } catch (err) {
        console.error('Hiba a rendeléstípus grafikon létrehozásakor:', err);
      }
      
      // Period buttons click handler
      document.querySelectorAll('.period-btn').forEach(button => {
        button.addEventListener('click', function() {
          // Remove active class from all buttons
          document.querySelectorAll('.period-btn').forEach(btn => {
            btn.classList.remove('bg-blue-600', 'text-white');
            btn.classList.add('bg-gray-200');
          });
          
          // Add active class to clicked button
          this.classList.remove('bg-gray-200');
          this.classList.add('bg-blue-600', 'text-white');
          
          // Here you would fetch new data based on the period
          // For now we'll just simulate by showing a message
          const period = this.getAttribute('data-period');
          console.log(`Fetching data for ${period} period`);
        });
      });
      
      // Filter apply button
      document.getElementById('applyFilter').addEventListener('click', function() {
        const dateFilter = document.getElementById('dateFilter').value;
        const categoryFilter = document.getElementById('categoryFilter').value;
        
        // Here you would submit the form or make an AJAX request
        console.log(`Applying filters: date=${dateFilter}, category=${categoryFilter}`);
        
        // For a quick prototype, you could reload the page with query parameters
        window.location.href = `?date=${dateFilter}&category=${categoryFilter}`;
      });
    };
  </script>
</body>
</html>