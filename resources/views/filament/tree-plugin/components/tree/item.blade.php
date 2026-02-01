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

<li @class(['filament-tree-row', 'dd-item', 'dd-collapsed' => $collapsed]) data-id="{{ $recordKey }}">
    <div class="dd-row">
        <div wire:loading.remove.delay wire:target="{{ implode(',', Tree::LOADING_TARGETS) }}" class="dd-handle">
            <x-filament::icon icon="heroicon-m-ellipsis-vertical" class="h-4 w-4 -mr-2" />
            <x-filament::icon icon="heroicon-m-ellipsis-vertical" class="h-4 w-4" />
        </div>

        <div class="dd-content dd-nodrag flex items-center w-full gap-4 ml-2">
            <div class="flex items-center gap-3">
                @include('filament.tree-plugin.components.tree.item-display', [
                    'record' => $record,
                    'title' => $title,
                    'icon' => $icon,
                    'description' => $description,
                ])

                @if ($children->isNotEmpty())
                    <div class="dd-item-btns flex items-center gap-1">
                        <button type="button" data-action="expand" @class([
                            'dd-expand items-center justify-center h-8 w-8 rounded-lg text-gray-400 hover:bg-gray-100 transition',
                            'hidden' => !$collapsed,
                        ])>
                            <x-filament::icon icon="heroicon-o-chevron-down" class="h-4 w-4 pointer-events-none" />
                        </button>
                        <button type="button" data-action="collapse" @class([
                            'dd-collapse items-center justify-center h-8 w-8 rounded-lg text-gray-400 hover:bg-gray-100 transition',
                            'hidden' => $collapsed,
                        ])>
                            <x-filament::icon icon="heroicon-o-chevron-up" class="h-4 w-4 pointer-events-none" />
                        </button>
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-2">
                <x-filament::icon-button icon="heroicon-o-plus-circle" color="gray" size="sm"
                    wire:click="mountTreeAction('addChild', '{{ $recordKey }}')" />
            </div>

            @php
                $isDataViewOpen = $this->isDataViewVisible($recordKey);
            @endphp
            <div class="flex-1 flex justify-left">
                <x-filament::button type="button" wire:click="toggleDataView('{{ $recordKey }}')" :color="$isDataViewOpen ? 'gray' : 'primary'"
                    size="sm" :icon="$isDataViewOpen ? 'heroicon-o-eye-slash' : 'heroicon-o-eye'" icon-alias="tree::dataview.button">
                    DataView
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

    @if ($this->isDataViewVisible($recordKey))
        @php
            $dataField = $record->data ?? [];
            if (is_string($dataField)) {
                $dataField = json_decode($dataField, true) ?? [];
            }
        @endphp
        @if (!empty($dataField))
            <div class="dd-nodrag my-4 mx-6 p-5 bg-white rounded-xl shadow-sm border border-gray-100 text-sm">
                <div class="flex items-center gap-3 mb-4 pb-3 border-b border-gray-100">
                    <x-filament::icon icon="heroicon-o-information-circle" class="h-5 w-5 text-primary-500" />
                    <span class="text-base font-bold text-gray-800 uppercase tracking-tight">Record Data Details</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach ($dataField as $key => $value)
                        <div class="flex flex-col gap-1 p-3 rounded-lg bg-gray-50/80 border border-gray-100/50">
                            <span
                                class="text-xs font-bold text-gray-500 uppercase tracking-wider text-gray-500">{{ $key }}</span>
                            <span class="text-sm text-gray-900 font-medium break-all">
                                {{ is_array($value) || is_object($value) ? json_encode($value, JSON_PRETTY_PRINT) : $value ?? '—' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
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
