<?php
// Session indítása - bejelentkezéshez szükséges
session_start();

// Ellenőrizzük, hogy a felhasználó be van-e jelentkezve
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Nincs bejelentkezve']);
    exit();
}

// Adatbázis kapcsolat betöltése
require_once 'config.php';

// JSON válasz fejléc beállítása
header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

// Felhasználói azonosító
$userId = $_SESSION['user_id'];

// AJAX kérések feldolgozása adatfrissítéshez
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Személyes adatok frissítése
    if (isset($_POST['action']) && $_POST['action'] === 'update_personal') {
        $fullName = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        
        // Validálás - példa
        if (empty($fullName) || empty($email)) {
            $response['message'] = 'A név és az email kötelező mezők!';
            echo json_encode($response);
            exit();
        }
        
        // Email formátum ellenőrzése
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Érvénytelen email formátum!';
            echo json_encode($response);
            exit();
        }
        
        // Megnézzük, hogy az email cím már használatban van-e más felhasználónál
        $emailCheckSql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $emailCheckStmt = $conn->prepare($emailCheckSql);
        $emailCheckStmt->bind_param("si", $email, $userId);
        $emailCheckStmt->execute();
        $emailCheckResult = $emailCheckStmt->get_result();
        
        if ($emailCheckResult->num_rows > 0) {
            $response['message'] = 'Ez az email cím már használatban van!';
            echo json_encode($response);
            exit();
        }
        
        // Frissítés az adatbázisban - a name mezőt használjuk a full_name helyett, az adatbázis struktúrának megfelelően
        $updateSql = "UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("sssi", $fullName, $email, $phone, $userId);
                
        // Az executeStmt utáni résznél:
        if ($updateStmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Személyes adatok sikeresen frissítve!';
            $response['debug'] = [
                'affected_rows' => $updateStmt->affected_rows,
                'error' => $conn->error
            ];
        } else {
            $response['message'] = 'Hiba történt a frissítés során: ' . $conn->error;
            $response['debug'] = [
                'sql_error' => $conn->error, 
                'sql_errno' => $conn->errno
            ];
        }
        
        echo json_encode($response);
        exit();
    }
    
    // Szállítási cím frissítése
    if (isset($_POST['action']) && $_POST['action'] === 'update_address') {
        $address = $_POST['address'] ?? '';
        $city = $_POST['city'] ?? '';
        $zip = $_POST['zip'] ?? '';
        
        // Validálás - példa
        if (empty($city) || empty($zip)) {
            $response['message'] = 'A város és az irányítószám kötelező mezők!';
            echo json_encode($response);
            exit();
        }
        
        // Frissítés az adatbázisban - a megfelelő mezőnevekkel, az adatbázis struktúrának megfelelően
        $updateSql = "UPDATE users SET address = ?, town = ?, postalCode = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("sssi", $address, $city, $zip, $userId);
        
        if ($updateStmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Szállítási cím sikeresen frissítve!';
        } else {
            $response['message'] = 'Hiba történt a frissítés során: ' . $conn->error;
        }
        
        echo json_encode($response);
        exit();
    }
    
    // Jelszó módosítása
    if (isset($_POST['action']) && $_POST['action'] === 'update_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validálás
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $response['message'] = 'Minden jelszó mező kitöltése kötelező!';
            echo json_encode($response);
            exit();
        }
        
        // Jelszó minimum hosszának ellenőrzése
        if (strlen($newPassword) < 8) {
            $response['message'] = 'Az új jelszónak legalább 8 karakter hosszúnak kell lennie!';
            echo json_encode($response);
            exit();
        }
        
        // Jelenlegi jelszó ellenőrzése
        $passwordSql = "SELECT password FROM users WHERE id = ?";
        $passwordStmt = $conn->prepare($passwordSql);
        $passwordStmt->bind_param("i", $userId);
        $passwordStmt->execute();
        $passwordResult = $passwordStmt->get_result();
        $userData = $passwordResult->fetch_assoc();
        
        if (!password_verify($currentPassword, $userData['password'])) {
            $response['message'] = 'A jelenlegi jelszó nem megfelelő!';
            echo json_encode($response);
            exit();
        }
        
        // Új jelszó és megerősítés egyezésének ellenőrzése
        if ($newPassword !== $confirmPassword) {
            $response['message'] = 'Az új jelszó és a megerősítés nem egyezik!';
            echo json_encode($response);
            exit();
        }
        
        // Jelszó frissítése
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $updatePasswordSql = "UPDATE users SET password = ? WHERE id = ?";
        $updatePasswordStmt = $conn->prepare($updatePasswordSql);
        $updatePasswordStmt->bind_param("si", $hashedPassword, $userId);
        
        if ($updatePasswordStmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Jelszó sikeresen módosítva!';
        } else {
            $response['message'] = 'Hiba történt a jelszó módosítása során: ' . $conn->error;
        }
        
        echo json_encode($response);
        exit();
    }
    
    // Ha ide eljut, akkor nincs valid action
    $response['message'] = 'Érvénytelen kérés';
    echo json_encode($response);
}
?>