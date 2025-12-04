<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyReportPhoto extends Model
{
    use HasFactory;

    protected $fillable = ['daily_report_id', 'path', 'caption'];

    public function dailyReport()
    {
        return $this->belongsTo(DailyReport::class);
    }
    // add guaded
    protected $guarded = ['id'];
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];
}
