<style>
    .pagination-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 24px 0;
        margin-top: 32px;
        border-top: 1px solid var(--color-border);
    }

    .pagination-info {
        font-size: 13px;
        font-weight: 500;
        color: var(--color-text-secondary);
    }

    .pagination-links {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .pagination-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        height: 38px;
        padding: 0 14px;
        border-radius: var(--radius-md);
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        color: var(--color-text);
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .pagination-link:hover:not(.disabled):not(.active) {
        background: var(--color-bg);
        border-color: var(--color-neutral);
        transform: translateY(-1px);
    }

    .pagination-link.active {
        background: var(--color-primary) !important;
        border-color: var(--color-primary) !important;
        color: #ffffff !important;
        box-shadow: var(--shadow-primary);
    }

    .pagination-link.disabled {
        opacity: 0.4;
        cursor: not-allowed;
        background: var(--color-bg);
        box-shadow: none;
    }

    @media (max-width: 768px) {
        .pagination-container {
            flex-direction: column;
            gap: 20px;
            align-items: center;
            text-align: center;
        }
    }
</style>

<div>
    @if ($paginator->hasPages())
        <div class="pagination-container">
            <div class="pagination-info">
                Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
            </div>

            <div class="pagination-links">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span class="pagination-link disabled">Prev</span>
                @else
                    <button wire:click="previousPage" wire:loading.attr="disabled" rel="prev" class="pagination-link">Prev</button>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span class="pagination-link disabled">{{ $element }}</span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="pagination-link active" wire:key="paginator-page-{{ $page }}">{{ $page }}</span>
                            @else
                                <button wire:click="gotoPage({{ $page }})" class="pagination-link" wire:key="paginator-page-{{ $page }}">{{ $page }}</button>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <button wire:click="nextPage" wire:loading.attr="disabled" rel="next" class="pagination-link">Next</button>
                @else
                    <span class="pagination-link disabled">Next</span>
                @endif
            </div>
        </div>
    @endif
</div>
