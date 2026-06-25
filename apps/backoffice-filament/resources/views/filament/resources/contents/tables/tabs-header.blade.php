<div class="fi-contents-table-tabs">
    <x-filament::tabs>
        @foreach ($tabs as $key => $tab)
            <x-filament::tabs.item
                :active="$activeTab === $key"
                :badge="$tab['badge']"
                wire:click="$set('activeTab', '{{ $key }}')"
            >
                {{ $tab['label'] }}
            </x-filament::tabs.item>
        @endforeach
    </x-filament::tabs>
</div>
