@extends('layouts.app')

@section('title', 'Kelola ' . $labels[$kategori])
@section('page-title', 'Kelola Katalog')
@section('page-subtitle', 'Alat, Bahan, dan Ruangan laboratorium.')

@section('content')
@php
    $label = $labels[$kategori];
    $fields = $config['fields'];
    $isRoom = $kategori === 'ruangan';
    $listQuery = request()->only(['search', 'sort', 'direction', 'per_page', 'page']);
    $openModal = session('open_modal');
    $report = session('inv_import_report');
    $sortLabels = collect($config['columns'])->filter(fn ($c) => $c[1])->map(fn ($c) => $c[0]);
    $modalQuery = request()->only(['search', 'sort', 'direction', 'per_page', 'page', 'kategori']);
    $fieldNames = array_column($fields, 'name');
    $colspan = count($config['columns']) + ($isRoom ? 2 : 3);
@endphp

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="font-display fw-semibold mb-1" style="font-size:1.5rem;">Kelola {{ $label }}</h2>
        <p class="mb-0" style="font-size:.83rem;color:var(--text-muted);">
            {{ $isRoom
                ? 'Ruangan dipakai sebagai lokasi penyimpanan alat/bahan dan untuk peminjaman ruangan.'
                : 'Harga adalah harga untuk 1 satuan. Lokasi penyimpanan dipilih pada form dan ditampilkan di kolom Keterangan.' }}
        </p>
    </div>

    <div class="d-flex flex-wrap gap-2">
        <button
            type="button"
            class="btn btn-outline-silab d-flex align-items-center gap-2"
            data-bs-toggle="modal"
            data-bs-target="#importModal">
            <i class="bi bi-file-earmark-arrow-up"></i>
            Import
        </button>

        <button
            type="button"
            class="btn btn-primary d-flex align-items-center gap-2"
            onclick="openItemModal('create')">
            <i class="bi bi-plus-lg"></i>
            Tambah {{ $label }}
        </button>
    </div>
</div>

<ul class="nav nav-pills gap-2 mb-3">
    @foreach($labels as $key => $text)
        <li class="nav-item">
            <a
                href="{{ route('kelola-katalog.index', ['kategori' => $key]) }}"
                class="nav-link px-4 {{ $kategori === $key ? 'active-tab' : '' }}"
                style="{{ $kategori === $key ? '' : 'background:#fff;border:1px solid var(--border-color);color:#3d475a;font-weight:500;' }} border-radius:8px;font-size:.87rem;">
                {{ $text }}
            </a>
        </li>
    @endforeach
</ul>

