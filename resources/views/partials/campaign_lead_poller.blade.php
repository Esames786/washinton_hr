{{-- New campaign leads, near real time: every 20s asks AdminCampaignLeadController::poll for the
     pending count (header bell) and any lead newer than the last one this browser has seen, which
     it announces with a toast naming the applicant and a sound. The last seen id is kept in
     localStorage so moving between pages doesn't miss or repeat a lead, and two open tabs don't
     both announce it. The first visit only records where things stand; it announces nothing. --}}
<script>
(function () {
    var url = @json(route('admin.campaign_leads.poll'));
    var sound = @json(asset('audio/notification.mp3'));
    var KEY = 'hrLeadLastId';

    // -1 = this browser has never polled. 0 is a real value (no leads yet), so the first lead
    // after an empty table is still announced. Falls back to memory if storage is blocked.
    var memo = -1;
    function lastSeen() {
        try { var v = localStorage.getItem(KEY); return v === null ? -1 : (parseInt(v, 10) || 0); }
        catch (e) { return memo; }
    }
    function remember(id) { memo = id; try { localStorage.setItem(KEY, String(id)); } catch (e) {} }
    function esc(s) { return $('<div>').text(s == null ? '' : String(s)).html(); }

    function poll() {
        $.getJSON(url, { after: lastSeen() }).done(function (res) {
            if (!res || !res.allowed) { return; }

            var $bell = $('#leadBellCount');
            if (res.pending > 0) {
                $bell.text(res.pending > 99 ? '99+' : res.pending).show();
            } else {
                $bell.hide();
            }

            if (lastSeen() < 0) { remember(res.max_id || 0); return; }

            var items = res['new'] || [];
            if (!items.length) { return; }
            remember(items[items.length - 1].id);

            items.forEach(function (lead) {
                if (window.toastr) {
                    toastr.info(
                        '<a href="' + esc(lead.url) + '" style="color:inherit;text-decoration:underline;">' + esc(lead.name) + '</a>'
                            + (lead.where ? '<br><small>' + esc(lead.where) + '</small>' : ''),
                        'New campaign lead',
                        { closeButton: true, timeOut: 0, extendedTimeOut: 0, escapeHtml: false }
                    );
                }
            });
            // Browsers only allow sound after the user has clicked on the page; ignore a refusal.
            try { var p = new Audio(sound).play(); if (p && p.catch) { p.catch(function () {}); } } catch (e) {}
            $(document).trigger('hr:new-leads', [items]);
        });
    }

    $(function () { poll(); setInterval(poll, 20000); });
})();
</script>
