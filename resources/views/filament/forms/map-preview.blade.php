@php
    $mapId = 'leaflet-map-' . (string) \Illuminate\Support\Str::uuid();
    $latPath = $generateRelativeStatePath('lat');
    $lngPath = $generateRelativeStatePath('lng');
    $namePath = $generateRelativeStatePath('nama');
@endphp

<div
    class="fi-section-content"
    x-data="{
        mapId: @js($mapId),
        lat: $wire.{{ $applyStateBindingModifiers("\$entangle('{$latPath}')") }},
        lng: $wire.{{ $applyStateBindingModifiers("\$entangle('{$lngPath}')") }},
        label: $wire.{{ $applyStateBindingModifiers("\$entangle('{$namePath}')") }},
        map: null,
        marker: null,
        icon: null,
        getIcon() {
            if (this.icon) return this.icon;
            const url = @js(asset('images/markers/construction.svg'));
            this.icon = L.icon({
                iconUrl: url,
                iconSize: [36, 36],
                iconAnchor: [18, 34],
                popupAnchor: [0, -30],
            });
            return this.icon;
        },
        parseLocaleFloat(v) {
            if (v === null || v === undefined) return NaN;
            if (typeof v === 'number') return v;
            let s = String(v).trim();
            // replace comma as decimal if no dot present
            if (s.includes(',') && !s.includes('.')) s = s.replace(',', '.');
            // remove thousand separators like 1.234,56 or 1,234.56
            s = s.replace(/\.(?=\d{3}(\D|$))/g, '');
            s = s.replace(/,(?=\d{3}(\D|$))/g, '');
            return parseFloat(s);
        },
        ready() { return typeof L !== 'undefined'; },
        isValid() {
            const a = this.parseLocaleFloat(this.lat), b = this.parseLocaleFloat(this.lng);
            return Number.isFinite(a) && Number.isFinite(b);
        },
        createOrUpdate() {
            if (!this.ready() || !this.isValid()) return;
            const a = this.parseLocaleFloat(this.lat), b = this.parseLocaleFloat(this.lng);
            if (!this.map) {
                this.map = L.map(this.mapId, { center: [a, b], zoom: 13, scrollWheelZoom: false });
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(this.map);
                this.marker = L.marker([a, b], { draggable: true, icon: this.getIcon() }).addTo(this.map);
                if (this.label) this.marker.bindPopup(this.label);
                // Drag to update lat/lng back to the form
                this.marker.on('dragend', (e) => {
                    const pos = e.target.getLatLng();
                    const lat = Number(pos.lat).toFixed(6);
                    const lng = Number(pos.lng).toFixed(6);
                    this.lat = lat;
                    this.lng = lng;
                });
                // Click on map to move marker and update
                this.map.on('click', (e) => {
                    const lat = Number(e.latlng.lat).toFixed(6);
                    const lng = Number(e.latlng.lng).toFixed(6);
                    this.marker.setLatLng([lat, lng]);
                    this.lat = lat;
                    this.lng = lng;
                });
                setTimeout(() => this.map.invalidateSize(), 0);
                return;
            }
            this.map.setView([a, b], this.map.getZoom());
            this.marker.setLatLng([a, b]);
            this.marker.setIcon(this.getIcon());
        },
        updateLabel() {
            if (this.marker && this.label) this.marker.bindPopup(this.label);
        },
    }"
    x-init="
        const boot = () => { if (!ready()) { setTimeout(boot, 50); return; } createOrUpdate(); };
        boot();
        $watch('lat', () => createOrUpdate());
        $watch('lng', () => createOrUpdate());
        $watch('label', () => updateLabel());
    "
>
    <div class="text-sm text-gray-600 mb-2">pratinjau lokasi menggunakan Leaflet (OSM)</div>
    <div id="{{ $mapId }}" style="height: 320px; border-radius: 0.5rem; overflow: hidden;"></div>
</div>

@once
    @push('styles')
        <link
            rel="stylesheet"
            href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
            crossorigin=""
        />
    @endpush
    @push('scripts')
        <script
            src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""
            defer
        ></script>
    @endpush
@endonce

<script>
    // No inline initialization here; Alpine handles it via x-data/x-init above
</script>
