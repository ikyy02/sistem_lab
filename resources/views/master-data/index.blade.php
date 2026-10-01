@extends('layouts.app')

@section('title', 'Kelola Data Master')
@section('page-title', 'Kelola Data Master')
@section('page-subtitle', 'Sumber pilihan dropdown pada form terkait.')

@section('content')
@php
    $label = $labels[$kategori];
    $readonly = isset($cfg['readonly']);
    $grups = $cfg['grups'] ?? null;
    $listQuery = request()->only(['search', 'per_page', 'page']);
    $modalQuery = request()->only(['search', 'per_page', 'page', 'kategori']);
    $openModal = session('open_modal');
    $colspan = ($grups ? 5 : 4);
@endphp

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="font-display fw-semibold mb-1" style="font-size:1.5rem;">Kelola Data Master</h2>
        <p class="mb-0" style="font-size:.83rem;color:var(--text-muted);">Perubahan di sini otomatis mengikuti dropdown pada form Kelola Data User dan Kelola Katalog.</p>
    </div>
    @unless($readonly)
    <button type="button" class="btn btn-primary d-flex align-items-center gap-2" onclick="openMasterModal('create')"><i class="bi bi-plus-lg"></i> Tambah {{ $label }}</button>
    @endunless
</div>

<ul class="nav nav-pills gap-2 mb-3">
    @foreach($labels as $key => $text)
        <li class="nav-item">
            <a href="{{ route('master-data.index', ['kategori' => $key]) }}" class="nav-link px-4 {{ $kategori === $key ? 'active-tab' : '' }}"
               style="{{ $kategori === $key ? '' : 'background:#fff;border:1px solid var(--border-color);color:#3d475a;font-weight:500;' }} border-radius:8px;font-size:.87rem;">{{ $text }}</a>
        </li>
    @endforeach
</ul>

@unless($readonly)
<form method="GET" action="{{ route('master-data.index') }}" class="card-modern p-3 mb-3">
    <input type="hidden" name="kategori" value="{{ $kategori }}">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-lg-6">
            <label class="form-label" for="search">Pencarian</label>
            <div class="input-group">
                <span class="input-group-text bg-white" style="border-color:var(--border-color);"><i class="bi bi-search"></i></span>
                <input type="text" id="search" name="search" value="{{ $search }}" maxlength="100" class="form-control" placeholder="Cari {{ strtolower($label) }}...">
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <label class="form-label" for="per_page">Tampil</label>
            <select id="per_page" name="per_page" class="form-select">
                @foreach($perPageOptions as $n)<option value="{{ $n }}" @selected($perPage === $n)>{{ $n }}</option>@endforeach
            </select>
        </div>
        <div class="col-6 col-lg-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">Terapkan</button>
            <a href="{{ route('master-data.index', ['kategori' => $kategori]) }}" class="btn btn-outline-silab" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </div>
</form>
@endunless

