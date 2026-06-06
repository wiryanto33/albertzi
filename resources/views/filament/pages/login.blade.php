<x-filament-panels::page.simple>
    @if (filament()->hasRegistration())
        <x-slot name="subheading">
            {{ __('filament-panels::pages/auth/login.actions.register.before') }}

            {{ $this->registerAction }}
        </x-slot>
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

    @php
        $settings = null;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $settings = app(\App\Settings\KaidoSetting::class);
            }
        } catch (\Exception $e) {
            // Ignore if settings table doesn't exist
        }
    @endphp

    @if($settings && $settings->auth_title)
        <h2 class="text-2xl font-bold tracking-tight text-center text-gray-950 dark:text-white mb-4">
            {{ $settings->auth_title }}
        </h2>
    @endif

    <x-filament-panels::form id="form" wire:submit="authenticate">
        {{ $this->form }}

        <x-filament-panels::form.actions :actions="$this->getCachedFormActions()" :full-width="$this->hasFullWidthFormActions()" />
    </x-filament-panels::form>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
</x-filament-panels::page.simple>
