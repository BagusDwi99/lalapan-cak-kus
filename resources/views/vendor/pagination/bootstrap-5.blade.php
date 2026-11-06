{{--
    Tampilan pagination Bootstrap 5 berbahasa Indonesia.
    File ini menggantikan tampilan bawaan Laravel yang berbahasa Inggris.
--}}
@if ($paginator->hasPages())
    <nav class="d-flex justify-content-between align-items-center">
        <div class="small text-muted">
            Menampilkan {{ $paginator->firstItem() }} sampai {{ $paginator->lastItem() }}
            dari {{ $paginator->total() }} data
        </div>

        <ul class="pagination mb-0">
            {{-- Tombol ke halaman sebelumnya --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled"><span class="page-link">&lsaquo; Sebelumnya</span></li>
            @else
                <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">&lsaquo; Sebelumnya</a></li>
            @endif

            {{-- Nomor halaman --}}
            @foreach ($elements as $element)
                {{-- Tanda "..." jika halamannya banyak --}}
                @if (is_string($element))
                    <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $halaman => $url)
                        @if ($halaman == $paginator->currentPage())
                            <li class="page-item active" aria-current="page"><span class="page-link">{{ $halaman }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $halaman }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Tombol ke halaman berikutnya --}}
            @if ($paginator->hasMorePages())
                <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya &rsaquo;</a></li>
            @else
                <li class="page-item disabled"><span class="page-link">Berikutnya &rsaquo;</span></li>
            @endif
        </ul>
    </nav>
@endif
