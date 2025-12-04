@php
    $mapId = 'monitoring-map';
    $data = $items ?? [];
@endphp

<x-filament::section class="fi-section-full-width">
    <x-slot name="heading">Peta Pekerjaan Berjalan</x-slot>

    <div class="-mx-4 md:-mx-6 lg:-mx-8 -mb-6">
        <iframe
            src="{{ route('monitoring.map-embed') }}"
            style="width: 100%; height: 68vh; min-height: 520px; border: 0; overflow: hidden; background: transparent;"
        ></iframe>
    </div>

    <div class="mt-3 text-xs text-gray-500">
        Keterangan: hanya menampilkan WO berstatus <span class="font-semibold">AKTIF</span> (Berjalan).
    </div>
</x-filament::section>

<style>
    /* Optional: Remove section padding completely for truly full width */
    .fi-section-full-width .fi-section-content-ctn { padding: 0 !important; }
    .fi-section-full-width .fi-section-header-ctn { padding: 1.5rem 1.5rem 1rem 1.5rem; }
</style>
