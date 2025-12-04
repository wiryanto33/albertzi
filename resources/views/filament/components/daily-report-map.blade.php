@php
    $lat = $record->lat ?? null;            // posisi operator/aktual
    $lng = $record->lng ?? null;
    $jobLat = $record->workOrder?->project?->lat ?? null; // area pekerjaan (project)
    $jobLng = $record->workOrder?->project?->lng ?? null;
    $mapId = 'map-daily-report-' . ($record->id ?? uniqid());
@endphp

<div class="space-y-2">
    <div class="text-sm text-gray-400 space-y-0.5">
        <div>
            Posisi operator
            @if ($lat && $lng)
                ({{ number_format((float) $lat, 6) }}, {{ number_format((float) $lng, 6) }})
            @endif
        </div>
        <div>
            Area pekerjaan
            @if ($jobLat && $jobLng)
                ({{ number_format((float) $jobLat, 6) }}, {{ number_format((float) $jobLng, 6) }})
            @endif
        </div>
    </div>

    @if ((! $lat || ! $lng) && (! $jobLat || ! $jobLng))
        <div class="text-sm text-gray-500">Koordinat belum diisi.</div>
    @else
        <div
            x-data="{
                async init() {
                    const lat = {{ $lat !== null ? (float) $lat : 'null' }};
                    const lng = {{ $lng !== null ? (float) $lng : 'null' }};
                    const jobLat = {{ $jobLat !== null ? (float) $jobLat : 'null' }};
                    const jobLng = {{ $jobLng !== null ? (float) $jobLng : 'null' }};
                    const id = @js($mapId);

                    // Ensure Leaflet assets are loaded dynamically (works in Livewire modals)
                    await new Promise((resolve, reject) => {
                        if (window.L) return resolve();

                        const cssId = 'leaflet-css';
                        if (!document.getElementById(cssId)) {
                            const link = document.createElement('link');
                            link.id = cssId;
                            link.rel = 'stylesheet';
                            link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                            document.head.appendChild(link);
                        }

                        const scriptId = 'leaflet-js';
                        const existing = document.getElementById(scriptId);
                        if (existing) {
                            existing.addEventListener('load', () => resolve(), { once: true });
                            existing.addEventListener('error', reject, { once: true });
                        } else {
                            const script = document.createElement('script');
                            script.id = scriptId;
                            script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                            script.onload = () => resolve();
                            script.onerror = reject;
                            document.head.appendChild(script);
                        }
                    });

                    // Create map (remove previous instance if modal reopened)
                    window.__maps = window.__maps || {};
                    if (window.__maps[id]) {
                        window.__maps[id].remove();
                        delete window.__maps[id];
                    }

                    // Center logic: if both markers exist, fit bounds later; else setView to whichever exists
                    let center = [0, 0], zoom = 2;
                    if (Number.isFinite(lat) && Number.isFinite(lng)) { center = [lat, lng]; zoom = 15; }
                    else if (Number.isFinite(jobLat) && Number.isFinite(jobLng)) { center = [jobLat, jobLng]; zoom = 15; }

                    const map = L.map(id, { zoomControl: true }).setView(center, zoom);
                    window.__maps[id] = map;

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(map);

                    // Custom icons
                    const iconOperator = L.icon({
                        iconUrl: @js(asset('images/markers/operator.svg')),
                        iconSize: [30, 30],
                        iconAnchor: [15, 28],
                        popupAnchor: [0, -24],
                    });
                    const iconJob = L.icon({
                        iconUrl: @js(asset('images/markers/construction.svg')),
                        iconSize: [36, 36],
                        iconAnchor: [18, 34],
                        popupAnchor: [0, -30],
                    });

                    const points = [];
                    if (Number.isFinite(lat) && Number.isFinite(lng)) {
                        L.marker([lat, lng], { icon: iconOperator }).addTo(map).bindTooltip('Operator', {direction: 'top', offset: [0, -8], sticky: true});
                        points.push([lat, lng]);
                    }
                    if (Number.isFinite(jobLat) && Number.isFinite(jobLng)) {
                        L.marker([jobLat, jobLng], { icon: iconJob }).addTo(map).bindTooltip('Area pekerjaan', {direction: 'top', offset: [0, -8], sticky: true});
                        points.push([jobLat, jobLng]);
                    }

                    if (points.length > 1) {
                        L.polyline(points, { color: '#3b82f6', weight: 3, opacity: 0.7 }).addTo(map);
                        map.fitBounds(points, { padding: [28, 28] });
                    }

                    // Fix sizing after modal animation
                    setTimeout(() => map.invalidateSize(), 350);
                }
            }"
            x-init="init()"
            class="rounded-lg overflow-hidden"
            style="height: 320px"
            wire:ignore
        >
            <div id="{{ $mapId }}" class="w-full h-full"></div>
        </div>
    @endif
</div>
