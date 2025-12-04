<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Filament\Resources\AssignmentResource;
use App\Notifications\FilamentDatabaseNotification;

class Assignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'work_order_id',
        'heavy_equipment_id',
        'operator_id',
        'shift',
        'tgl_mulai',
        'tgl_selesai',
        'status'
    ];

    protected $casts = [
        'tgl_mulai' => 'date',
        'tgl_selesai' => 'date',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function heavyEquipment()
    {
        return $this->belongsTo(HeavyEquipment::class);
    }

    public function operator()
    {
        return $this->belongsTo(Operator::class);
    }

    public function dailyReports()
    {
        return $this->hasMany(DailyReport::class);
    }

    public function fuelLogs()
    {
        return $this->hasMany(FuelLog::class);
    }

    // add guaded
    protected $guarded = ['id'];
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];

    protected static function booted(): void
    {
        static::created(function (self $assignment): void {
            try {
                $user = $assignment->operator?->user;
                if ($user) {
                    $data = \Filament\Notifications\Notification::make()
                        ->title('Penugasan Baru')
                        ->body('WO ' . ($assignment->workOrder->no_wo ?? '—') . ' · Alat ' . ($assignment->heavyEquipment->nama ?? '—'))
                        ->icon('heroicon-o-briefcase')
                        ->color('info')
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('Lihat')
                                ->button()
                                ->url(AssignmentResource::getUrl('view', ['record' => $assignment], isAbsolute: true, panel: 'admin')),
                        ])
                        ->getDatabaseMessage();

                    $user->notify(new FilamentDatabaseNotification($data));
                }
            } catch (\Throwable $e) {
                // fail silently to avoid breaking creation
            }
        });

        static::updated(function (self $assignment): void {
            try {
                if ($assignment->wasChanged('operator_id')) {
                    $user = $assignment->operator?->user;
                    if ($user) {
                        $data = \Filament\Notifications\Notification::make()
                            ->title('Penugasan Diperbarui')
                            ->body('Anda ditugaskan pada WO ' . ($assignment->workOrder->no_wo ?? '—') . ' · Alat ' . ($assignment->heavyEquipment->nama ?? '—'))
                            ->icon('heroicon-o-clipboard-document-check')
                            ->color('warning')
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('Lihat')
                                    ->button()
                                    ->url(AssignmentResource::getUrl('view', ['record' => $assignment], isAbsolute: true, panel: 'admin')),
                            ])
                            ->getDatabaseMessage();

                        $user->notify(new FilamentDatabaseNotification($data));
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }
        });
    }
}
