@if ($paginator->hasPages() || $paginator->total() > 0)
    <nav role="navigation" aria-label="Navigasi Halaman" class="siwil-pagination-container">
        {{-- Info Counter --}}
        <div class="siwil-pagination-info">
            Menampilkan
            <span class="siwil-pagination-highlight">{{ $paginator->firstItem() ?? 0 }}</span>
            sampai
            <span class="siwil-pagination-highlight">{{ $paginator->lastItem() ?? 0 }}</span>
            dari
            <span class="siwil-pagination-highlight">{{ $paginator->total() }}</span>
            hasil
        </div>

        {{-- Navigation Controls --}}
        @if ($paginator->hasPages())
            <div class="siwil-pagination-controls">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span class="siwil-page-btn siwil-page-disabled" aria-disabled="true" title="Sebelumnya" aria-label="Sebelumnya">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="siwil-page-btn" title="Sebelumnya" aria-label="Sebelumnya">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </a>
                @endif

                {{-- Pagination Elements --}}
                <div class="siwil-page-numbers">
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span class="siwil-page-dots">{{ $element }}</span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span class="siwil-page-number active" aria-current="page">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="siwil-page-number">{{ $page }}</a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                </div>

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="siwil-page-btn" title="Berikutnya" aria-label="Berikutnya">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                @else
                    <span class="siwil-page-btn siwil-page-disabled" aria-disabled="true" title="Berikutnya" aria-label="Berikutnya">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </span>
                @endif
            </div>
        @endif
    </nav>
@endif
