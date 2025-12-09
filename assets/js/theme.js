// assets/js/theme.js
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.createElement('button');
    themeToggle.className = 'theme-toggle';
    themeToggle.innerHTML = '🌙';
    themeToggle.title = 'Toggle dark/light mode';
    document.body.appendChild(themeToggle);
    
    const currentTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', currentTheme);
    updateToggleIcon(currentTheme);
    
    themeToggle.addEventListener('click', function() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateToggleIcon(newTheme);
        
        // Sauvegarder la préférence si utilisateur connecté
        if (window.userId) {
            saveThemePreference(newTheme);
        }
    });
    
    function updateToggleIcon(theme) {
        themeToggle.innerHTML = theme === 'light' ? '🌙' : '☀️';
    }
    
    async function saveThemePreference(theme) {
        try {
            await fetch('/api/update-theme', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ theme: theme })
            });
        } catch (error) {
            console.error('Failed to save theme preference:', error);
        }
    }
});