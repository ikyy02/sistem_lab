@props(['tabs', 'active', 'route', 'addLabel', 'addOnclick'])

{{-- Baris tab kategori (kiri) + tombol Import Excel & Tambah (kanan). Dipakai semua halaman yang punya keduanya. --}}
<div class="tab-action-bar mb-3">
    <ul class="nav nav-pills gap-2 mb-0">
        @foreach($tabs as $key => $text)
            <li class="nav-item">
                <a href="{{ route($route, ['kategori' => $key]) }}"
                   class="nav-link px-4 {{ $active === $key ? 'active-tab' : '' }}"
                   @if($active === $key) aria-current="page" @endif
                   style="{{ $active === $key ? '' : 'background:#fff;border:1px solid var(--border-color);color:#3d475a;font-weight:500;' }} border-radius:8px;font-size:.87rem;">
                    {{ $text }}
                </a>
            </li>
        @endforeach
    </ul>

    <div class="tab-action-bar__actions">
        <button type="button" class="btn btn-outline-silab" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="bi bi-file-earmark-excel"></i> Import Excel
        </button>
        <button type="button" class="btn btn-primary" onclick="{{ $addOnclick }}">
            <i class="bi bi-plus-lg"></i> {{ $addLabel }}
        </button>
    </div>
</div>
