{{-- vendor/pagination/custom.blade.php — Modern responsive pagination (Previous / Next) --}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 py-4 border-t border-border">
        <p class="text-xs text-muted-foreground order-2 sm:order-1">
            Menampilkan
            <span class="font-semibold text-muted-foreground">{{ $paginator->firstItem() }}</span>–<span class="font-semibold text-muted-foreground">{{ $paginator->lastItem() }}</span>
            dari <span class="font-semibold text-muted-foreground">{{ $paginator->total() }}</span> data
        </p>

        <div class="flex items-center gap-1.5 order-1 sm:order-2">
            {{-- Previous Button --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 rounded-xl text-xs font-medium text-muted-foreground bg-muted cursor-not-allowed select-none" aria-disabled="true" aria-label="Sebelumnya">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                    <span class="hidden sm:inline">Sebelumnya</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                   class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 rounded-xl text-xs font-semibold text-brand-900 bg-white border border-border hover:bg-brand-900 hover:text-white hover:border-brand-900 active:scale-95 transition-all duration-200"
                   aria-label="Sebelumnya">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                    <span class="hidden sm:inline">Sebelumnya</span>
                </a>
            @endif

            {{-- Page Number Links --}}
            <div class="hidden md:flex items-center gap-1">
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span class="px-2 py-2 text-xs text-muted-foreground">{{ $element }}</span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page"
                                      class="inline-flex items-center justify-center min-w-[34px] h-[34px] px-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-brand-900 to-brand-800 shadow-md shadow-brand-900/20">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}"
                                   class="inline-flex items-center justify-center min-w-[34px] h-[34px] px-2 rounded-xl text-xs font-semibold text-muted-foreground bg-white border border-border hover:bg-muted hover:text-brand-900 active:scale-95 transition-all duration-200">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Current page indicator (mobile) --}}
            <span class="md:hidden inline-flex items-center justify-center min-w-[34px] h-[34px] px-3 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-brand-900 to-brand-800 shadow-md shadow-brand-900/20">
                {{ $paginator->currentPage() }}<span class="text-white/50 font-medium">/{{ $paginator->lastPage() }}</span>
            </span>

            {{-- Next Button --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                   class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 rounded-xl text-xs font-semibold text-brand-900 bg-white border border-border hover:bg-brand-900 hover:text-white hover:border-brand-900 active:scale-95 transition-all duration-200"
                   aria-label="Berikutnya">
                    <span class="hidden sm:inline">Berikutnya</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </a>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 rounded-xl text-xs font-medium text-muted-foreground bg-muted cursor-not-allowed select-none" aria-disabled="true" aria-label="Berikutnya">
                    <span class="hidden sm:inline">Berikutnya</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </span>
            @endif
        </div>
    </nav>
@elseif ($paginator->total() > 0)
    <div class="px-4 py-3 border-t border-border">
        <p class="text-xs text-muted-foreground">Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data</p>
    </div>
@endif
