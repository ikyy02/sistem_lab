@extends('layouts.app')

@section('title', 'Pengaturan Laboratorium')
@section('page-title', 'Pengaturan Laboratorium')
@section('page-subtitle', 'Satuan alat/bahan serta jam dan hari operasional laboratorium.')

@section('content')
@php $satuan = $tab === 'satuan'; @endphp

<p class="mb-3" style="font-size:.83rem;color:var(--text-muted);">
    @if($satuan)Perubahan di sini otomatis mengikuti dropdown pada form Kelola Katalog.@else Jam dan hari operasional dipakai untuk memeriksa waktu pengajuan peminjaman.@endif
</p>

<x-tab-actions :tabs="$tabs" :active="$tab" route="pengaturan-lab.index" param="tab"
    :add-label="$satuan ? 'Tambah Satuan' : null" add-onclick="openMasterModal('create')" />

@if($satuan)
@php
    $listQuery = request()->only(['search', 'per_page', 'page']);
    $modalQuery = request()->only(['search', 'per_page', 'page']) + ['tab' => 'satuan'];
    $openModal = session('open_modal');
@endphp

<form method="GET" action="{{ route('pengaturan-lab.index') }}" class="card-modern p-3 mb-3">
    <input type="hidden" name="tab" value="satuan">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-lg-6">
            <label class="form-label" for="search">Pencarian</label>
            <div class="input-group">
                <span class="input-group-text bg-white" style="border-color:var(--border-color);"><i class="bi bi-search"></i></span>
                <input type="text" id="search" name="search" value="{{ $search }}" maxlength="100" class="form-control" placeholder="Cari satuan...">
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
            <a href="{{ route('pengaturan-lab.index', ['tab' => 'satuan']) }}" class="btn btn-outline-silab" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </div>
</form>

<div class="card-modern overflow-hidden">
    <div class="table-responsive">
        <table class="table table-silab mb-0">
            <thead>
                <tr>
                    <th class="ps-4" style="width:56px;">No</th>
                    <th>Satuan</th>
                    <th style="width:120px;">Dipakai</th>
                    <th class="text-end pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($data as $item)
                <tr>
                    <td class="ps-4 text-muted">{{ $data->firstItem() + $loop->index }}</td>
                    <td><span class="fw-semibold">{{ $item->nama }}</span></td>
                    <td><span class="badge badge-tab" style="font-weight:700;padding:5px 12px;">{{ $item->used }}</span></td>
                    <td class="text-end pe-4">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-edit d-flex align-items-center gap-1" data-id="{{ $item->id }}"
                                    data-item="{{ json_encode(['nama' => $item->nama]) }}"
                                    onclick="openMasterModal('edit', this)"><i class="bi bi-pencil"></i> Edit</button>
                            <form action="{{ route('pengaturan-lab.satuan.destroy', ['id' => $item->id] + $listQuery) }}" method="POST"
                                  data-konfirmasi="Hapus satuan &quot;{{ $item->nama }}&quot;?">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-del d-flex align-items-center gap-1"><i class="bi bi-trash"></i> Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center py-5" style="color:var(--text-muted);">
                    <i class="bi bi-inbox fs-1 d-block mb-2" style="color:#c3cbdb;"></i>
                    {{ $search !== '' ? 'Tidak ada satuan yang cocok dengan “' . $search . '”.' : 'Belum ada data satuan.' }}
                </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="table-footer">
        <div>{{ $data->total() > 0 ? 'Menampilkan ' . $data->firstItem() . '–' . $data->lastItem() . ' dari ' . $data->total() . ' data satuan' : '0 data' }}</div>
        {{ $data->links('pagination.silab') }}
    </div>
</div>

<!-- Modal Tambah/Edit Satuan -->
<div class="modal fade" id="masterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="masterForm" class="modal-content" autocomplete="off"
              data-store="{{ route('pengaturan-lab.satuan.store') }}"
              data-update="{{ route('pengaturan-lab.satuan.update', ['id' => '__ID__']) }}"
              data-query="{{ json_encode($modalQuery) }}">
            @csrf
            <input type="hidden" name="_method" id="masterMethod" value="PUT" disabled>
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="masterModalTitle" style="font-size:1rem;">Tambah Satuan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4">
                <label class="form-label" for="f_nama">Satuan <span class="text-danger">*</span></label>
                <input type="text" id="f_nama" name="nama" value="{{ old('nama') }}" maxlength="50" class="form-control @error('nama') is-invalid @enderror" placeholder="Nama satuan">
                @error('nama')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-silab" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

@else
@php
    $aktif = old('hari', $setting->hariAktif());
    $buka = old('jam_buka', substr($setting->jam_buka, 0, 5));
    $tutup = old('jam_tutup', substr($setting->jam_tutup, 0, 5));
@endphp

<form method="POST" action="{{ route('pengaturan-lab.operasional.update') }}" class="card-modern p-4">
    @csrf @method('PUT')
    <h5 class="fw-bold mb-3" style="font-size:1rem;">Jam &amp; Hari Operasional</h5>
    <div class="row g-3">
        <div class="col-6">
            <label class="form-label" for="jam_buka">Jam Buka <span class="text-danger">*</span></label>
            <input type="time" id="jam_buka" name="jam_buka" value="{{ $buka }}" class="form-control @error('jam_buka') is-invalid @enderror">
            @error('jam_buka')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-6">
            <label class="form-label" for="jam_tutup">Jam Tutup <span class="text-danger">*</span></label>
            <input type="time" id="jam_tutup" name="jam_tutup" value="{{ $tutup }}" class="form-control @error('jam_tutup') is-invalid @enderror">
            @error('jam_tutup')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <label class="form-label">Hari Operasional <span class="text-danger">*</span></label>
            <div class="d-flex flex-wrap gap-3">
                @foreach($labelHari as $key => $text)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="hari[]" value="{{ $key }}" id="hari_{{ $key }}" @checked(in_array($key, $aktif, true))>
                        <label class="form-check-label" for="hari_{{ $key }}">{{ $text }}</label>
                    </div>
                @endforeach
            </div>
            @error('hari')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="mt-4"><button type="submit" class="btn btn-primary">Simpan</button></div>
</form>
@endif
@endsection

@push('scripts')
@if($satuan)
<script>
    function openMasterModal(mode, btn, keepOld) {
        const form = document.getElementById('masterForm');
        const edit = mode === 'edit';
        const id = edit ? (btn.dataset ? btn.dataset.id : btn) : null;

        document.getElementById('masterModalTitle').textContent = edit ? 'Edit Satuan' : 'Tambah Satuan';
        document.getElementById('masterMethod').disabled = !edit;
        form.action = edit
            ? form.dataset.update.replace('__ID__', id) + '?' + new URLSearchParams(JSON.parse(form.dataset.query || '{}')).toString()
            : form.dataset.store;

        if (!keepOld) {
            document.getElementById('f_nama').value = edit ? (JSON.parse(btn.dataset.item).nama ?? '') : '';
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
@endif
@endpush
