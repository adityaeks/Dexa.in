<x-filament-panels::form wire:submit="updateProfile">
    <div class="space-y-6">
        <div class="w-full">
            <div class="bg-gray-900 rounded-xl p-8">
                {{ $this->form }}
                <div class="fi-form-actions mt-6">
                    <div class="flex flex-row-reverse flex-wrap items-center gap-3 fi-ac">
                        <x-filament::button type="submit">
                            {{ __('filament-edit-profile::default.save') }}
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::form>
