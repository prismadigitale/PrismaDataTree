@php use Illuminate\Database\Eloquent\Model; @endphp
@php use Filament\Facades\Filament; @endphp
@php use App\Filament\TreePlugin\Components\Tree; @endphp
@props(['record', 'containerKey', 'tree', 'title' => null, 'icon' => null, 'description' => null])
@php
    /** @var $record Model */
    /** @var $containerKey string */
    /** @var $tree Tree */

    $recordKey = $tree->getRecordKey($record);
    $parentKey = $tree->getParentKey($record);

    $children = $record->children;
    $collapsed = $this->getNodeCollapsedState($record);

    $actions = $tree->getActions();
@endphp

<li x-data="{ isDataViewOpen: false }" @class(['filament-tree-row', 'dd-item', 'dd-collapsed' => $collapsed]) data-id="{{ $recordKey }}">
    <div class="dd-row">
        {{-- Box 1: Drag handle --}}
        <div wire:loading.remove.delay wire:target="{{ implode(',', Tree::LOADING_TARGETS) }}" class="dd-handle">
            <x-filament::icon icon="heroicon-m-ellipsis-vertical" class="h-4 w-4 -mr-2" />
            <x-filament::icon icon="heroicon-m-ellipsis-vertical" class="h-4 w-4" />
        </div>

        {{-- Box 2: Expand/Collapse (Restore dd-item-btns for functionality) --}}
        <div class="dd-handle dd-nodrag dd-item-btns" style="cursor: default; padding-left: 0.5rem; padding-right: 0.5rem; border-left: 0;">
            @if ($children->isNotEmpty())
                <button type="button" data-action="expand" @class([
                    'dd-expand text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition',
                    'hidden' => !$collapsed,
                ])>
                    <x-filament::icon icon="heroicon-m-chevron-right" class="h-5 w-5 pointer-events-none" />
                </button>
                <button type="button" data-action="collapse" @class([
                    'dd-collapse text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition',
                    'hidden' => $collapsed,
                ])>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-5 w-5 pointer-events-none" />
                </button>
            @else
                <div class="w-5 h-5"></div>
            @endif
        </div>

        {{-- Box 3: Add Child --}}
        <div class="dd-handle dd-nodrag flex items-center justify-center p-0 m-0" style="cursor: default; padding-left: 0.5rem; padding-right: 0.5rem; border-left: 0;">
            <button type="button" wire:click="mountTreeAction('addChild', '{{ $recordKey }}')"
                class="text-primary-500 hover:text-primary-600 transition flex items-center justify-center w-full h-full">
                <x-filament::icon icon="heroicon-m-plus" class="h-5 w-5" />
            </button>
        </div>

        <div class="dd-content dd-nodrag flex items-center w-full gap-2 pl-2">
            {{-- Node Title/Display --}}
            <div class="flex items-center ml-2">
                @include('filament.tree-plugin.components.tree.item-display', [
                    'record' => $record,
                    'title' => $title,
                    'icon' => $icon,
                    'description' => $description,
                ])
            </div>

            {{-- Data View Button --}}
            <div class="flex-1 flex justify-left ml-2">
                <x-filament::button type="button" x-on:click="isDataViewOpen = !isDataViewOpen" x-bind:color="isDataViewOpen ? 'gray' : 'primary'"
                    size="sm" x-bind:icon="isDataViewOpen ? 'heroicon-o-eye-slash' : 'heroicon-o-eye'" icon-alias="tree::dataview.button">
                    {{ __('filament.tree-plugin.filament-tree.button.dataview') }}
                </x-filament::button>
            </div>
        </div>

        @if (count($actions))
            <div class="fi-tree-actions-ctn dd-nodrag ml-auto">
                @include('filament.tree-plugin.components.actions.index', [
                    'actions' => $actions,
                    'record' => $record,
                ])
            </div>
        @endif
    </div>

    @php
        $dataField = $record->data ?? [];
        if (is_string($dataField)) {
            $dataField = json_decode($dataField, true) ?? [];
        }
    @endphp
    @if (!empty($dataField))
        <div x-show="isDataViewOpen" x-transition x-cloak class="dd-nodrag my-4 mx-6 p-5 bg-white rounded-xl shadow-sm border border-gray-100 text-sm">
            <div class="flex items-center gap-3 mb-4 pb-3 border-b border-gray-100">
                <x-filament::icon icon="heroicon-o-information-circle" class="h-5 w-5 text-primary-500" />
                <span class="text-base font-bold text-gray-800 uppercase tracking-tight">{{ __('filament.tree-plugin.filament-tree.details.title') }}</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach ($dataField as $key => $value)
                    <div class="flex flex-col gap-1 p-3 rounded-lg bg-gray-50/80 border border-gray-100/50">
                        <span
                            class="text-xs font-bold text-gray-500 uppercase tracking-wider text-gray-500">{{ $key }}</span>
                        <span class="text-sm text-gray-900 font-medium break-all prose">
                            {!! is_array($value) || is_object($value) ? json_encode($value, JSON_PRETTY_PRINT) : $value ?? '—' !!}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    @if (count($children))
        @include('filament.tree-plugin.components.tree.list', [
            'records' => $children,
            'containerKey' => $containerKey,
            'tree' => $tree,
            'collapsed' => $collapsed,
        ])
    @endif
    <div class="loading-indicator hidden" wire:loading.class.remove.delay="hidden"
        wire:target="{{ implode(',', Tree::LOADING_TARGETS) }}"></div>
</li>
