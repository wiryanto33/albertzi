<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'no_wo',
        'deskripsi',
        'tgl_mulai_rencana',
        'tgl_selesai_rencana',
        'status',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'tgl_mulai_rencana' => 'date',
        'tgl_selesai_rencana' => 'date',
        'approved_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function dailyReports()
    {
        return $this->hasMany(DailyReport::class);
    }

    public function incidentReports()
    {
        return $this->hasMany(IncidentReport::class);
    }

    // public function documents()
    // {
    //     return $this->morphMany(Document::class, 'ref');
    // }

    // add guaded
    protected $guarded = ['id'];
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (blank($model->no_wo)) {
                $model->no_wo = self::generateNoWo();
            }
        });
    }

    public static function generateNoWo(): string
    {
        // Format: SP/{running-number}/{roman-month}/{year}, e.g. SP/0001/III/2025
        $year = now()->format('Y');
        $month = (int) now()->format('n');
        $roman = self::monthToRoman($month);

        $last = self::withTrashed()
            ->where('no_wo', 'like', 'SP/%/' . $roman . '/' . $year)
            ->orderByDesc('no_wo')
            ->value('no_wo');

        $next = 1;
        $pattern = '/^SP\/(\d+)\/' . preg_quote($roman, '/') . '\/' . preg_quote($year, '/') . '$/';
        if (is_string($last) && preg_match($pattern, $last, $m)) {
            $next = ((int) $m[1]) + 1;
        }

        return 'SP/' . str_pad((string) $next, 4, '0', STR_PAD_LEFT) . '/' . $roman . '/' . $year;
    }

    private static function monthToRoman(int $month): string
    {
        $map = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];
        return $map[$month] ?? '';
    }
}
