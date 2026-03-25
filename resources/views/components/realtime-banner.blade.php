@props(['channel'])
{{-- Real-time update banner via Laravel Reverb --}}
@auth
<script>
if (window.Echo) {
    window.Echo.private('church.{{ $currentChurch->id }}.{{ $channel }}')
        .listen('.data.updated', function(e) {
            var id = 'realtime-banner-{{ $channel }}';
            if (document.getElementById(id)) return;

            var banner = document.createElement('div');
            banner.id = id;
            banner.className = 'fixed bottom-4 right-4 z-50 bg-primary-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3 cursor-pointer hover:bg-primary-700 transition-colors';
            banner.innerHTML = '<svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>'
                + '<span>{{ __("app.new_data_available") }}</span>';
            banner.onclick = function() { window.location.reload(); };
            document.body.appendChild(banner);

            setTimeout(function() {
                if (banner.parentNode) banner.remove();
            }, 15000);
        });
}
</script>
@endauth
