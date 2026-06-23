{{-- Employee active working-time tracker (HR portal).
     Counts active seconds (cursor/keyboard, tab visible) into the SHARED
     agent_active_times total, shows a live clock, pauses on idle/tab-switch,
     and pops a "Welcome back" toast on resume. --}}
@auth('employee')
@if(auth('employee')->user()->agent_id)
<script>
(function () {
    var HEARTBEAT_URL = "{{ route('employee.activity.heartbeat') }}";
    var CSRF = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
    if (!CSRF) return;

    var IDLE_LIMIT_MS = 60000, TICK_MS = 5000, FLUSH_MS = 60000;
    var lastActivity = Date.now(), pendingSeconds = 0, idle = false;

    // Live display value (seconds). Seeded from any active-time element on the page
    // (navbar widget + dashboard banner both carry class js-active-time).
    function activeEls() { return document.querySelectorAll('.js-active-time, #hrActiveTimeDisplay'); }
    var displaySeconds = 0;
    activeEls().forEach(function (el) {
        var s = parseInt(el.dataset.seconds || '0', 10) || 0;
        if (s > displaySeconds) displaySeconds = s;
    });

    function human(s) {
        s = Math.max(0, Math.floor(s));
        var h = Math.floor(s / 3600), m = Math.floor((s % 3600) / 60);
        return (h > 0 ? h + 'h ' : '') + m + 'm';
    }
    function paintClock() {
        var h = human(displaySeconds);
        activeEls().forEach(function (el) { el.textContent = h; });
    }
    paintClock();

    // ── Live shift countdown (runs on every screen; drives all .js-shift-banner) ──
    (function () {
        var banners = document.querySelectorAll('.js-shift-banner');
        if (!banners.length) return;

        function parseToday(hhmm) {
            var p = (hhmm || '00:00').split(':');
            var d = new Date();
            d.setHours(parseInt(p[0], 10) || 0, parseInt(p[1], 10) || 0, 0, 0);
            return d;
        }
        function pad(n) { return (n < 10 ? '0' : '') + n; }
        function fmt(secs) {
            secs = Math.max(0, Math.floor(secs));
            return pad(Math.floor(secs / 3600)) + ':' + pad(Math.floor((secs % 3600) / 60)) + ':' + pad(secs % 60);
        }
        function tick() {
            var now = new Date();
            banners.forEach(function (b) {
                var start = parseToday(b.dataset.start), end = parseToday(b.dataset.end);
                if (end <= start) end = new Date(end.getTime() + 86400000); // overnight
                var phaseEl = b.querySelector('.js-shift-phase'),
                    valEl   = b.querySelector('.js-shift-value'),
                    barEl   = b.querySelector('.js-shift-progress');
                if (now < start) {
                    if (phaseEl) phaseEl.textContent = 'Starts in';
                    if (valEl) valEl.textContent = fmt((start - now) / 1000);
                    if (barEl) barEl.style.width = '0%';
                } else if (now < end) {
                    if (phaseEl) phaseEl.textContent = 'Time left';
                    if (valEl) valEl.textContent = fmt((end - now) / 1000);
                    if (barEl) barEl.style.width = Math.min(100, ((now - start) / (end - start)) * 100).toFixed(1) + '%';
                } else {
                    if (phaseEl) phaseEl.textContent = 'Shift ended';
                    if (valEl) valEl.textContent = '00:00:00';
                    if (barEl) barEl.style.width = '100%';
                }
            });
        }
        tick();
        setInterval(tick, 1000);
    })();

    function showWelcomeBack() {
        // Self-contained toast with !important inline styles so the HR theme's
        // large heading/font rules can't inflate it (SweetAlert gets blown up here).
        var box = document.createElement('div');
        box.setAttribute('style', [
            'position:fixed!important','top:20px!important','right:20px!important','z-index:2147483647!important',
            'background:#16a34a!important','color:#fff!important','padding:12px 16px!important',
            'border-radius:10px!important','box-shadow:0 8px 24px rgba(0,0,0,.28)!important',
            'max-width:300px!important','width:auto!important','box-sizing:border-box!important',
            'font-family:Segoe UI,Arial,sans-serif!important','opacity:1!important'
        ].join(';'));
        var title = document.createElement('div');
        title.setAttribute('style', 'font-size:15px!important;font-weight:700!important;line-height:1.3!important;margin:0 0 2px 0!important;color:#fff!important;');
        title.textContent = 'Welcome back! 👋';
        var sub = document.createElement('div');
        sub.setAttribute('style', 'font-size:12.5px!important;font-weight:400!important;line-height:1.4!important;color:#fff!important;');
        sub.textContent = 'Your work timer has resumed.';
        box.appendChild(title); box.appendChild(sub);
        document.body.appendChild(box);
        setTimeout(function () { box.style.transition = 'opacity .4s'; box.style.setProperty('opacity', '0', 'important'); setTimeout(function () { box.remove(); }, 400); }, 3000);
    }

    function markActive() {
        if (idle) { idle = false; showWelcomeBack(); }
        lastActivity = Date.now();
    }
    ['mousemove','mousedown','keydown','scroll','touchstart','click','wheel'].forEach(function (ev) {
        window.addEventListener(ev, markActive, { passive: true });
    });

    // Accrue 1s of live display each second while active; batch for the server.
    var liveTick = setInterval(function () {
        var visible = (document.visibilityState !== 'hidden');
        if (visible && (Date.now() - lastActivity) < IDLE_LIMIT_MS) {
            displaySeconds += 1;
            paintClock();
        }
    }, 1000);

    setInterval(function () {
        var visible = (document.visibilityState !== 'hidden');
        if (visible && (Date.now() - lastActivity) < IDLE_LIMIT_MS) {
            pendingSeconds += TICK_MS / 1000;
        } else {
            idle = true;
        }
    }, TICK_MS);

    function flush(useBeacon) {
        var secs = Math.round(pendingSeconds);
        if (secs < 1) return;
        pendingSeconds -= secs;

        if (useBeacon && navigator.sendBeacon) {
            var fd = new FormData(); fd.append('_token', CSRF); fd.append('seconds', secs);
            navigator.sendBeacon(HEARTBEAT_URL, fd);
            return;
        }
        fetch(HEARTBEAT_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
            body: JSON.stringify({ seconds: secs }), keepalive: true
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res && typeof res.today_seconds === 'number') {
                displaySeconds = res.today_seconds; // reconcile with shared total
                paintClock();
            }
        })
        .catch(function () { pendingSeconds += secs; });
    }

    // Pull the latest shared total from the DB and repaint (no increment)
    function syncTotal() {
        fetch(HEARTBEAT_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
            body: JSON.stringify({ seconds: 0 })
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res && typeof res.today_seconds === 'number') {
                if (res.today_seconds > displaySeconds) displaySeconds = res.today_seconds; // never go backwards mid-tick
                paintClock();
            }
        })
        .catch(function () {});
    }

    setInterval(function () { flush(false); }, FLUSH_MS);
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'hidden') { flush(true); idle = true; }
        else { markActive(); syncTotal(); }  // returning to tab: show the true shared total now
    });
    window.addEventListener('pagehide', function () { flush(true); });
})();
</script>
@endif
@endauth
