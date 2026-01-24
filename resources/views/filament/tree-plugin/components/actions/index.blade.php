@props([
    'actions',
    'alignment' => null,
    'record' => null,
    // 'wrap' => false,
])

@php
    use Filament\Support\Enums\Alignment;

    $actions = array_filter(
        $actions,
        function ($action) use ($record): bool {

            if (! $action instanceof \App\Filament\TreePlugin\Actions\Modal\Action) {
                $action->record($record);
            }
            
            return $action->isVisible();
        },
    );
@endphp

<x-filament::actions :actions="$actions" :alignment="$alignment" :fullWidth="true" class="fi-tree-actions"/>