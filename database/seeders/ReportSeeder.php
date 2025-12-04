<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\DailyReport;
use App\Models\DailyReportPhoto;
use App\Models\EquipmentInspection;
use App\Models\FuelLog;
use App\Models\IncidentReport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $assignment = Assignment::first();
        $wo = $assignment->workOrder;

        $operatorUser = $assignment->operator->user;

        // Daily Report
        $report = DailyReport::create([
            'work_order_id' => $wo->id,
            'assignment_id' => $assignment->id,
            'tanggal' => now()->subDay(),
            'progress_persen' => 25,
            'uraian' => 'Penggalian 50 meter selesai',
            'jam_kerja_operator' => 8,
            'jam_jalan_alat' => 7.5,
            'cuaca' => 'Cerah',
            'lat' => -7.219,
            'lng' => 112.739,
            'created_by' => $operatorUser->id,
        ]);

        DailyReportPhoto::create([
            'daily_report_id' => $report->id,
            'path' => 'photos/report1.jpg',
            'caption' => 'Hasil galian hari pertama'
        ]);

        // Inspection
        EquipmentInspection::create([
            'heavy_equipment_id' => $assignment->heavy_equipment_id,
            'inspected_by' => null,
            'tanggal' => now()->subDays(2),
            'checklist_json' => json_encode(['oli' => 'OK', 'rem' => 'OK', 'lampu' => 'Rusak']),
            'status_layak' => 'LAYAK',
            'catatan' => 'Perlu ganti lampu depan',
        ]);

        // Fuel Log
        FuelLog::create([
            'assignment_id' => $assignment->id,
            'tanggal' => now()->subDay(),
            'liter' => 50,
            'odometer_jam_awal' => 500,
            'odometer_jam_akhir' => 507,
            'bukti_foto' => 'photos/bbm.jpg',
        ]);

        // Incident
        IncidentReport::create([
            'work_order_id' => $wo->id,
            'assignment_id' => $assignment->id,
            'tanggal' => now(),
            'kategori' => 'Kerusakan',
            'deskripsi' => 'Rantai excavator lepas',
            'severity' => 'MED',
            'foto_bukti' => 'photos/incident.jpg',
        ]);
    }
}
