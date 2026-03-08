<style>
    .tree-search-highlight-bg {
        background-color: #fce7f3 !important;
        /* light pink/yellow fallback */
        background-color: #fef08a !important;
        box-shadow: 0 0 0 2px #eab308 !important;
        border-radius: 4px !important;
        padding: 2px 6px !important;
        color: #000 !important;
        transition: all 0.3s ease !important;
        display: inline-block;
    }

    .dark .tree-search-highlight-bg {
        background-color: #713f12 !important;
        box-shadow: 0 0 0 2px #ca8a04 !important;
        color: #fff !important;
    }

    /* Custom scrollbar for deep trees to not hide search pane */
    .filament-tree-list {
        scroll-behavior: smooth;
    }
</style>

<div x-data="treeSearch()" class="px-4 py-4 mb-4  border-gray-200 dark:border-gray-800">
    <x-filament::section compact collapsible collapsed>
        <x-slot name="heading">
            <div class="flex items-center gap-2 text-sm">
                <x-filament::icon icon="heroicon-o-magnifying-glass" class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                <span class="font-bold">Search</span>
            </div>
        </x-slot>

        <div class="pt-2">
            <!-- Search Input -->
            <div style="position: relative; margin-bottom: 1rem;">
                <input type="text" x-model="searchText" @keydown.enter="findNext()" placeholder="Search terms..."
                    style="width: 100%; padding: 0.5rem 2rem 0.5rem 0.75rem; border-width: 1px; border-radius: 0.5rem;"
                    class="text-sm border-gray-300 shadow-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                <button x-show="searchText.length > 0" @click="searchText = ''; findMatches()"
                    style="display: none; position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%);"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                    <x-filament::icon icon="heroicon-m-x-mark" class="w-4 h-4" />
                </button>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-3 mb-4">
                <x-filament::button color="gray" size="sm" @click="findPrevious()"
                    class="flex-1 justify-center">Prev</x-filament::button>
                <x-filament::button color="primary" size="sm" @click="findNext()"
                    class="flex-1 justify-center">Next</x-filament::button>
            </div>

            <!-- Match Status indicator -->
            <div class="text-[11px] font-medium text-gray-500 dark:text-gray-400 flex items-center justify-between mb-4">
                <span
                    x-text="matches.length > 0 ? `Match ${currentIndex + 1} of ${matches.length}` : (searchText ? 'No matches' : 'Ready')"></span>
                <span x-show="matches.length > 0" class="flex gap-1.5 items-center" style="display: none;">
                    <span class="w-2 h-2 rounded-full bg-primary-500 inline-block animate-pulse"></span>
                </span>
            </div>

            <!-- Advanced Options Accordion -->
            <div x-data="{ advancedOpen: false }" class="pt-4 mt-2 border-t border-gray-100 dark:border-gray-800">
                <button @click="advancedOpen = !advancedOpen" type="button"
                    class="flex items-center justify-between w-full text-xs font-semibold text-gray-600 transition dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">
                    <span class="uppercase tracking-wider">Advanced</span>
                    <x-filament::icon x-show="!advancedOpen" icon="heroicon-m-chevron-down" class="w-4 h-4" />
                    <x-filament::icon x-show="advancedOpen" style="display: none;" icon="heroicon-m-chevron-up"
                        class="w-4 h-4" />
                </button>

                <div x-show="advancedOpen" style="display: none;" x-transition class="mt-4">

                    <!-- Scope -->
                    <div class="mb-5">
                        <span
                            class="block text-[11px] font-bold text-gray-400 dark:text-gray-500 mb-2 uppercase tracking-wide">Scope</span>
                        <div class="flex flex-col gap-2.5">
                            <label
                                class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300 cursor-pointer hover:text-primary-600 transition-colors">
                                <input type="radio" x-model="searchScope" value="full"
                                    class="w-4 h-4 text-primary-600 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-600">
                                <span>Titles + Details</span>
                            </label>
                            <label
                                class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300 cursor-pointer hover:text-primary-600 transition-colors">
                                <input type="radio" x-model="searchScope" value="titles"
                                    class="w-4 h-4 text-primary-600 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-600">
                                <span>Titles only</span>
                            </label>
                        </div>
                    </div>

                    <!-- Method -->
                    <div class="mb-2">
                        <span
                            class="block text-[11px] font-bold text-gray-400 dark:text-gray-500 mb-2 uppercase tracking-wide">Method</span>
                        <div class="flex flex-col gap-2.5">
                            <label
                                class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300 cursor-pointer hover:text-primary-600 transition-colors">
                                <input type="radio" x-model="searchMethod" value="keywords"
                                    class="w-4 h-4 text-primary-600 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-600">
                                <span>Key words</span>
                            </label>
                            <label
                                class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300 cursor-pointer hover:text-primary-600 transition-colors">
                                <input type="radio" x-model="searchMethod" value="full_words"
                                    class="w-4 h-4 text-primary-600 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-600">
                                <span>Exact full words</span>
                            </label>
                            <label
                                class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300 cursor-pointer hover:text-primary-600 transition-colors">
                                <input type="radio" x-model="searchMethod" value="phrase"
                                    class="w-4 h-4 text-primary-600 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-600">
                                <span>Exact phrase</span>
                            </label>
                            <label
                                class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300 cursor-pointer hover:text-primary-600 transition-colors">
                                <input type="radio" x-model="searchMethod" value="regex"
                                    class="w-4 h-4 text-primary-600 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-600">
                                <span>Regular expression</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('treeSearch', () => ({
            searchText: '',
            searchScope: 'full',
            searchMethod: 'keywords',
            currentIndex: -1,
            matches: [],

            init() {
                this.$watch('searchText', () => this.findMatches());
                this.$watch('searchScope', () => this.findMatches());
                this.$watch('searchMethod', () => this.findMatches());
            },

            clearHighlights() {
                const highlightedNodes = document.querySelectorAll('.filament-tree-row.ring-2');
                highlightedNodes.forEach(el => {
                    el.classList.remove('ring-2', 'ring-primary-500', 'bg-primary-50',
                        'dark:bg-primary-900/20');
                });

                const bgHighlights = document.querySelectorAll('.tree-search-highlight-bg');
                bgHighlights.forEach(el => {
                    el.classList.remove('tree-search-highlight-bg');
                });
            },

            findMatches() {
                this.clearHighlights();
                if (!this.searchText.trim()) {
                    this.matches = [];
                    this.currentIndex = -1;
                    return;
                }

                this.matches = [];
                const searchStr = this.searchText.toLowerCase().trim();
                let regex = null;

                if (this.searchMethod === 'regex') {
                    try {
                        regex = new RegExp(this.searchText, 'i');
                    } catch (e) {
                        return; // Invalid regex
                    }
                } else if (this.searchMethod === 'full_words') {
                    const escaped = this.searchText.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                    regex = new RegExp('\\b' + escaped + '\\b', 'i');
                }

                const items = document.querySelectorAll('.filament-tree-row');

                items.forEach((item) => {
                    let selfTitle, selfDesc, dataviewEls;
                    try {
                        selfTitle = item.querySelector(':scope > .dd-row .item-title');
                        selfDesc = item.querySelector(':scope > .dd-row .item-description');
                        dataviewEls = Array.from(item.children).filter(child => child
                            .classList.contains('dd-nodrag') && child.classList
                            .contains('my-4') && child.getAttribute('x-show') ===
                            'isDataViewOpen');
                    } catch (e) {
                        selfTitle = item.querySelector('.item-title');
                        selfDesc = item.querySelector('.item-description');
                        dataviewEls = [];
                    }

                    // 1. Check title
                    let titleContent = (selfTitle ? selfTitle.textContent : '') + ' ' + (
                        selfDesc ? selfDesc.textContent : '');
                    let isTitleMatch = this._checkMatch(titleContent, searchStr, regex);

                    // 2. Check DataView and fields
                    let isDataViewMatch = false;
                    let matchedDataViewFields = [];

                    if (this.searchScope === 'full' && dataviewEls.length > 0) {
                        const dataviewWrapper = dataviewEls[0];
                        const fields = dataviewWrapper.querySelectorAll('.break-all');

                        fields.forEach(f => {
                            if (this._checkMatch(f.textContent, searchStr, regex)) {
                                isDataViewMatch = true;
                                matchedDataViewFields.push(f);
                            }
                        });
                    }

                    if (isTitleMatch || isDataViewMatch) {
                        this.matches.push({
                            element: item,
                            inTitle: isTitleMatch,
                            titleElement: selfTitle,
                            inDataView: isDataViewMatch,
                            dataViewFields: matchedDataViewFields,
                            dataViewWrapper: dataviewEls.length > 0 ? dataviewEls[
                                0] : null
                        });
                    }
                });

                if (this.matches.length > 0) {
                    this.currentIndex = 0;
                    this.focusCurrentMatch();
                } else {
                    this.currentIndex = -1;
                }
            },

            _checkMatch(content, searchStr, regex) {
                if (this.searchMethod === 'phrase') {
                    return content.toLowerCase().includes(searchStr);
                } else if (regex) {
                    return regex.test(content);
                } else {
                    const keywords = searchStr.split(/\s+/).filter(k => k);
                    if (keywords.length > 0) {
                        return keywords.every(k => content.toLowerCase().includes(k));
                    }
                    return false;
                }
            },

            findNext() {
                if (this.matches.length === 0) return;
                this.currentIndex = (this.currentIndex + 1) % this.matches.length;
                this.focusCurrentMatch();
            },

            findPrevious() {
                if (this.matches.length === 0) return;
                this.currentIndex = (this.currentIndex - 1 + this.matches.length) % this.matches
                    .length;
                this.focusCurrentMatch();
            },

            focusCurrentMatch() {
                if (this.matches.length === 0 || this.currentIndex < 0 || this.currentIndex >= this
                    .matches.length) return;

                this.clearHighlights();

                const matchData = this.matches[this.currentIndex];
                const item = matchData.element;

                // --- Node Auto-Expansion ---
                let parentsToExpand = [];
                let currentItem = item;
                while (currentItem) {
                    const parentList = currentItem.parentElement.closest('.filament-tree-list');
                    if (!parentList) break;

                    const parentNode = parentList.closest('.filament-tree-row');
                    if (!parentNode) break;

                    if (parentNode.classList.contains('dd-collapsed')) {
                        parentsToExpand.push(parentNode);
                    }

                    currentItem = parentNode;
                }

                // Expand from top-most parent down to the immediate parent synchronously
                // Bypassing jQuery .click() animations ensures the DOM is instantly ready for scrollIntoView
                parentsToExpand.reverse().forEach(pNode => {
                    pNode.classList.remove('dd-collapsed');
                    
                    // Show the child list directly
                    const childList = pNode.querySelector(':scope > .filament-tree-list') || pNode.querySelector('.filament-tree-list');
                    if (childList) {
                        childList.style.display = 'block';
                    }

                    // Swap the expand/collapse icons
                    const expandBtn = pNode.querySelector(':scope > .dd-row .dd-item-btns [data-action="expand"]') || pNode.querySelector('[data-action="expand"]');
                    const collapseBtn = pNode.querySelector(':scope > .dd-row .dd-item-btns [data-action="collapse"]') || pNode.querySelector('[data-action="collapse"]');
                    
                    if (expandBtn) expandBtn.classList.add('hidden');
                    if (collapseBtn) collapseBtn.classList.remove('hidden');
                });

                // Node level highlight
                item.classList.add('ring-2', 'ring-primary-500', 'bg-primary-50',
                    'dark:bg-primary-900/20');

                // Target elements to scroll to and highlight specifically
                let elementToScrollTo = item;

                // --- Title Highlighting ---
                if (matchData.inTitle && !matchData.inDataView) {
                    if (matchData.titleElement && matchData.titleElement.parentElement) {
                        matchData.titleElement.parentElement.classList.add(
                            'tree-search-highlight-bg');
                        elementToScrollTo = matchData.titleElement.parentElement;
                    }
                }

                // --- DataView Highlighting and Auto-Expansion ---
                if (matchData.inDataView) {
                    // Trigger the item's Alpine instance to open the DataView safely for v3
                    try {
                        if (window.Alpine && window.Alpine.$data) {
                            window.Alpine.$data(item).isDataViewOpen = true;
                        } else if (item._x_dataStack) {
                            item._x_dataStack[0].isDataViewOpen = true;
                        }
                    } catch (e) {
                        console.error('Failed opening DataView via Alpine', e);
                    }

                    // Specific highlight on the matched fields
                    matchData.dataViewFields.forEach((field, idx) => {
                        const fieldContainer = field.closest('.bg-gray-50\\/80') || field
                            .parentElement;
                        fieldContainer.classList.add('tree-search-highlight-bg');

                        if (idx === 0) {
                            elementToScrollTo = fieldContainer;
                        }
                    });
                }

                // Note: The DataView Alpine transitions and Nestable slideDown take time (~300-400ms).
                // Wait until they are almost done so the block position is stable.
                setTimeout(() => {
                    elementToScrollTo.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    // Provide a backup instant adjustment for any trailing layout shifts
                    setTimeout(() => {
                        elementToScrollTo.scrollIntoView({
                            behavior: 'auto',
                            block: 'center'
                        });
                    }, 350);
                }, 400);
            }
        }));
    });
</script>
