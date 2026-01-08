<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HeavyEquipment extends Model
{
    use HasFactory, SoftDeletes;

    // Explicit table name to avoid any pluralization mismatch
    protected $table = 'heavy_equipments';

    protected $fillable = [
        'kode',
        'nama',
        'tipe',
        'nopol',
        'tahun',
        'status_kesiapan',
        'jam_jalan_total',
        'last_service_at'
    ];

    protected $casts = [
        'last_service_at' => 'date',
    ];



    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function jamJalan()
    {
        return $this->hasMany(DailyReport::class, 'heavy_equipment_id', 'id')->sum('jam_jalan_alat');
    }
    // add guaded
    protected $guarded = ['id'];
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];
}
