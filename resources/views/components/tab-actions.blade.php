@props(['tabs', 'active', 'route', 'param' => 'kategori', 'import' => false, 'addLabel' => null, 'addOnclick' => null])

{{-- Page Toolbar: Kiri = Tab, Kanan = Tombol Aksi. Satu baris rapi, responsive wrap. --}}
<div class="tab-action-bar mb-3">
    <ul class="nav nav-pills gap-2 mb-0">
        @foreach($tabs as $key => $text)
            <li class="nav-item">
                <a href="{{ route($route, [$param => $key]) }}"
                   class="nav-link {{ $active === $key ? 'active-tab' : '' }}"
                   @if($active === $key) aria-current="page" @endif>
                    {{ $text }}
                </a>
            </li>
        @endforeach
    </ul>

    @if($import || $addLabel)
    <div class="tab-action-bar__actions">
        @if($import)
        <button type="button" class="btn btn-outline-silab" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="bi bi-file-earmark-excel"></i> Import Excel
        </button>
        @endif
        @if($addLabel)
        <button type="button" class="btn btn-primary" onclick="{{ $addOnclick }}">
            <i class="bi bi-plus-lg"></i> {{ $addLabel }}
        </button>
        @endif
    </div>
    @endif
</div>
