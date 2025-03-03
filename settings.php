<?php
// Munkamenet és hitelesítés ellenőrzése
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Adatbázis kapcsolat létrehozása
require_once 'config.php';
require_once 'auth.php';

// Felhasználói adatok lekérése az adatbázisból
$userId = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

// Alapértelmezett beállítások
$defaultSettings = [
    'dark_mode' => 0,
    'notifications' => 1,
    'newsletter' => 1,
    'language' => 'hu',
    'currency' => 'HUF',
    'items_per_page' => 12
];

// Beállítások mentése
$messageType = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form adatok feldolgozása
    $darkMode = isset($_POST['dark_mode']) ? 1 : 0;
    $notifications = isset($_POST['notifications']) ? 1 : 0;
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;
    $language = isset($_POST['language']) ? $_POST['language'] : 'hu';
    $currency = isset($_POST['currency']) ? $_POST['currency'] : 'HUF';
    $itemsPerPage = isset($_POST['items_per_page']) ? intval($_POST['items_per_page']) : 12;
    
    // Validálás
    if ($itemsPerPage < 4 || $itemsPerPage > 48) {
        $itemsPerPage = 12;
    }
    
    // Beállítások összeállítása
    $settings = [
        'dark_mode' => $darkMode,
        'notifications' => $notifications,
        'newsletter' => $newsletter,
        'language' => $language,
        'currency' => $currency,
        'items_per_page' => $itemsPerPage
    ];
    
    // Beállítások konvertálása pontosvesszővel elválasztott formátumba
    $settingsString = '';
    foreach ($settings as $key => $value) {
        $settingsString .= $key . '=' . $value . ';';
    }
    $settingsString = rtrim($settingsString, ';');
    
    // Beállítások mentése az adatbázisba
    $updateQuery = "UPDATE users SET settings = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("si", $settingsString, $userId);
    
    if ($updateStmt->execute()) {
        $messageType = 'success';
        $message = 'A beállítások sikeresen mentve!';
        
        // Frissítjük a session-t is
        $_SESSION['settings'] = $settings;
        
        // Cookie beállítása a dark mode-hoz (30 napig érvényes)
        setcookie('dark_mode', $darkMode, time() + (30 * 24 * 60 * 60), '/');
    } else {
        $messageType = 'error';
        $message = 'Hiba történt a beállítások mentése közben!';
    }
}

// Felhasználó adatainak és beállításainak lekérdezése
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Beállítások feldolgozása
$userSettings = $defaultSettings;
if (!empty($user['settings'])) {
    $settingsParts = explode(';', $user['settings']);
    foreach ($settingsParts as $part) {
        if (strpos($part, '=') !== false) {
            list($key, $value) = explode('=', $part);
            if (array_key_exists($key, $userSettings)) {
                $userSettings[$key] = $value;
            }
        }
    }
}

$monogram = generateMonogram($user['name']);

// Beállítások mentése a munkamenetbe
$_SESSION['settings'] = $userSettings;

// Cookie beállítása, ha még nem létezik
if (!isset($_COOKIE['dark_mode']) && isset($userSettings['dark_mode'])) {
    setcookie('dark_mode', $userSettings['dark_mode'], time() + (30 * 24 * 60 * 60), '/');
}

$darkMode = isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === '1';

