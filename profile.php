<?php
// Session indítása - bejelentkezéshez szükséges
session_start();

// Ellenőrizzük, hogy a felhasználó be van-e jelentkezve
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Átirányítás a bejelentkezési oldalra, ha nincs bejelentkezve
    exit();
}

// Adatbázis kapcsolat betöltése
require_once 'config.php';

// Üzenetek kezelése
$notification = '';
$notificationType = '';

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

// Form beküldés feldolgozása (hagyományos módszer)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Személyes adatok frissítése
    if (isset($_POST['action']) && $_POST['action'] === 'update_personal') {
        $fullName = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        
        // Validálás - példa
        if (empty($fullName) || empty($email)) {
            $notification = 'A név és az email kötelező mezők!';
            $notificationType = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $notification = 'Érvénytelen email formátum!';
            $notificationType = 'error';
        } else {
            // Megnézzük, hogy az email cím már használatban van-e más felhasználónál
            $emailCheckSql = "SELECT id FROM users WHERE email = ? AND id != ?";
            $emailCheckStmt = $conn->prepare($emailCheckSql);
            $emailCheckStmt->bind_param("si", $email, $userId);
            $emailCheckStmt->execute();
            $emailCheckResult = $emailCheckStmt->get_result();
            
            if ($emailCheckResult->num_rows > 0) {
                $notification = 'Ez az email cím már használatban van!';
                $notificationType = 'error';
            } else {
                // Frissítés az adatbázisban
                $updateSql = "UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("sssi", $fullName, $email, $phone, $userId);
                
                if ($updateStmt->execute()) {
                    $notification = 'Személyes adatok sikeresen frissítve!';
                    $notificationType = 'success';
                    
                    // Adatok újratöltése, hogy a megjelenítés frissüljön
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $user = $result->fetch_assoc();
                } else {
                    $notification = 'Hiba történt a frissítés során: ' . $conn->error;
                    $notificationType = 'error';
                }
            }
        }
    }
    
    // Szállítási cím frissítése
    if (isset($_POST['action']) && $_POST['action'] === 'update_address') {
        $address = $_POST['address'] ?? '';
        $city = $_POST['city'] ?? '';
        $zip = $_POST['zip'] ?? '';
        
        // Validálás - példa
        if (empty($city) || empty($zip)) {
            $notification = 'A város és az irányítószám kötelező mezők!';
            $notificationType = 'error';
        } else {
            // Frissítés az adatbázisban
            $updateSql = "UPDATE users SET address = ?, town = ?, postalCode = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("sssi", $address, $city, $zip, $userId);
            
            if ($updateStmt->execute()) {
                $notification = 'Szállítási cím sikeresen frissítve!';
                $notificationType = 'success';
                
                // Adatok újratöltése, hogy a megjelenítés frissüljön
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
            } else {
                $notification = 'Hiba történt a frissítés során: ' . $conn->error;
                $notificationType = 'error';
            }
        }
    }
    
    // Jelszó módosítása
    if (isset($_POST['action']) && $_POST['action'] === 'update_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validálás
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $notification = 'Minden jelszó mező kitöltése kötelező!';
            $notificationType = 'error';
        } elseif (strlen($newPassword) < 8) {
            $notification = 'Az új jelszónak legalább 8 karakter hosszúnak kell lennie!';
            $notificationType = 'error';
        } else {
            // Jelenlegi jelszó ellenőrzése
            $passwordSql = "SELECT password FROM users WHERE id = ?";
            $passwordStmt = $conn->prepare($passwordSql);
            $passwordStmt->bind_param("i", $userId);
            $passwordStmt->execute();
            $passwordResult = $passwordStmt->get_result();
            $userData = $passwordResult->fetch_assoc();
            
            if (!password_verify($currentPassword, $userData['password'])) {
                $notification = 'A jelenlegi jelszó nem megfelelő!';
                $notificationType = 'error';
            } elseif ($newPassword !== $confirmPassword) {
                $notification = 'Az új jelszó és a megerősítés nem egyezik!';
                $notificationType = 'error';
            } else {
                // Jelszó frissítése
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updatePasswordSql = "UPDATE users SET password = ? WHERE id = ?";
                $updatePasswordStmt = $conn->prepare($updatePasswordSql);
                $updatePasswordStmt->bind_param("si", $hashedPassword, $userId);
                
                if ($updatePasswordStmt->execute()) {
                    $notification = 'Jelszó sikeresen módosítva!';
                    $notificationType = 'success';
                } else {
                    $notification = 'Hiba történt a jelszó módosítása során: ' . $conn->error;
                    $notificationType = 'error';
                }
            }
        }
    }
}

