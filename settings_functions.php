<?php
/**
 * Segédfunkciók a felhasználói beállítások kezeléséhez
 */

/**
 * Felhasználói beállítások ellenőrzése és lekérése
 * Ha a munkamenetben nincs beállítás, az alapértelmezetteket adja vissza
 */
function getUserSettings() {
    // Alapértelmezett beállítások
    $defaultSettings = [
        'dark_mode' => 0,
        'notifications' => 1,
        'newsletter' => 1,
        'language' => 'hu',
        'currency' => 'HUF',
        'items_per_page' => 12
    ];
    
    // Ellenőrizzük, hogy a munkamenetben vannak-e beállítások
    if (isset($_SESSION['settings']) && is_array($_SESSION['settings'])) {
        return $_SESSION['settings'];
    }
    
    return $defaultSettings;
}

/**
 * Beállítás érték lekérése
 */
function getSetting($key, $default = null) {
    $settings = getUserSettings();
    
    if (isset($settings[$key])) {
        return $settings[$key];
    }
    
    return $default;
}

/**
 * Pénznemek kezelése és formázása
 */
function formatCurrency($amount, $currency = null) {
    if ($currency === null) {
        $currency = getSetting('currency', 'HUF');
    }
    
    switch ($currency) {
        case 'EUR':
            return number_format($amount, 2, ',', ' ') . ' €';
        case 'USD':
            return '$' . number_format($amount, 2, '.', ',');
        case 'HUF':
        default:
            return number_format($amount, 0, ',', ' ') . ' Ft';
    }
}

/**
 * Lapozás beállítása terméklistákhoz
 */
function getItemsPerPage() {
    return (int) getSetting('items_per_page', 12);
}

/**
 * Nyelvi beállítások kezelése
 */
function getCurrentLanguage() {
    return getSetting('language', 'hu');
}

/**
 * Sötét mód beállítás ellenőrzése
 */
function isDarkModeEnabled() {
    return (bool) getSetting('dark_mode', 0);
}

/**
 * Nyelvi fordításokat kezelő funkció
 * (egyszerű példa, valós környezetben összetettebb megoldás javasolt)
 */
function translate($key, $replacements = []) {
    $lang = getCurrentLanguage();
    
    // Itt valójában egy teljes fordítási rendszert használnánk
    // ez csak egy egyszerű példa
    $translations = [
        'hu' => [
            'save' => 'Mentés',
            'cancel' => 'Mégse',
            'welcome' => 'Üdvözöljük, {name}!',
        ],
        'en' => [
            'save' => 'Save',
            'cancel' => 'Cancel',
            'welcome' => 'Welcome, {name}!',
        ],
        'de' => [
            'save' => 'Speichern',
            'cancel' => 'Abbrechen',
            'welcome' => 'Willkommen, {name}!',
        ],
    ];
    
    // Ha a kulcs létezik az adott nyelvben
    if (isset($translations[$lang][$key])) {
        $text = $translations[$lang][$key];
        
        // Helyettesítések végrehajtása
        foreach ($replacements as $placeholder => $value) {
            $text = str_replace('{' . $placeholder . '}', $value, $text);
        }
        
        return $text;
    }
    
    // Fallback az alapértelmezett nyelvre
    if ($lang !== 'hu' && isset($translations['hu'][$key])) {
        return $translations['hu'][$key];
    }
    
    // Ha sehol nincs, visszaadjuk a kulcsot
    return $key;
}