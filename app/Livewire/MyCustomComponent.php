<?php

namespace App\Livewire;

use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Jeffgreco13\FilamentBreezy\Livewire\PersonalInfo;

class MyCustomComponent extends PersonalInfo
{
    // Simpan juga name & email selain field tambahan
    public array $only = ['name', 'email', 'pangkat', 'korps', 'nrp', 'satuan'];

    protected function getProfileFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Informasi Profil')
                ->schema([
                    FileUpload::make('avatar_url')
                        ->image()
                        ->disk('public'),
                    Forms\Components\TextInput::make('name')
                        ->label('Nama')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('pangkat')
                        ->label('Pangkat')
                        ->required()
                        ->maxLength(100),

                    Forms\Components\TextInput::make('korps')
                        ->label('Korps')
                        ->required()
                        ->maxLength(100),

                    Forms\Components\TextInput::make('nrp')
                        ->label('NRP')
                        ->required()
                        ->maxLength(50),

                    Forms\Components\TextInput::make('satuan')
                        ->label('Satuan')
                        ->required()
                        ->maxLength(100),
                ]),
        ];
    }
}
