<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama',
        'client_id',
        'lokasi_nama',
        'lat',
        'lng',
        'tanggal_mulai',
        'tanggal_selesai',
        'status'
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }

    // public function documents()
    // {
    //     return $this->morphMany(Document::class, 'ref');
    // }
    // add guaded
    protected $guarded = ['id'];
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];
}
