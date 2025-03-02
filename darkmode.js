document.addEventListener('DOMContentLoaded', function() {
    // Dark mode állapot ellenőrzése cookie-ból
    function isDarkMode() {
        const darkModeCookie = getCookie('dark_mode');
        return darkModeCookie === '1';
    }
    
    // Cookie olvasás
    function getCookie(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for(let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }
    
    // Cookie írás
    function setCookie(name, value, days) {
        let expires = "";
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + value + expires + "; path=/";
    }
    
    // Dark mode beállítása
    function setDarkMode(isDark) {
        if (isDark) {
            document.body.classList.add('dark-mode');
        } else {
            document.body.classList.remove('dark-mode');
        }
        
        // Állapot mentése cookie-ba
        setCookie('dark_mode', isDark ? '1' : '0', 365);
        
        // Dark mode kapcsoló állapotának frissítése, ha létezik
        const toggleBtn = document.getElementById('dark-mode-toggle');
        if (toggleBtn) {
            toggleBtn.setAttribute('aria-checked', isDark.toString());
            toggleBtn.innerHTML = isDark ? '☀️ Világos mód' : '🌙 Sötét mód';
        }
    }
    
    // Rendszer preferencia figyelése
    function watchSystemPreference() {
        if (window.matchMedia) {
            const darkModeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            
            // Rendszer preferencia változás esemény
            darkModeMediaQuery.addEventListener('change', (e) => {
                // Csak akkor állítjuk be automatikusan, ha nincs mentett felhasználói beállítás
                if (getCookie('dark_mode') === null) {
                    setDarkMode(e.matches);
                }
            });
            
            // Kezdeti beállítás, ha nincs cookie
            if (getCookie('dark_mode') === null) {
                setDarkMode(darkModeMediaQuery.matches);
            }
        }
    }
    
    // Dark mode kapcsoló létrehozása és eseménykezelő beállítása
    function setupDarkModeToggle() {
        const toggleBtn = document.getElementById('dark-mode-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                const currentMode = isDarkMode();
                setDarkMode(!currentMode);
            });
            
            // Kezdeti állapot beállítása
            const currentMode = isDarkMode();
            toggleBtn.setAttribute('aria-checked', currentMode.toString());
            toggleBtn.innerHTML = currentMode ? '☀️ Világos mód' : '🌙 Sötét mód';
        }
    }
    
    // Inicializálás
    function initialize() {
        // Sötét mód beállítása a cookie alapján
        setDarkMode(isDarkMode());
        
        // Rendszer preferencia figyelése
        watchSystemPreference();
        
        // Kapcsoló beállítása
        setupDarkModeToggle();
    }
    
    // Alkalmazás inicializálása
    initialize();
});