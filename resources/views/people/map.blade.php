@extends('layouts.app')

@section('title', 'Карта членів')

@section('content')
<div class="max-w-7xl mx-auto" x-data="membersMap()">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-gray-900 dark:text-white">Карта членів</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        <span x-text="markers.length"></span> на карті
                        <template x-if="error">
                            <span class="text-red-500" x-text="error"></span>
                        </template>
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                <select @change="loadData()" x-model="filters.ministry_id"
                        class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="">Усі команди</option>
                    @foreach($ministries as $ministry)
                        <option value="{{ $ministry->id }}">{{ $ministry->name }}</option>
                    @endforeach
                </select>
                <select @change="loadData()" x-model="filters.group_id"
                        class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="">Усі групи</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Map -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div id="members-map" class="w-full" style="height: calc(100vh - 250px); min-height: 400px;"></div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.Default.css" />
<style>
    .leaflet-popup-content-wrapper { border-radius: 12px; }
    .dark .leaflet-tile { filter: brightness(0.8) contrast(1.1) saturate(0.8); }
    .member-popup { text-align: center; min-width: 150px; }
    .member-popup img { width: 48px; height: 48px; border-radius: 50%; object-fit: cover; margin: 0 auto 8px; }
    .member-popup .name { font-weight: 600; font-size: 14px; margin-bottom: 4px; }
    .member-popup .phone { font-size: 12px; color: #6b7280; }
    .member-popup .address { font-size: 11px; color: #9ca3af; margin-top: 4px; }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/leaflet.markercluster.min.js"></script>
<script>
function membersMap() {
    return {
        map: null,
        markerGroup: null,
        markers: [],
        error: '',
        filters: { ministry_id: '', group_id: '' },

        init() {
            // Load data first regardless of map
            this.loadData();

            // Initialize map with retry
            this.$nextTick(() => this.initMap());
        },

        initMap() {
            try {
                if (typeof L === 'undefined') {
                    this.error = '(Leaflet не завантажився)';
                    console.error('Leaflet not loaded');
                    return;
                }

                const el = document.getElementById('members-map');
                if (!el) return;

                this.map = L.map('members-map').setView([48.45, 35.05], 6);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap',
                    maxZoom: 18,
                }).addTo(this.map);
                this.markerGroup = L.markerClusterGroup();
                this.map.addLayer(this.markerGroup);

                // If data already loaded, render markers
                if (this.markers.length) {
                    this.renderMarkers();
                }
            } catch (e) {
                this.error = '(помилка ініціалізації карти)';
                console.error('Map init error:', e);
            }
        },

        async loadData() {
            const params = new URLSearchParams();
            if (this.filters.ministry_id) params.set('ministry_id', this.filters.ministry_id);
            if (this.filters.group_id) params.set('group_id', this.filters.group_id);

            try {
                const res = await fetch(`{{ route('people.map-data') }}?${params}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!res.ok) {
                    this.error = `(помилка ${res.status})`;
                    console.error('Map data response:', res.status, res.statusText);
                    return;
                }

                this.markers = await res.json();
                this.renderMarkers();
            } catch (e) {
                this.error = '(помилка завантаження)';
                console.error('Map data error:', e);
            }
        },

        renderMarkers() {
            if (!this.markerGroup) return;
            this.markerGroup.clearLayers();
            if (!this.markers.length) return;

            const bounds = [];
            this.markers.forEach(p => {
                const photoHtml = p.photo
                    ? `<img src="${p.photo}" alt="${p.name}">`
                    : `<div style="width:48px;height:48px;border-radius:50%;background:#e5e7eb;display:flex;align-items:center;justify-content:center;margin:0 auto 8px;font-size:18px;color:#6b7280;">${p.name.charAt(0)}</div>`;

                const popup = `<div class="member-popup">
                    ${photoHtml}
                    <div class="name">${p.name}</div>
                    ${p.phone ? `<div class="phone"><a href="tel:${p.phone}">${p.phone}</a></div>` : ''}
                    ${p.address ? `<div class="address">${p.address}</div>` : ''}
                </div>`;

                const marker = L.marker([p.lat, p.lng]).bindPopup(popup);
                this.markerGroup.addLayer(marker);
                bounds.push([p.lat, p.lng]);
            });

            if (bounds.length && this.map) {
                this.map.fitBounds(bounds, { padding: [50, 50], maxZoom: 14 });
            }
        }
    };
}
</script>
@endpush
@endsection
