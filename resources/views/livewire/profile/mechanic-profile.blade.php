<x-filament-breezy::grid-section md=2 title="Profil Mekanik" description="Lengkapi atau perbarui data mekanik Anda.">
    <x-filament::card>
        <form wire:submit.prevent="submit" class="space-y-6">
            {{ $this->form }}

            <div class="text-right">
                <x-filament::button type="submit" form="submit">Simpan</x-filament::button>
            </div>
        </form>
    </x-filament::card>
</x-filament-breezy::grid-section>