// Ha van bejelentkezett felhasználó és vannak beállításai, akkor onnan is lekérhetjük
if (isset($_SESSION['user_id']) && isset($userSettings['dark_mode'])) {
    $darkMode = $userSettings['dark_mode'] == 1;
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beállítások</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="darkmode.css">
    <script src="darkmode.js"></script>
</head>
<body class="bg-gray-100 min-h-screen <?php echo $darkMode ? 'dark-mode' : ''; ?>">
    
    <div class="container mx-auto px-4 py-8">
        <!-- Értesítés doboz -->
        <?php if (!empty($message)): ?>
            <div id="notification" class="mb-4 p-4 rounded <?php echo $messageType === 'success' ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100'; ?>">
                <p><?php echo $message; ?></p>
            </div>
            <script>
                // 5 másodperc után elrejtjük
                setTimeout(function() {
                    document.getElementById('notification').style.display = 'none';
                }, 5000);
            </script>
        <?php endif; ?>
        
        <div class="flex flex-col md:flex-row gap-6">
            <!-- Sidebar Menu -->
            <div class="w-full md:w-64 bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-center md:justify-start mb-8">
                    <div class="h-16 w-16 rounded-full bg-blue-500 flex items-center justify-center">
                        <span class="text-2xl font-bold text-white"><?php echo htmlspecialchars($monogram); ?></span>
                    </div>
                    <div class="ml-4">
                        <h2 class="font-bold text-xl"><?php echo htmlspecialchars($user['name'] ?? ''); ?></h2>
                        <p class="text-gray-500 text-sm"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                    </div>
                </div>
                
                <nav>
                    <ul class="space-y-2">
                        <li>
                            <a href="profile.php" class="flex items-center p-3 text-gray-700 hover:bg-gray-50 rounded-lg font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Profilom
                            </a>
                        </li>
                        <li>
                            <a href="orders.php" class="flex items-center p-3 text-gray-700 hover:bg-gray-50 rounded-lg font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Rendeléseim
                            </a>
                        </li>
                        <li>
                            <a href="mycoupons.php" class="flex items-center p-3 text-gray-700 hover:bg-gray-50 rounded-lg font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                                </svg>
                                Kuponjaim
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center p-3 text-gray-700 hover:bg-gray-50 rounded-lg font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Kívánságlistám
                            </a>
                        </li>
                        <li>
                            <a href="settings.php" class="flex items-center p-3 text-blue-600 bg-blue-50 rounded-lg font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Beállítások
                            </a>
                        </li>
                        <li>
                            <a href="index.php" class="flex items-center p-3 text-red-600 hover:bg-red-50 dark:hover:bg-red-900 dark:text-red-400 rounded-lg font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Kilépés
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            
            <!-- Fő tartalom -->
            <div class="flex-1 bg-white rounded-lg shadow-md p-6">
                <h1 class="text-2xl font-bold mb-6">Beállítások</h1>
                
                <form method="POST" action="settings.php">
                    <!-- Felhasználói felület beállítások -->
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold mb-3 pb-2 border-b">Felhasználói felület</h2>
                        
                        <div class="flex items-center justify-between mb-4">
                            <label for="notifications" class="cursor-pointer">Sötét mód</label>
                            <label class="dark-mode-toggle">
                                <input id="dark-mode-toggle" type="checkbox" id="dark_mode" name="dark_mode" <?php echo $userSettings['dark_mode'] == 1 ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </div>
                        
                        <div class="mb-4">
                            <label for="language" class="block text-gray-700 text-sm font-bold mb-2">Nyelv</label>
                            <select id="language" name="language" class="shadow appearance-none border rounded w-full md:w-1/3 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="hu" <?php echo $userSettings['language'] == 'hu' ? 'selected' : ''; ?>>Magyar</option>
                                <option value="en" <?php echo $userSettings['language'] == 'en' ? 'selected' : ''; ?>>English</option>
                                <option value="de" <?php echo $userSettings['language'] == 'de' ? 'selected' : ''; ?>>Deutsch</option>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label for="items_per_page" class="block text-gray-700 text-sm font-bold mb-2">Termékek száma oldalanként</label>
                            <select id="items_per_page" name="items_per_page" class="shadow appearance-none border rounded w-full md:w-1/3 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="8" <?php echo $userSettings['items_per_page'] == 8 ? 'selected' : ''; ?>>8</option>
                                <option value="12" <?php echo $userSettings['items_per_page'] == 12 ? 'selected' : ''; ?>>12</option>
                                <option value="24" <?php echo $userSettings['items_per_page'] == 24 ? 'selected' : ''; ?>>24</option>
                                <option value="36" <?php echo $userSettings['items_per_page'] == 36 ? 'selected' : ''; ?>>36</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Vásárlási beállítások -->
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold mb-3 pb-2 border-b">Vásárlási beállítások</h2>
                        
                        <div class="mb-4">
                            <label for="currency" class="block text-gray-700 text-sm font-bold mb-2">Pénznem</label>
                            <select id="currency" name="currency" class="shadow appearance-none border rounded w-full md:w-1/3 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="HUF" <?php echo $userSettings['currency'] == 'HUF' ? 'selected' : ''; ?>>Forint (HUF)</option>
                                <option value="EUR" <?php echo $userSettings['currency'] == 'EUR' ? 'selected' : ''; ?>>Euro (EUR)</option>
                                <option value="USD" <?php echo $userSettings['currency'] == 'USD' ? 'selected' : ''; ?>>Dollár (USD)</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Értesítési beállítások -->
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold mb-3 pb-2 border-b">Értesítési beállítások</h2>
                        
                        <div class="flex items-center justify-between mb-4">
                            <label for="notifications" class="cursor-pointer">Értesítések engedélyezése</label>
                            <label class="dark-mode-toggle">
                                <input type="checkbox" id="notifications" name="notifications" <?php echo $userSettings['notifications'] == 1 ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-between mb-4">
                            <label for="newsletter" class="cursor-pointer">Hírlevél feliratkozás</label>
                            <label class="dark-mode-toggle">
                                <input type="checkbox" id="newsletter" name="newsletter" <?php echo $userSettings['newsletter'] == 1 ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Mentés gomb -->
                    <div class="mt-6">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Beállítások mentése
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Dark mode kapcsoló valós idejű előnézete
    document.getElementById('dark_mode').addEventListener('change', function() {
        document.body.classList.toggle('dark-mode', this.checked);
    });
    </script>
    <script src="script.js"></script>
</body>
</html>