<div class="card-modern overflow-hidden">
    <div class="table-responsive">
        <table class="table table-silab mb-0">
            <thead>
                <tr>
                    <th class="ps-4" style="width:56px;">No</th>
                    <th>{{ $label }}</th>
                    @if($grups)<th>Berlaku untuk</th>@endif
                    <th style="width:120px;">Dipakai</th>
                    @unless($readonly)<th class="text-end pe-4">Aksi</th>@endunless
                </tr>
            </thead>
            <tbody>
            @forelse($data as $item)
                <tr>
                    <td class="ps-4 text-muted">{{ $readonly ? $loop->iteration : $data->firstItem() + $loop->index }}</td>
                    <td><span class="fw-semibold">{{ $item->nama }}</span></td>
                    @if($grups)<td>{{ $grups[$item->grup] ?? '—' }}</td>@endif
                    <td><span class="badge badge-tab" style="font-weight:700;padding:5px 12px;">{{ $item->used }}</span></td>
                    @unless($readonly)
                    <td class="text-end pe-4">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-edit d-flex align-items-center gap-1" data-id="{{ $item->id }}"
                                    data-item="{{ json_encode(['nama' => $item->nama, 'grup' => $item->grup ?? '']) }}"
                                    onclick="openMasterModal('edit', this)"><i class="bi bi-pencil"></i> Edit</button>
                            <form action="{{ route('master-data.destroy', ['kategori' => $kategori, 'id' => $item->id] + $listQuery) }}" method="POST"
                                  onsubmit="return confirm('Hapus {{ strtolower($label) }} &quot;{{ e($item->nama) }}&quot;?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-del d-flex align-items-center gap-1"><i class="bi bi-trash"></i> Hapus</button>
                            </form>
                        </div>
                    </td>
                    @endunless
                </tr>
            @empty
                <tr><td colspan="{{ $colspan }}" class="text-center py-5" style="color:var(--text-muted);">
                    <i class="bi bi-inbox fs-1 d-block mb-2" style="color:#c3cbdb;"></i>
                    {{ $search !== '' ? 'Tidak ada ' . strtolower($label) . ' yang cocok dengan “' . $search . '”.' : 'Belum ada data ' . strtolower($label) . '.' }}
                </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="table-footer">
        <div>{{ $data->total() > 0 ? 'Menampilkan ' . ($readonly ? '1–' . $data->total() : $data->firstItem() . '–' . $data->lastItem()) . ' dari ' . $data->total() . ' data ' . strtolower($label) : '0 data' }}@if($readonly) · Jenis ditetapkan sistem dan tidak dapat diubah.@endif</div>
        @unless($readonly){{ $data->links('pagination.silab') }}@endunless
    </div>
</div>

@unless($readonly)
<!-- Modal Tambah/Edit -->
<div class="modal fade" id="masterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="masterForm" class="modal-content" autocomplete="off"
              data-store="{{ route('master-data.store', $kategori) }}"
              data-update="{{ route('master-data.update', ['kategori' => $kategori, 'id' => '__ID__']) }}"
              data-query="{{ json_encode($modalQuery) }}">
            @csrf
            <input type="hidden" name="_method" id="masterMethod" value="PUT" disabled>
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="masterModalTitle" style="font-size:1rem;">Tambah {{ $label }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="f_nama">{{ $label }} <span class="text-danger">*</span></label>
                        <input type="text" id="f_nama" name="nama" value="{{ old('nama') }}" maxlength="{{ $cfg['max'] }}" class="form-control @error('nama') is-invalid @enderror" placeholder="Nama {{ strtolower($label) }}">
                        @error('nama')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    @if($grups)
                    <div class="col-12">
                        <label class="form-label" for="f_grup">Berlaku untuk <span class="text-danger">*</span></label>
                        <select id="f_grup" name="grup" class="form-select @error('grup') is-invalid @enderror">
                            <option value="">Pilih Jenis</option>
                            @foreach($grups as $val => $text)<option value="{{ $val }}" @selected(old('grup') === $val)>{{ $text }}</option>@endforeach
                        </select>
                        @error('grup')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-silab" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endunless
@endsection

@push('scripts')
@unless($readonly)
<script>
    const masterLabel = {!! json_encode($label) !!};

    function openMasterModal(mode, btn, keepOld) {
        const form = document.getElementById('masterForm');
        const edit = mode === 'edit';
        const id = edit ? (btn.dataset ? btn.dataset.id : btn) : null;

        document.getElementById('masterModalTitle').textContent = (edit ? 'Edit ' : 'Tambah ') + masterLabel;
        document.getElementById('masterMethod').disabled = !edit;
        form.action = edit
            ? form.dataset.update.replace('__ID__', id) + '?' + new URLSearchParams(JSON.parse(form.dataset.query || '{}')).toString()
            : form.dataset.store;

        if (!keepOld) {
            const item = edit ? JSON.parse(btn.dataset.item) : {};
            document.getElementById('f_nama').value = item.nama ?? '';
            const grup = document.getElementById('f_grup');
            if (grup) grup.value = item.grup ?? '';
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
        }
        bootstrap.Modal.getOrCreateInstance(document.getElementById('masterModal')).show();
    }

    @if($openModal)
    document.addEventListener('DOMContentLoaded', function () {
        openMasterModal('{{ $openModal['mode'] }}', {!! $openModal['id'] ? "'" . (int) $openModal['id'] . "'" : 'null' !!}, true);
    });
    @endif
</script>
@endunless
@endpush
