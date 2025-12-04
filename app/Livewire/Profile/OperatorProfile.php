<?php

namespace App\Livewire\Profile;

use App\Models\Operator;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Jeffgreco13\FilamentBreezy\Livewire\MyProfileComponent;

class OperatorProfile extends MyProfileComponent
{
    protected string $view = 'livewire.profile.operator-profile';

    public ?array $data = [];

    public ?Operator $record = null;

    public static $sort = 12;

    public function mount(): void
    {
        $user = Filament::getCurrentPanel()->auth()->user();
        $this->record = Operator::firstOrNew(['user_id' => $user->id]);
        $this->form->fill($this->record->only(['nama','nrp','pangkat','sertifikasi','aktif']));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')->label('Nama')->required(),
                Forms\Components\TextInput::make('nrp')->label('NRP')->maxLength(50),
                Forms\Components\TextInput::make('pangkat')->label('Pangkat')->maxLength(100),
                Forms\Components\TextInput::make('sertifikasi')->label('Sertifikasi')->maxLength(150),
                Forms\Components\Toggle::make('aktif')->label('Aktif')->default(true),
            ])
            ->columns(2)
            ->statePath('data');
    }

    public function submit(): void
    {
        $user = Filament::getCurrentPanel()->auth()->user();
        $this->record->fill(array_merge($this->form->getState(), ['user_id' => $user->id]))->save();

        Notification::make()->success()->title('Profil Operator diperbarui.')->send();
    }

    public static function canView(): bool
    {
        $user = Filament::getCurrentPanel()->auth()->user();
        if (! $user) return false;
        // Tampilkan jika user punya role operator atau punya record operator
        try {
            if (method_exists($user, 'hasRole') && $user->hasRole(['operator', 'super_admin', 'Komandan', 'staff_minlog'])) {
                return true;
            }
        } catch (\Throwable $e) {}

        return Operator::where('user_id', $user->id)->exists();
    }
}

