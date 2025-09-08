@php
    // Valores actuales del form por si hay edición
    $livewire = $getLivewire(); // instancia Livewire del form
    $lat = data_get($livewire, 'data.latitud') ?? 6.25184;    // Medellín por defecto (ajusta)
    $lng = data_get($livewire, 'data.longitud') ?? -75.56359;
@endphp

{{-- Incluye Leaflet (hazlo una sola vez en tu layout si prefieres) --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div
    x-data="{
        lat: {{ $lat }},
        lng: {{ $lng }},
        map: null,
        marker: null,
        init() {
            // Crear mapa
            this.map = L.map(this.$refs.map).setView([this.lat, this.lng], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(this.map);

            // Marker arrastrable
            this.marker = L.marker([this.lat, this.lng], { draggable: true }).addTo(this.map);

            const update = (lat, lng) => {
                this.lat = lat;
                this.lng = lng;

                // Escribe al estado del form de Filament v3:
                // El path por defecto es 'data'
                $wire.$set('data.latitud', Number(lat).toFixed(6));
                $wire.$set('data.longitud', Number(lng).toFixed(6));
            };

            // Click en mapa
            this.map.on('click', (e) => {
                const { lat, lng } = e.latlng;
                this.marker.setLatLng([lat, lng]);
                update(lat, lng);
            });

            // Drag del marker
            this.marker.on('dragend', (e) => {
                const pos = e.target.getLatLng();
                update(pos.lat, pos.lng);
            });
        }
    }"
    class="w-full"
>
    <div x-ref="map" class="w-full border rounded-md h-80"></div>

    <div class="mt-2 text-xs text-gray-600">
        Lat: <span x-text="Number(lat).toFixed(6)"></span>,
        Lng: <span x-text="Number(lng).toFixed(6)"></span>
    </div>
</div>
