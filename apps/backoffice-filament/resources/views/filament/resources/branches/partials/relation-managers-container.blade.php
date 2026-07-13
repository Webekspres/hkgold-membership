<div class="fi-branch-relation-managers">
    <div class="fi-contents-table-tabs" style="margin-bottom: 1rem;">
        <x-filament::tabs>
            @foreach (\App\Filament\Resources\Branches\Pages\ViewBranch::getRelationManagerTabDefinitions() as $key => $tab)
                <x-filament::tabs.item
                    :active="$activeManager === $key"
                    wire:click="switchRelationTab('{{ $key }}')"
                >
                    {{ $tab['label'] }}
                </x-filament::tabs.item>
            @endforeach
        </x-filament::tabs>
    </div>

    @foreach ($mountedManagers as $managerKey)
        @php
            $managerClass = $managers[$managerKey] ?? null;
            $isActive = $activeManager === $managerKey;
        @endphp

        @if (filled($managerClass))
            <div
                @if (! $isActive) style="display: none;" @endif
                wire:key="branch-relation-{{ $managerKey }}"
            >
                @livewire(
                    $managerClass,
                    [
                        'ownerRecord' => $ownerRecord,
                        'pageClass' => $pageClass,
                    ],
                    key('branch-relation-' . $managerKey)
                )
            </div>
        @endif
    @endforeach
</div>