@if($report && !empty($report['errors']))
    <div class="card-modern overflow-hidden mb-3 import-report" style="border-color:#fecdd3;">
        <div
            class="px-4 py-3 d-flex align-items-start gap-3"
            style="background:#fff1f2;border-bottom:1px solid #fecdd3;">
            <i
                class="bi bi-exclamation-triangle-fill"
                style="color:#be123c;font-size:1.2rem;">
            </i>

            <div class="flex-grow-1">
                <div
                    class="fw-bold"
                    style="font-size:.92rem;color:#9f1239;">
                    Import dibatalkan: {{ $report['error_count'] }} dari {{ $report['total'] }} baris bermasalah
                </div>

                <div style="font-size:.8rem;color:#be123c;">
                    Tidak ada data yang disimpan. Perbaiki baris berikut lalu unggah ulang.
                </div>
            </div>

            <button
                type="button"
                class="btn-close"
                aria-label="Tutup"
                onclick="this.closest('.import-report').remove()">
            </button>
        </div>

        <div class="table-responsive" style="max-height:320px;overflow-y:auto;">
            <table class="table table-sm mb-0" style="font-size:.82rem;">
                <thead>
                    <tr>
                        <th class="ps-4" style="width:90px;">Baris</th>
                        <th style="width:220px;">Nama</th>
                        <th>Alasan</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($report['errors'] as $e)
                        <tr>
                            <td class="ps-4 fw-semibold" style="color:#be123c;">
                                {{ $e['row'] }}
                            </td>

                            <td>
                                {{ $e['nama'] !== '' ? $e['nama'] : '—' }}
                            </td>

                            <td>
                                <ul class="mb-0 ps-3">
                                    @foreach($e['messages'] as $m)
                                        <li>{{ $m }}</li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<form
    method="GET"
    action="{{ route('kelola-katalog.index') }}"
    class="card-modern p-3 mb-3">

    <input type="hidden" name="kategori" value="{{ $kategori }}">

    <div class="row g-2 align-items-end">
        <div class="col-12 col-lg-4">
            <label class="form-label" for="search">Pencarian</label>

            <div class="input-group">
                <span
                    class="input-group-text bg-white"
                    style="border-color:var(--border-color);">
                    <i class="bi bi-search"></i>
                </span>

                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ $search }}"
                    maxlength="100"
                    class="form-control"
                    placeholder="Cari {{ strtolower($label) }}...">
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <label class="form-label" for="sort">Urutkan</label>

            <select id="sort" name="sort" class="form-select">
                @foreach($sortLabels as $key => $text)
                    <option
                        value="{{ $key }}"
                        @selected($sort === $key)>
                        {{ $text }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-6 col-lg-2">
            <label class="form-label" for="direction">Arah</label>

            <select id="direction" name="direction" class="form-select">
                <option value="asc" @selected($direction === 'asc')>
                    A → Z (Naik)
                </option>
                <option value="desc" @selected($direction === 'desc')>
                    Z → A (Turun)
                </option>
            </select>
        </div>

        <div class="col-6 col-lg-1">
            <label class="form-label" for="per_page">Tampil</label>

            <select id="per_page" name="per_page" class="form-select">
                @foreach($perPageOptions as $n)
                    <option
                        value="{{ $n }}"
                        @selected($perPage === $n)>
                        {{ $n }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-6 col-lg-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">
                Terapkan
            </button>

            <a
                href="{{ route('kelola-katalog.index', ['kategori' => $kategori]) }}"
                class="btn btn-outline-silab"
                title="Reset">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>
    </div>
</form>

<div class="card-modern overflow-hidden">
    <div>
        <table class="table table-silab tbl-katalog mb-0">
            <thead>
                <tr>
                    <th class="ps-4 col-no" style="width:56px;">No</th>

                    @unless($isRoom)
                        <th class="col-gambar" style="width:84px;">Gambar</th>
                    @endunless

                    @foreach($config['columns'] as $col => [$text, $sortable])
                        <th class="col-{{ $col }}">
                            @if($sortable)
                                @php
                                    $next = ($sort === $col && $direction === 'asc')
                                        ? 'desc'
                                        : 'asc';
                                @endphp

                                <a
                                    class="sort-link {{ $sort === $col ? 'active' : '' }}"
                                    href="{{ route('kelola-katalog.index', array_merge(
                                        request()->query(),
                                        [
                                            'kategori' => $kategori,
                                            'sort' => $col,
                                            'direction' => $next,
                                            'page' => null
                                        ]
                                    )) }}">
                                    {{ $text }}

                                    <i
                                        class="bi {{ $sort === $col
                                            ? ($direction === 'asc'
                                                ? 'bi-sort-up'
                                                : 'bi-sort-down')
                                            : 'bi-arrow-down-up' }}"
                                        style="font-size:.7rem;">
                                    </i>
                                </a>
                            @else
                                {{ $text }}
                            @endif
                        </th>
                    @endforeach

                    <th class="text-end pe-3 col-aksi">
                        Aksi
                    </th>
                </tr>
            </thead>

            <tbody>
                @forelse($data as $item)
                    @php
                        $payload = collect($fieldNames)
                            ->mapWithKeys(fn ($n) => [$n => $item->{$n}])
                            ->all()
                            + [
                                'gambar_url' => $isRoom ? null : $item->gambar_url
                            ];
                    @endphp

                    <tr>
                        <td class="ps-4 text-muted col-no">
                            {{ $data->firstItem() + $loop->index }}
                        </td>

                        @unless($isRoom)
                            <td class="col-gambar">
                                @if($item->gambar_url)
                                    <img
                                        src="{{ $item->gambar_url }}"
                                        alt="{{ $item->nama }}"
                                        class="thumb"
                                        loading="lazy"
                                        title="Klik untuk memperbesar"
                                        onclick="previewImage(this.src, this.alt)">
                                @else
                                    <span class="thumb-empty">
                                        <i class="bi bi-image"></i>
                                    </span>
                                @endif
                            </td>
                        @endunless

                        @foreach($config['columns'] as $col => $meta)
                            <td class="col-{{ $col }}">
                                @if(in_array($col, ['nama', 'nama_ruangan'], true))
                                    <span class="fw-semibold">
                                        {{ $item->{$col} }}
                                    </span>

                                @elseif($col === 'harga')
                                    Rp{{ number_format((float) $item->harga, 2, ',', '.') }}

                                @elseif($col === 'keterangan')
                                    @php
                                        $ket = trim((string) $item->keterangan);

                                        if (! $isRoom) {
                                            $lokasi = trim((string) ($item->nama_ruangan ?? ''));

                                            $teksLokasi = $lokasi !== ''
                                                ? 'Disimpan di ' . $lokasi . '.'
                                                : 'Lokasi penyimpanan belum ditentukan.';

                                            $ket = trim(
                                                $teksLokasi . ' ' .
                                                (
                                                    $ket !== ''
                                                        ? (
                                                            preg_match('/[.!?]$/u', $ket)
                                                                ? $ket
                                                                : $ket . '.'
                                                        )
                                                        : ''
                                                )
                                            );
                                        }
                                    @endphp

                                    <span
                                        class="d-block text-truncate"
                                        style="color:var(--text-muted);"
                                        title="{{ $ket }}">
                                        {{ $ket !== '' ? $ket : '—' }}
                                    </span>

                                @else
                                    {{ $item->{$col} !== '' && $item->{$col} !== null
                                        ? $item->{$col}
                                        : '—' }}
                                @endif
                            </td>
                        @endforeach

                        <td class="text-end pe-3 col-aksi">
                            <div class="d-flex flex-nowrap justify-content-end gap-2">
                                <button
                                    type="button"
                                    class="btn btn-edit btn-aksi d-flex align-items-center gap-1 text-nowrap"
                                    title="Edit"
                                    data-id="{{ $item->getKey() }}"
                                    data-item="{{ json_encode($payload) }}"
                                    onclick="openItemModal('edit', this)">
                                    <i class="bi bi-pencil"></i>
                                    <span class="btn-label">Edit</span>
                                </button>

                                <form
                                    action="{{ route('kelola-katalog.destroy', [
                                        'kategori' => $kategori,
                                        'id' => $item->getKey()
                                    ] + $listQuery) }}"
                                    method="POST"
                                    class="m-0"
                                    onsubmit="return confirm('Hapus {{ strtolower($label) }} &quot;{{ e($item->nama ?? $item->nama_ruangan) }}&quot;? Tindakan ini tidak dapat dibatalkan.')">
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn btn-del btn-aksi d-flex align-items-center gap-1 text-nowrap"
                                        title="Hapus">
                                        <i class="bi bi-trash"></i>
                                        <span class="btn-label">Hapus</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td
                            colspan="{{ $colspan }}"
                            class="text-center py-5"
                            style="color:var(--text-muted);">

                            <i
                                class="bi bi-inbox fs-1 d-block mb-2"
                                style="color:#c9bdb4;">
                            </i>

                            {{ $search !== ''
                                ? 'Tidak ada ' . strtolower($label) . ' yang cocok dengan “' . $search . '”.'
                                : 'Belum ada data ' . strtolower($label) . '.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div>
            {{ $data->total() > 0
                ? 'Menampilkan ' . $data->firstItem() . '–' . $data->lastItem() . ' dari ' . $data->total() . ' data ' . $label
                : '0 data' }}
        </div>

        {{ $data->links('pagination.silab') }}
    </div>
</div>

<!-- Modal Tambah/Edit -->
<div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form
            method="POST"
            id="itemForm"
            enctype="multipart/form-data"
            class="modal-content"
            autocomplete="off"
            data-store="{{ route('kelola-katalog.store', $kategori) }}"
            data-update="{{ route('kelola-katalog.update', ['kategori' => $kategori, 'id' => '__ID__']) }}"
            data-query="{{ json_encode($modalQuery) }}">

            @csrf

            <input
                type="hidden"
                name="_method"
                id="itemMethod"
                value="PUT"
                disabled>

            <div class="modal-header">
                <h5
                    class="modal-title fw-bold"
                    id="itemModalTitle"
                    style="font-size:1rem;">
                    Tambah {{ $label }}
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Tutup">
                </button>
            </div>

            <div class="modal-body p-4">
                <div class="row g-3">
                    @foreach($fields as $f)
                        <div class="col-12 {{ in_array($f['name'], ['stok', 'id_satuan', 'harga', 'id_ruangan']) ? 'col-md-6' : '' }}">
                            <label
                                class="form-label"
                                for="f_{{ $f['name'] }}">
                                {{ $f['label'] }}@if($f['required'] ?? true)
                                    <span class="text-danger">*</span>
                                @endif
                            </label>

                            @if(in_array($f['type'], ['select', 'satuan']))
                                <select
                                    id="f_{{ $f['name'] }}"
                                    name="{{ $f['name'] }}"
                                    class="form-select @error($f['name']) is-invalid @enderror">

                                    <option value="">
                                        {{ $f['placeholder'] }}
                                    </option>

                                    @foreach($f['options'] as $val => $opt)
                                        <option
                                            value="{{ $val }}"
                                            @selected((string) old($f['name']) === (string) $val)>
                                            {{ $opt }}
                                        </option>
                                    @endforeach
                                </select>

                            @elseif($f['type'] === 'textarea')
                                <textarea
                                    id="f_{{ $f['name'] }}"
                                    name="{{ $f['name'] }}"
                                    rows="3"
                                    class="form-control @error($f['name']) is-invalid @enderror"
                                    placeholder="{{ $f['placeholder'] }}">{{ old($f['name']) }}</textarea>

                            @else
                                <input
                                    type="{{ $f['type'] }}"
                                    id="f_{{ $f['name'] }}"
                                    name="{{ $f['name'] }}"
                                    value="{{ old($f['name']) }}"
                                    @if($f['type'] === 'number')
                                        min="{{ $f['name'] === 'harga' ? '0.01' : '0' }}"
                                        step="{{ $f['name'] === 'harga' ? '0.01' : '1' }}"
                                    @endif
                                    class="form-control @error($f['name']) is-invalid @enderror"
                                    placeholder="{{ $f['placeholder'] }}">
                            @endif

                            @error($f['name'])
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    @endforeach

                    @unless($isRoom)
                        <div class="col-12">
                            <label
                                class="form-label"
                                for="f_gambar">
                                Gambar <span class="text-danger">*</span>
                            </label>

                            <div class="d-flex align-items-center gap-3">
                                <img
                                    id="gambarPreview"
                                    src=""
                                    alt=""
                                    class="thumb"
                                    style="display:none;"
                                    onclick="previewImage(this.src, 'Gambar {{ $label }}')">

                                <input
                                    type="file"
                                    id="f_gambar"
                                    name="gambar"
                                    accept=".jpg,.jpeg,.png,.webp"
                                    class="form-control @error('gambar') is-invalid @enderror">
                            </div>

                            <div class="form-text" id="gambarHint">
                                Format JPG, PNG, atau WEBP. Maksimal 2 MB.
                            </div>

                            @error('gambar')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    @endunless
                </div>
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-outline-silab"
                    data-bs-dismiss="modal">
                    Batal
                </button>

                <button
                    type="submit"
                    class="btn btn-primary">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

@include('kelola-katalog._preview')

@endsection

@push('styles')
<style>
    .tbl-katalog {
        table-layout: fixed;
        width: 100%;
    }

    .tbl-katalog th,
    .tbl-katalog td {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .tbl-katalog td.col-aksi {
        overflow: visible;
    }

    .tbl-katalog .col-nama,
    .tbl-katalog .col-nama_ruangan {
        width: 22%;
    }

    .tbl-katalog .col-nama_satuan {
        width: 11%;
    }

    .tbl-katalog .col-stok {
        width: 9%;
    }

    .tbl-katalog .col-harga {
        width: 15%;
    }

    .tbl-katalog .col-aksi {
        width: 168px;
    }

    .tbl-katalog .btn-aksi {
        padding: 4px 10px;
        line-height: 1.4;
    }

    @media (max-width: 767.98px) {
        .tbl-katalog th,
        .tbl-katalog td {
            padding-left: .4rem;
            padding-right: .4rem;
        }

        .tbl-katalog .col-aksi {
            width: 84px;
        }

        .tbl-katalog .btn-label {
            display: none;
        }

        .tbl-katalog .btn-aksi {
            padding: 5px 8px;
        }

        .tbl-katalog .col-gambar {
            width: 68px !important;
        }

        .tbl-katalog .thumb,
        .tbl-katalog .thumb-empty {
            width: 44px;
            height: 44px;
        }
    }

    @media (max-width: 575.98px) {
        .tbl-katalog .col-no,
        .tbl-katalog .col-gambar {
            display: none;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    const itemFields = {!! json_encode($fieldNames) !!};
    const itemLabel = {!! json_encode($label) !!};

    function openItemModal(mode, btn, keepOld) {
        const form = document.getElementById('itemForm');
        const edit = mode === 'edit';
        const id = edit ? (btn.dataset ? btn.dataset.id : btn) : null;

        document.getElementById('itemModalTitle').textContent =
            (edit ? 'Edit ' : 'Tambah ') + itemLabel;

        document.getElementById('itemMethod').disabled = !edit;

        form.action = edit
            ? form.dataset.update.replace('__ID__', id) +
                '?' +
                new URLSearchParams(
                    JSON.parse(form.dataset.query || '{}')
                ).toString()
            : form.dataset.store;

        @unless($isRoom)
            const prev = document.getElementById('gambarPreview');
        @endunless

        if (!keepOld) {
            const item = edit ? JSON.parse(btn.dataset.item) : {};

            itemFields.forEach(n => {
                const el = document.getElementById('f_' + n);

                if (el) {
                    el.value = String(item[n] ?? '');
                }
            });

            @unless($isRoom)
                document.getElementById('f_gambar').value = '';

                prev.src = item.gambar_url || '';
                prev.style.display = item.gambar_url ? '' : 'none';

                document.getElementById('gambarHint').textContent =
                    (edit && item.gambar_url
                        ? 'Pilih file baru untuk mengganti gambar. '
                        : '') +
                    'Format JPG, PNG, atau WEBP. Maksimal 2 MB.';
            @endunless

            form.querySelectorAll('.is-invalid').forEach(el =>
                el.classList.remove('is-invalid')
            );

            form.querySelectorAll('.invalid-feedback').forEach(el =>
                el.remove()
            );
        }

        bootstrap.Modal
            .getOrCreateInstance(document.getElementById('itemModal'))
            .show();
    }

    @unless($isRoom)
        document.getElementById('f_gambar').addEventListener('change', function () {
            const prev = document.getElementById('gambarPreview');

            if (this.files[0]) {
                prev.src = URL.createObjectURL(this.files[0]);
                prev.style.display = '';
            }
        });
    @endunless

    document.addEventListener('DOMContentLoaded', function () {
        @if(session('open_modal'))
            openItemModal(
                '{{ session('open_modal')['mode'] }}',
                {!! session('open_modal')['id']
                    ? "'" . (int) session('open_modal')['id'] . "'"
                    : 'null' !!},
                true
            );
        @endif

        @if($errors->has('file'))
            bootstrap.Modal
                .getOrCreateInstance(document.getElementById('importModal'))
                .show();
        @endif
    });
</script>
@endpush