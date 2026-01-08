<?php

use Illuminate\Support\Facades\Route;
use App\Models\WorkOrder;
use App\Models\Assignment;
use Barryvdh\DomPDF\Facade\Pdf;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::middleware(['web', 'auth'])
    ->get('/monitoring-map-embed', function () {
        $items = WorkOrder::query()
            ->with(['project:id,nama,lat,lng'])
            ->where('status', 'AKTIF')
            ->get(['id', 'project_id', 'no_wo', 'status'])
            ->map(function ($wo) {
                return [
                    'id' => $wo->id,
                    'wo' => $wo->no_wo,
                    'status' => $wo->status,
                    'project' => [
                        'name' => $wo->project->nama ?? null,
                        'lat' => $wo->project->lat ?? null,
                        'lng' => $wo->project->lng ?? null,
                    ],
                ];
            })
            ->values()
            ->all();

        return view('monitoring.map-embed', [
            'items' => $items,
        ]);
    })
    ->name('monitoring.map-embed');

Route::middleware(['web', 'auth'])
    ->get('/assignments/{assignment}/surat-tugas', function (Assignment $assignment) {
        // Load relationships - changed 'operator' to 'user'
        $assignment->loadMissing(['workOrder.project.client', 'heavyEquipment', 'user']);

        $pdf = Pdf::loadView('pdf.surat-tugas', [
            'assignment' => [
                'tgl_mulai' => $assignment->tgl_mulai,
                'tgl_selesai' => $assignment->tgl_selesai,
                'status' => $assignment->status,
            ],
            'workOrder' => $assignment->workOrder,
            'project' => optional($assignment->workOrder)->project,
            'client' => optional(optional($assignment->workOrder)->project)->client,
            'heavyEquipment' => $assignment->heavyEquipment,
            'operator' => $assignment->user, // Changed from 'operator' to 'user'
        ])->setPaper('a4', 'portrait');

        $raw = (string) ($assignment->workOrder->no_wo ?? 'surat');
        $safe = preg_replace('/[\\\\\/\:\*\?\"\<\>\|]+/', '-', $raw) ?: 'surat';
        $filename = 'Surat_Tugas_' . $safe . '.pdf';
        return $pdf->download($filename);
    })
    ->name('assignments.surat-tugas');
