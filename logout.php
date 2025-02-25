<?php
// Session indítása
session_start();

// Session változók törlése
$_SESSION = array();

// Cookie törlése, ami a session-t tárolja
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Session megsemmisítése
session_destroy();

// JavaScript kód a localStorage törlésére
echo '<script>
    localStorage.removeItem("wheelCountdownEnd");
    window.location.href = "index.php";
</script>';
exit();
?>