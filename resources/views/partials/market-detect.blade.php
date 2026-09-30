<script>
    (() => {
        const market = document.documentElement.dataset.market || 'GBP';
        if (market === 'NGN') return;

        const hasCountryCookie = document.cookie.split('; ').some((part) => part.startsWith('cutcost_country='));
        if (hasCountryCookie) return;

        const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
        const lang = (navigator.language || '').toLowerCase();
        const looksNigerian = tz === 'Africa/Lagos' || lang === 'en-ng' || lang.endsWith('-ng');

        // Reload once only: the cookie is set before reloading, so the next pass exits above.
        if (looksNigerian) {
            document.cookie = 'cutcost_country=NG; Max-Age=31536000; Path=/; SameSite=Lax';
            window.location.reload();
        }
    })();
</script>
