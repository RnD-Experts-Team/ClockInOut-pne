<!-- resources/views/custom1.blade.php -->
@if ($paginator->hasPages())
    <div >
        <!-- Pagination Info -->
        <!-- Pagination Navigation -->
        <nav aria-label="Pagination Navigation" class="custom-pagination">
            {{-- First Page Link --}}
            @if ($paginator->currentPage() > 3)
                <a class="page-link edge-btn" href="{{ $paginator->url(1) }}" aria-label="Go to first page">
                    <span aria-hidden="true">««</span>
                </a>
            @endif

            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="page-link nav-btn disabled" aria-label="Previous page (disabled)">
                <span aria-hidden="true">‹</span>
            </span>
            @else
                <a class="page-link nav-btn" href="{{ $paginator->previousPageUrl() }}" aria-label="Go to previous page">
                    <span aria-hidden="true">‹</span>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="page-link ellipsis" aria-hidden="true">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="page-link active" aria-current="page" aria-label="Current page, page {{ $page }}">
                            {{ $page }}
                        </span>
                        @else
                            <a class="page-link" href="{{ $url }}" aria-label="Go to page {{ $page }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a class="page-link nav-btn" href="{{ $paginator->nextPageUrl() }}" aria-label="Go to next page">
                    <span aria-hidden="true">›</span>
                </a>
            @else
                <span class="page-link nav-btn disabled" aria-label="Next page (disabled)">
                <span aria-hidden="true">›</span>
            </span>
            @endif

            {{-- Last Page Link --}}
            @if ($paginator->currentPage() < $paginator->lastPage() - 2)
                <a class="page-link edge-btn" href="{{ $paginator->url($paginator->lastPage()) }}" aria-label="Go to last page, page {{ $paginator->lastPage() }}">
                    <span aria-hidden="true">»»</span>
                </a>
            @endif
        </nav>
    </div>
@endif

<style>

    /* Modern Pagination Container */
    .custom-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin: 2rem 0;
        padding: 1rem;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    /* Individual Pagination Links */
    .custom-pagination .page-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 44px; /* Minimum touch target size */
        min-height: 44px;
        padding: 8px 12px;
        margin: 0 2px;

        /* Typography */
        font-size: 14px;
        font-weight: 500;
        line-height: 1.5;
        text-decoration: none;
        color: #374151;

        /* Visual styling */
        background-color: #ffffff;
        border: 2px solid #e5e7eb;
        border-radius: 8px;

        /* Smooth transitions */
        transition: all 0.2s ease-in-out;
        cursor: pointer;

        /* Accessibility */
        position: relative;
        overflow: hidden;
    }

    /* Hover Effects */
    .custom-pagination .page-link:hover:not(.active):not(.disabled) {
        background-color: #f3f4f6;
        border-color: #d1d5db;
        color: #111827;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    /* Active Page Styling */
    .custom-pagination .page-link.active {
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        border-color: #3b82f6;
        color: #ffffff;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    /* Disabled State */
    .custom-pagination .page-link.disabled {
        background-color: #f9fafb;
        border-color: #e5e7eb;
        color: #9ca3af;
        cursor: not-allowed;
        opacity: 0.6;
    }

    .custom-pagination .page-link.disabled:hover {
        transform: none;
        box-shadow: none;
    }

    /* Previous/Next Button Styling */
    .custom-pagination .page-link.nav-btn {
        padding: 8px 16px;
        font-weight: 500;
        background-color: #f8fafc;
        border-color: #cbd5e1;
        color: #475569;
    }

    .custom-pagination .page-link.nav-btn:hover:not(.disabled) {
        background-color: #e2e8f0;
        border-color: #94a3b8;
        color: #334155;
    }

    /* First/Last Page Styling */
    .custom-pagination .page-link.edge-btn {
        background-color: #fef3c7;
        border-color: #f59e0b;
        color: #92400e;
    }

    .custom-pagination .page-link.edge-btn:hover:not(.disabled) {
        background-color: #fde68a;
        border-color: #d97706;
        color: #78350f;
    }

    /* Ellipsis Styling */
    .custom-pagination .page-link.ellipsis {
        background: none;
        border: none;
        color: #9ca3af;
        cursor: default;
        padding: 8px 4px;
    }

    .custom-pagination .page-link.ellipsis:hover {
        background: none;
        transform: none;
        box-shadow: none;
    }

    /* Focus States for Accessibility */
    .custom-pagination .page-link:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
        z-index: 1;
    }

    /* Mobile Responsiveness */
    @media (max-width: 640px) {
        .custom-pagination {
            gap: 4px;
            margin: 1rem 0;
            flex-wrap: wrap;
        }

        .custom-pagination .page-link {
            min-width: 40px;
            min-height: 40px;
            padding: 6px 10px;
            font-size: 13px;
            margin: 0 1px;
        }

        .custom-pagination .page-link.nav-btn {
            padding: 6px 12px;
        }

        /* Hide some page numbers on mobile */
        .custom-pagination .page-link.mobile-hidden {
            display: none;
        }
    }

    /* Dark Mode Support */
    @media (prefers-color-scheme: dark) {
        .custom-pagination .page-link {
            background-color: #1f2937;
            border-color: #374151;
            color: #d1d5db;
        }

        .custom-pagination .page-link:hover:not(.active):not(.disabled) {
            background-color: #374151;
            border-color: #4b5563;
            color: #f9fafb;
        }

        .custom-pagination .page-link.disabled {
            background-color: #111827;
            color: #6b7280;
        }
    }

    /* Loading Animation */
    .custom-pagination.loading .page-link:not(.active) {
        opacity: 0.5;
        pointer-events: none;
    }

    /* Info Text Styling */
    .pagination-info {
        font-size: 14px;
        color: #6b7280;
        margin-bottom: 1rem;
        text-align: center;
        font-weight: 400;
    }

    .pagination-info strong {
        color: #374151;
        font-weight: 600;
    }

    /* Container for Pagination + Info */
    .pagination-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 2rem 1rem;
        background: linear-gradient(to right, #f8fafc, #f1f5f9);
        border-radius: 12px;
        margin: 2rem 0;
    }

    /* High Contrast Mode */
    @media (prefers-contrast: high) {
        .custom-pagination .page-link {
            border-width: 3px;
            font-weight: 600;
        }

        .custom-pagination .page-link.active {
            background: #000000;
            border-color: #000000;
            color: #ffffff;
        }
    }

    /* Reduced Motion */
    @media (prefers-reduced-motion: reduce) {
        .custom-pagination .page-link {
            transition: none;
        }

        .custom-pagination .page-link:hover {
            transform: none;
        }
    }

</style>
