<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'tanggal',
        'liter',
        'odometer_jam_awal',
        'odometer_jam_akhir',
        'bukti_foto'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'liter' => 'decimal:2',
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }
    // add guaded
    protected $guarded = ['id'];
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];
}