$darkMode = isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === '1';

// Ha van bejelentkezett felhasználó és vannak beállításai, akkor onnan is lekérhetjük
if (isset($_SESSION['user_id']) && isset($userSettings['dark_mode'])) {
    $darkMode = $userSettings['dark_mode'] == 1;
}

$monogram = generateMonogram($user['name']);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Felhasználói Profil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="darkmode.css">
    <script src="darkmode.js"></script>
</head>
<body class="bg-gray-100 min-h-screen <?php echo $darkMode ? 'dark-mode' : ''; ?>">
    <div class="container mx-auto px-4 py-8">
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
                            <a href="profile.php" class="flex items-center p-3 text-blue-600 bg-blue-50 rounded-lg font-medium">
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
            
            <!-- Main Content -->
            <div class="flex-1 bg-white rounded-lg shadow-md p-6">
                <h1 class="text-2xl font-bold mb-6">Profilom</h1>
                
                <?php if (!empty($notification)): ?>
                    <div id="notification" class="mb-6 p-4 <?php echo $notificationType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?> rounded">
                        <p><?php echo htmlspecialchars($notification); ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h2 class="text-lg font-semibold mb-4">Személyes adatok</h2>
                        <form id="personalForm" method="POST">
                            <input type="hidden" name="action" value="update_personal">
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                                    Teljes név
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                                       id="name" 
                                       name="name" 
                                       type="text" 
                                       value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>">
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                                    Email cím
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                                       id="email" 
                                       name="email" 
                                       type="email" 
                                       value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">
                                    Telefonszám
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                                       id="phone" 
                                       name="phone" 
                                       type="tel" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            <div>
                                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" 
                                        type="submit">
                                    Mentés
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div>
                        <h2 class="text-lg font-semibold mb-4">Szállítási cím</h2>
                        <form id="addressForm" method="POST">
                            <input type="hidden" name="action" value="update_address">
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="address">
                                    Cím
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                                       id="address" 
                                       name="address" 
                                       type="text" 
                                       value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="city">
                                    Város
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                                       id="city" 
                                       name="city" 
                                       type="text" 
                                       value="<?php echo htmlspecialchars($user['town'] ?? ''); ?>">
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="zip">
                                    Irányítószám
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                                       id="zip" 
                                       name="zip" 
                                       type="text" 
                                       value="<?php echo htmlspecialchars($user['postalCode'] ?? ''); ?>">
                            </div>
                            <div>
                                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" 
                                        type="submit">
                                    Mentés
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="mt-8">
                    <h2 class="text-lg font-semibold mb-4">Jelszó módosítása</h2>
                    <form id="passwordForm" class="max-w-md" method="POST">
                        <input type="hidden" name="action" value="update_password">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="current-password">
                                Jelenlegi jelszó
                            </label>
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                                   id="current-password" 
                                   name="current_password" 
                                   type="password" 
                                   required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="new-password">
                                Új jelszó
                            </label>
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                                   id="new-password" 
                                   name="new_password" 
                                   type="password" 
                                   required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="confirm-password">
                                Új jelszó megerősítése
                            </label>
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                                   id="confirm-password" 
                                   name="confirm_password" 
                                   type="password" 
                                   required>
                        </div>
                        <div>
                            <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" 
                                    type="submit">
                                Jelszó módosítása
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>