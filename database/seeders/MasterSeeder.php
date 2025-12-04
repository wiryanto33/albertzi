<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\HeavyEquipment;
use App\Models\Operator;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Client
        $client = Client::create([
            'nama' => 'PT Kontraktor Laut',
            'telepon' => '08123456789',
            'email' => 'info@kontraktorlaut.test',
            'alamat' => 'Jl. Pelabuhan No.1 Surabaya'
        ]);

        // Operator user & record
        $opUser = User::create([
            'name' => 'Operator 1',
            'email' => 'operator1@test.local',
            'password' => bcrypt('password'),
        ]);
        $opUser->assignRole('operator');

        $operator = Operator::create([
            'user_id' => $opUser->id,
            'nama' => 'Sertu Andi',
            'nrp' => '123456',
            'pangkat' => 'Sertu',
            'sertifikasi' => 'Excavator',
            'aktif' => true,
        ]);

        // Removed mechanic seeding since Mechanic model was deleted

        // Heavy Equipment
        HeavyEquipment::create([
            'kode' => 'HE-EXC-01',
            'nama' => 'Excavator Hitachi',
            'tipe' => 'ZX200',
            'nopol' => 'AB1234XZ',
            'tahun' => 2020,
            'status_kesiapan' => 'SIAP',
            'jam_jalan_total' => 500,
            'last_service_at' => now()->subMonth(),
        ]);

        HeavyEquipment::create([
            'kode' => 'HE-DZR-01',
            'nama' => 'Dozer Komatsu',
            'tipe' => 'D85',
            'nopol' => 'AB5678YZ',
            'tahun' => 2019,
            'status_kesiapan' => 'PERBAIKAN',
            'jam_jalan_total' => 1200,
            'last_service_at' => now()->subMonths(3),
        ]);
    }
}
