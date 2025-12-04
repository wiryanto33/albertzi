@php
    $mapId = 'leaflet-map-two-' . $getId();
    $latPath = $generateRelativeStatePath('lat');
    $lngPath = $generateRelativeStatePath('lng');
    $projLatPath = $generateRelativeStatePath('project_lat');
    $projLngPath = $generateRelativeStatePath('project_lng');
    $opNamePath = $generateRelativeStatePath('operator_nama');
@endphp

<div
    class="fi-section-content"
    wire:ignore
    x-data="{
        mapId: @js($mapId),
        lat: $wire.$entangle('{{ $latPath }}').live,
        lng: $wire.$entangle('{{ $lngPath }}').live,
        projLat: $wire.$entangle('{{ $projLatPath }}').live,
        projLng: $wire.$entangle('{{ $projLngPath }}').live,
        opName: $wire.$entangle('{{ $opNamePath }}').live,
        map: null,
        markers: { operator: null, job: null },
        icons: { operator: null, job: null },
        getIcon(name) {
            if (this.icons[name]) return this.icons[name];
            if (name === 'job') {
                this.icons.job = L.icon({
                    iconUrl: @js(asset('images/markers/construction.svg')),
                    iconSize: [36, 36],
                    iconAnchor: [18, 34],
                    popupAnchor: [0, -30],
                });
                return this.icons.job;
            }
            this.icons.operator = L.icon({
                iconUrl: @js(asset('images/markers/operator.svg')),
                iconSize: [30, 30],
                iconAnchor: [15, 28],
                popupAnchor: [0, -24],
            });
            return this.icons.operator;
        },
        parseLocaleFloat(v) {
            if (v === null || v === undefined) return NaN;
            if (typeof v === 'number') return v;
            let s = String(v).trim();
            if (s.includes(',') && !s.includes('.')) s = s.replace(',', '.');
            s = s.replace(/\.(?=\d{3}(\D|$))/g, '');
            s = s.replace(/,(?=\d{3}(\D|$))/g, '');
            return parseFloat(s);
        },
        ready() { return typeof L !== 'undefined'; },
        anyValid() { return Number.isFinite(this.parseLocaleFloat(this.lat)) && Number.isFinite(this.parseLocaleFloat(this.lng)) || Number.isFinite(this.parseLocaleFloat(this.projLat)) && Number.isFinite(this.parseLocaleFloat(this.projLng)); },
        ensureMap() {
            if (!this.ready()) return;
            const el = document.getElementById(this.mapId);
            // Recreate map if missing or bound to old container
            if (!this.map || !this.map._container || this.map._container !== el) {
                if (this.map) { try { this.map.remove(); } catch (e) {} }
                const jbA = this.parseLocaleFloat(this.projLat), jbB = this.parseLocaleFloat(this.projLng);
                const opA = this.parseLocaleFloat(this.lat), opB = this.parseLocaleFloat(this.lng);
                let center = [0, 0], zoom = 2;
                if (Number.isFinite(jbA) && Number.isFinite(jbB)) { center = [jbA, jbB]; zoom = 13; }
                else if (Number.isFinite(opA) && Number.isFinite(opB)) { center = [opA, opB]; zoom = 13; }
                this.map = L.map(this.mapId, { center, zoom, scrollWheelZoom: false });
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(this.map);
                setTimeout(() => this.map.invalidateSize(), 0);
            }
        },
        upsertOperator() {
            const a = this.parseLocaleFloat(this.lat), b = this.parseLocaleFloat(this.lng);
            if (!Number.isFinite(a) || !Number.isFinite(b)) return;
            if (!this.markers.operator) {
                this.markers.operator = L.marker([a, b], { draggable: true, icon: this.getIcon('operator') }).addTo(this.map);
                if (this.opName) this.markers.operator.bindTooltip(this.opName, {direction: 'top', offset: [0, -8], sticky: true});
                this.markers.operator.on('dragend', (e) => {
                    const pos = e.target.getLatLng();
                    this.lat = Number(pos.lat).toFixed(6);
                    this.lng = Number(pos.lng).toFixed(6);
                });
            } else {
                this.markers.operator.setLatLng([a, b]);
                if (this.opName) this.markers.operator.bindTooltip(this.opName, {direction: 'top', offset: [0, -8], sticky: true});
            }
        },
        upsertJob() {
            const a = this.parseLocaleFloat(this.projLat), b = this.parseLocaleFloat(this.projLng);
            if (!Number.isFinite(a) || !Number.isFinite(b)) return;
            if (!this.markers.job) {
                this.markers.job = L.marker([a, b], { draggable: false, icon: this.getIcon('job') }).addTo(this.map);
            } else {
                this.markers.job.setLatLng([a, b]);
            }
        },
        fit() {
            const points = [];
            const opA = this.parseLocaleFloat(this.lat), opB = this.parseLocaleFloat(this.lng);
            const jbA = this.parseLocaleFloat(this.projLat), jbB = this.parseLocaleFloat(this.projLng);
            if (Number.isFinite(opA) && Number.isFinite(opB)) points.push([opA, opB]);
            if (Number.isFinite(jbA) && Number.isFinite(jbB)) points.push([jbA, jbB]);
            if (points.length === 0) return;
            if (points.length === 1) {
                this.map.setView(points[0], 14);
            } else {
                this.map.fitBounds(points, { padding: [30, 30] });
            }
            this.map.invalidateSize();
        },
        attachClickToSetOperator() {
            if (!this.map) return;
            this.map.on('click', (e) => {
                const lat = Number(e.latlng.lat).toFixed(6);
                const lng = Number(e.latlng.lng).toFixed(6);
                this.lat = lat; this.lng = lng;
            });
        },
    }"
    x-init="
        const boot = () => { if (!ready()) { setTimeout(boot, 50); return; } ensureMap(); upsertOperator(); upsertJob(); fit(); attachClickToSetOperator(); };
        boot();
        $watch('lat', () => { ensureMap(); upsertOperator(); fit(); });
        $watch('lng', () => { ensureMap(); upsertOperator(); fit(); });
        $watch('projLat', () => { ensureMap(); upsertJob(); fit(); });
        $watch('projLng', () => { ensureMap(); upsertJob(); fit(); });
        $watch('opName', () => { if (markers.operator) markers.operator.bindTooltip(opName || 'Operator', {direction: 'top', offset: [0, -8], sticky: true}); });
        window.addEventListener('resize', () => { if (this.map) this.map.invalidateSize(); });
    "
>
    <div class="text-sm text-gray-600 mb-2">Peta: posisi operator & lokasi kerja</div>
    <div id="{{ $mapId }}" style="height: 340px; border-radius: 0.5rem; overflow: hidden;"></div>
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
