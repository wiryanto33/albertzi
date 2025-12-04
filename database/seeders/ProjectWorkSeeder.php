<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\HeavyEquipment;
use App\Models\Operator;
use App\Models\Project;
use App\Models\WorkOrder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectWorkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientId = \App\Models\Client::first()->id;
        $project = Project::create([
            'nama' => 'Pembangunan Dermaga Koarmada',
            'client_id' => $clientId,
            'lokasi_nama' => 'Koarmada II, Surabaya',
            'lat' => -7.219,
            'lng' => 112.739,
            'tanggal_mulai' => now()->subDays(10),
            'tanggal_selesai' => now()->addDays(30),
            'status' => 'BERJALAN'
        ]);

        $wo = WorkOrder::create([
            'project_id' => $project->id,
            'no_wo' => 'WO-2025-001',
            'deskripsi' => 'Penggalian dasar dermaga',
            'tgl_mulai_rencana' => now()->subDays(10),
            'tgl_selesai_rencana' => now()->addDays(20),
            'status' => 'AKTIF',
        ]);

        $operator = Operator ::first();
        $equipment = HeavyEquipment::first();

        Assignment::create([
            'work_order_id' => $wo->id,
            'heavy_equipment_id' => $equipment->id,
            'operator_id' => $operator->id,
            'shift' => 'Pagi',
            'tgl_mulai' => now()->subDays(5),
            'tgl_selesai' => now()->addDays(15),
            'status' => 'AKTIF'
        ]);
    }
}
