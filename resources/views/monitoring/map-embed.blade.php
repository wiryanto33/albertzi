<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Peta Pekerjaan Berjalan</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <style>
        html, body { margin: 0; padding: 0; height: 100%; background: transparent; }
        #map { height: 100%; width: 100%; }
        .leaflet-container { background: #0b0f19; }
    </style>
</head>
<body>
<div id="map"></div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
    (function () {
        const items = @json($items);
        const center = [ -2.2, 117.0 ];
        const map = L.map('map', { zoomControl: true }).setView(center, 5);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const iconRun = L.icon({
            iconUrl: '{{ asset('images/markers/construction.svg') }}',
            iconSize: [34, 34],
            iconAnchor: [17, 32],
            popupAnchor: [0, -28],
        });

        const points = [];
        for (const it of items) {
            const a = parseFloat(it?.project?.lat);
            const b = parseFloat(it?.project?.lng);
            if (!Number.isFinite(a) || !Number.isFinite(b)) continue;
            points.push([a, b]);
            const title = [it.wo, it?.project?.name].filter(Boolean).join(' · ');
            L.marker([a, b], { icon: iconRun }).addTo(map)
                .bindPopup('<div style=\'min-width:220px\'><strong>' + title + '</strong><br>Status: ' + it.status + '</div>');
        }

        if (points.length > 1) {
            map.fitBounds(points, { padding: [30, 30] });
        } else if (points.length === 1) {
            map.setView(points[0], 12);
        }
    })();
</script>
</body>
</html>

