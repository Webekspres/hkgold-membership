<div class="fi-branch-relation-managers">
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
