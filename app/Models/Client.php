<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Notifications\FilamentDatabaseNotification;
use App\Filament\Resources\ClientResource;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama',
        'telepon',
        'email',
        'alamat'
    ];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    // add guaded
    protected $guarded = ['id'];
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];

    protected static function booted(): void
    {
        static::created(function (self $client): void {
            try {
                $recipients = collect();
                try {
                    foreach (['super_admin', 'admin', 'pimpinan', 'komandan'] as $roleName) {
                        try {
                            $recipients = $recipients->merge(User::role($roleName)->get() ?? []);
                        } catch (\Throwable $eIgnore) {
                        }
                    }
                } catch (\Throwable $eRole) {
                }

                $recipients = $recipients->unique('id');
                if ($recipients->isNotEmpty()) {
                    $data = \Filament\Notifications\Notification::make()
                        ->title('Client Baru Terdaftar')
                        ->body('Nama: ' . $client->nama . ' · Alamat: ' . ($client->alamat ?? '—'))
                        ->icon('heroicon-o-building-office')
                        ->color('info')
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('Lihat')
                                ->button()
                                ->url(ClientResource::getUrl('edit', ['record' => $client], isAbsolute: true, panel: 'admin')),
                        ])
                        ->getDatabaseMessage();

                    foreach ($recipients as $u) {
                        $u->notify(new FilamentDatabaseNotification($data));
                    }
                }
            } catch (\Throwable $e) {
            }
        });
    }
}
