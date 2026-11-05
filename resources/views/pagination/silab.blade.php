{{--
    Pagination SILAB: Previous | nomor halaman | Next
    Dipakai lewat: $data->links('pagination.silab')
    (View bawaan Laravel bertipe Tailwind, sedangkan project ini memakai Bootstrap 5.)
--}}
@if ($paginator->hasPages())
    <nav aria-label="Navigasi halaman">
        <ul class="pagination flex-wrap mb-0">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link"><i class="bi bi-chevron-left" style="font-size:0.7rem;"></i> Previous</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                        <i class="bi bi-chevron-left" style="font-size:0.7rem;"></i> Previous
                    </a>
                </li>
            @endif

            {{-- Nomor halaman --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">
                        Next <i class="bi bi-chevron-right" style="font-size:0.7rem;"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link">Next <i class="bi bi-chevron-right" style="font-size:0.7rem;"></i></span>
                </li>
            @endif
        </ul>
    </nav>
@endif
