<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Assignment;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Filament\Resources\DailyReportResource;
use App\Notifications\FilamentDatabaseNotification;

class DailyReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'work_order_id',
        'assignment_id',
        'tanggal',
        'progress_persen',
        'uraian',
        'jam_kerja_operator',
        'jam_jalan_alat',
        'cuaca',
        'lat',
        'lng',
        'created_by'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'progress_persen' => 'integer',
        'jam_kerja_operator' => 'float',
        'jam_jalan_alat' => 'float',
        'lat' => 'float',
        'lng' => 'float',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function photos()
    {
        return $this->hasMany(DailyReportPhoto::class);
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
        static::created(function (self $report): void {
            $equipment = $report->assignment?->heavyEquipment;
            if (! $equipment) {
                // Still continue to notify admins below
            }

            $increment = (int) round((float) ($report->jam_jalan_alat ?? 0));
            if ($increment !== 0) {
                $equipment->increment('jam_jalan_total', $increment);
                if (config('app.debug')) {
                    Log::info('DailyReport created: increment equipment hour meter', [
                        'report_id' => $report->id,
                        'equipment_id' => $equipment->id,
                        'increment' => $increment,
                    ]);
                }
            }

            // Notify admins (and mechanics) that a new daily report was submitted
            try {
                $recipients = collect();
                try {
                    foreach (['super_admin', 'admin', 'mekanik', 'mechanic', 'pimpinan'] as $roleName) {
                        try {
                            $recipients = $recipients->merge(User::role($roleName)->get() ?? []);
                        } catch (\Throwable $eIgnore) {
                            // silently ignore missing role scope
                        }
                    }
                } catch (\Throwable $eRole) {
                    // If role scope is unavailable, keep recipients empty
                }

                $recipients = $recipients->unique('id');
                if ($recipients->isNotEmpty()) {
                    $data = \Filament\Notifications\Notification::make()
                        ->title('Laporan Harian Baru')
                        ->body('WO ' . ($report->workOrder?->no_wo ?? '—') . ' · Operator ' . ($report->assignment?->operator?->nama ?? '—') . ' · Progres ' . ((int) $report->progress_persen) . '%')
                        ->icon('heroicon-o-newspaper')
                        ->color('success')
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('Lihat')
                                ->button()
                                ->url(DailyReportResource::getUrl('view', ['record' => $report], isAbsolute: true, panel: 'admin')),
                        ])
                        ->getDatabaseMessage();

                    foreach ($recipients as $u) {
                        $u->notify(new FilamentDatabaseNotification($data));
                    }
                }
            } catch (\Throwable $e) {
                // ignore notification failures
            }
        });

        static::updated(function (self $report): void {
            $oldHours = (float) $report->getOriginal('jam_jalan_alat');
            $newHours = (float) $report->jam_jalan_alat;

            $oldAssignmentId = $report->getOriginal('assignment_id');
            $newAssignmentId = $report->assignment_id;

            $oldInc = (int) round($oldHours);
            $newInc = (int) round($newHours);

            // If assignment changed, adjust different equipments
            if ($oldAssignmentId !== $newAssignmentId) {
                if ($oldAssignmentId) {
                    $oldEquipment = Assignment::find($oldAssignmentId)?->heavyEquipment;
                    if ($oldEquipment && $oldInc !== 0) {
                        $oldEquipment->decrement('jam_jalan_total', $oldInc);
                        if (config('app.debug')) {
                            Log::info('DailyReport updated: assignment changed, decrement old equipment', [
                                'report_id' => $report->id,
                                'old_equipment_id' => $oldEquipment->id ?? null,
                                'oldInc' => $oldInc,
                            ]);
                        }
                    }
                }

                $newEquipment = $report->assignment?->heavyEquipment;
                if ($newEquipment && $newInc !== 0) {
                    $newEquipment->increment('jam_jalan_total', $newInc);
                    if (config('app.debug')) {
                        Log::info('DailyReport updated: assignment changed, increment new equipment', [
                            'report_id' => $report->id,
                            'new_equipment_id' => $newEquipment->id ?? null,
                            'newInc' => $newInc,
                        ]);
                    }
                }

                return;
            }

            // Same assignment: adjust delta
            $delta = $newInc - $oldInc;
            if ($delta !== 0) {
                $equipment = $report->assignment?->heavyEquipment;
                if ($equipment) {
                    if ($delta > 0) {
                        $equipment->increment('jam_jalan_total', $delta);
                        if (config('app.debug')) {
                            Log::info('DailyReport updated: delta positive, increment equipment', [
                                'report_id' => $report->id,
                                'equipment_id' => $equipment->id,
                                'delta' => $delta,
                            ]);
                        }
                    } else {
                        $equipment->decrement('jam_jalan_total', abs($delta));
                        if (config('app.debug')) {
                            Log::info('DailyReport updated: delta negative, decrement equipment', [
                                'report_id' => $report->id,
                                'equipment_id' => $equipment->id,
                                'delta' => $delta,
                            ]);
                        }
                    }
                }
            }
        });

        static::deleted(function (self $report): void {
            // Soft delete: subtract the hours
            $equipment = $report->assignment?->heavyEquipment;
            if (! $equipment) {
                return;
            }

            $dec = (int) round((float) ($report->jam_jalan_alat ?? 0));
            if ($dec !== 0) {
                $equipment->decrement('jam_jalan_total', $dec);
                if (config('app.debug')) {
                    Log::info('DailyReport deleted: decrement equipment hour meter', [
                        'report_id' => $report->id,
                        'equipment_id' => $equipment->id,
                        'decrement' => $dec,
                    ]);
                }
            }
        });

        static::restored(function (self $report): void {
            $equipment = $report->assignment?->heavyEquipment;
            if (! $equipment) {
                return;
            }

            $inc = (int) round((float) ($report->jam_jalan_alat ?? 0));
            if ($inc !== 0) {
                $equipment->increment('jam_jalan_total', $inc);
                if (config('app.debug')) {
                    Log::info('DailyReport restored: increment equipment hour meter', [
                        'report_id' => $report->id,
                        'equipment_id' => $equipment->id,
                        'increment' => $inc,
                    ]);
                }
            }
        });
    }
}
