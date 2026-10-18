@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between py-3">
        {{-- Mobile pagination --}}
        <div class="flex flex-1 items-center justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="btn-ghost btn-sm pointer-events-none opacity-40">
                    &larr; Previous
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn-ghost btn-sm focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2">
                    &larr; Previous
                </a>
            @endif

            <span class="text-xs font-medium text-slate-500">
                Page <span class="font-bold text-forest">{{ $paginator->currentPage() }}</span> of <span class="font-bold text-forest">{{ $paginator->lastPage() }}</span>
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn-ghost btn-sm focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2">
                    Next &rarr;
                </a>
            @else
                <span class="btn-ghost btn-sm pointer-events-none opacity-40">
                    Next &rarr;
                </span>
            @endif
        </div>

        {{-- Desktop pagination --}}
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-slate-500">
                    Showing
                    @if ($paginator->firstItem())
                        <span class="font-bold text-forest">{{ $paginator->firstItem() }}</span>
                        to
                        <span class="font-bold text-forest">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    of
                    <span class="font-bold text-forest">{{ $paginator->total() }}</span>
                    results
                </p>
            </div>

            <div>
                <ul class="flex items-center gap-1">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <li aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl text-slate-300 opacity-40" aria-hidden="true">&larr;</span>
                        </li>
                    @else
                        <li>
                            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2" aria-label="{{ __('pagination.previous') }}">&larr;</a>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <li aria-disabled="true">
                                <span class="flex h-9 w-9 items-center justify-center text-sm font-medium text-slate-400">{{ $element }}</span>
                            </li>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li aria-current="page">
                                        <span class="flex h-9 min-w-9 items-center justify-center rounded-xl bg-forest px-3 text-sm font-bold text-white shadow-sm">{{ $page }}</span>
                                    </li>
                                @else
                                    <li>
                                        <a href="{{ $url }}" class="flex h-9 min-w-9 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 transition hover:border-brand hover:text-forest hover:bg-brand-50 focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                            {{ $page }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <li>
                            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2" aria-label="{{ __('pagination.next') }}">&rarr;</a>
                        </li>
                    @else
                        <li aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl text-slate-300 opacity-40" aria-hidden="true">&rarr;</span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
@endif
