<meta name="color-scheme" content="light dark">
<script>
    (function () {
        var key = 'bb-theme';
        var theme = 'light';

        try {
            var saved = localStorage.getItem(key);
            if (saved === 'dark' || saved === 'light') {
                theme = saved;
            } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                theme = 'dark';
            }
        } catch (error) {
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                theme = 'dark';
            }
        }

        var dark = theme === 'dark';
        document.documentElement.classList.toggle('dark', dark);
        document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
    })();
</script>
