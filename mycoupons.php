<?php
// Munkamenet és hitelesítés ellenőrzése
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Adatbázis kapcsolat létrehozása
require_once 'config.php';

// Felhasználói adatok lekérése az adatbázisból
$userId = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "Felhasználó nem található!";
    exit();
}


// Kuponok lekérdezése az új adatbázis struktúra alapján
$query = "SELECT id, code, is_active, type, created_at 
          FROM coupons 
          WHERE user_id = ? 
          ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$kuponok = $result->fetch_all(MYSQLI_ASSOC);

$monogram = generateMonogram($user['name']);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuponjaim</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="darkmode.css">
    <script src="darkmode.js"></script>
</head>
<body class="bg-gray-100 <?php echo $userSettings['dark_mode'] == 1 ? 'dark-mode' : ''; ?>">
    <div class="container mx-auto px-4 py-8">
        <!-- Értesítés doboz -->
        <div id="notification" class="hidden mb-4 p-4 rounded"></div>
        
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
                            <a href="mycoupons.php" class="flex items-center p-3 text-blue-600 bg-blue-50 rounded-lg font-medium">
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
                            <a href="settings.php" class="flex items-center p-3 text-gray-700 hover:bg-gray-50 rounded-lg font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Beállítások
                            </a>
                        </li>
                        <li>
                            <a href="index.php" class="flex items-center p-3 text-red-600 hover:bg-red-50 rounded-lg font-medium">
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
            <div class="w-full md:w-3/4">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h1 class="text-2xl font-bold mb-6">Kuponjaim</h1>
                    
                    <?php if (empty($kuponok)): ?>
                        <div class="bg-blue-50 p-4 rounded">
                            <p class="text-blue-700">Jelenleg nincs aktív kuponod. A rendeléseid és akciók során szerezhetsz kuponokat.</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ($kuponok as $kupon): ?>
                                <?php 
                                    // Kupon típusához megfelelő színek meghatározása
                                    $cardClass = "border-l-4 rounded-lg shadow-sm p-4 ";
                                    $headerClass = "font-bold text-lg ";
                                    
                                    if (!$kupon['is_active']) {
                                        $cardClass .= "bg-gray-50 border-gray-300";
                                        $headerClass .= "text-gray-400";
                                    } else {
                                        switch ($kupon['type']) {
                                            case 'fixed':
                                                $cardClass .= "bg-indigo-50 border-indigo-500";
                                                $headerClass .= "text-indigo-700";
                                                break;
                                            case 'shipping':
                                                $cardClass .= "bg-green-50 border-green-500";
                                                $headerClass .= "text-green-700";
                                                break;
                                            case 'special':
                                                $cardClass .= "bg-yellow-50 border-yellow-500";
                                                $headerClass .= "text-yellow-700";
                                                break;
                                            default: // 'percentage'
                                                $cardClass .= "bg-blue-50 border-blue-500";
                                                $headerClass .= "text-blue-700";
                                        }
                                    }

                                    // Típus szerinti megjelenítés
                                    $typeDescription = '';
                                    switch ($kupon['type']) {
                                        case 'fixed':
                                            $typeDescription = 'Fix összegű kedvezmény';
                                            break;
                                        case 'shipping':
                                            $typeDescription = 'Ingyenes szállítás';
                                            break;
                                        case 'special':
                                            $typeDescription = 'Speciális ajánlat';
                                            break;
                                        default: // 'percentage'
                                            $typeDescription = 'Százalékos kedvezmény';
                                    }

                                    // Létrehozás dátuma
                                    $created_at = new DateTime($kupon['created_at']);
                                ?>
                                <div class="<?php echo $cardClass; ?>">
                                    <div class="flex justify-between items-start">
                                        <h3 class="<?php echo $headerClass; ?>"><?php echo htmlspecialchars($typeDescription); ?></h3>
                                        <?php if (!$kupon['is_active']): ?>
                                            <span class="bg-gray-200 text-gray-600 text-xs px-2 py-1 rounded">Inaktív</span>
                                        <?php else: ?>
                                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Aktív</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="my-2 text-gray-600">
                                        <p>Kupon típusa: <?php echo htmlspecialchars($typeDescription); ?></p>
                                    </div>
                                    <div class="mt-3 pt-3 border-t border-gray-200">
                                        <div class="flex justify-between items-center">
                                            <div class="bg-gray-100 px-3 py-1 rounded font-mono text-sm"><?php echo htmlspecialchars($kupon['code']); ?></div>
                                            <div class="text-sm text-gray-500">
                                                <span>Létrehozva: <?php echo $created_at->format('Y.m.d'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>