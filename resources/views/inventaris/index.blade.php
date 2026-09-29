@extends('layouts.app')

@section('title', 'Kelola ' . $labels[$kategori])
@section('page-title', 'Kelola Inventaris')
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
    $colspan = count($config['columns']) + 3;
@endphp

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="font-display fw-semibold mb-1" style="font-size:1.5rem;">Kelola {{ $label }}</h2>
        <p class="mb-0" style="font-size:.83rem;color:var(--text-muted);">Lokasi barang dituliskan pada kolom Keterangan.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @unless($isRoom)
            <button type="button" class="btn btn-outline-silab d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#satuanModal"><i class="bi bi-rulers"></i> Kelola Satuan</button>
        @endunless
        <button type="button" class="btn btn-outline-silab d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#importModal"><i class="bi bi-file-earmark-arrow-up"></i> Import</button>
        <button type="button" class="btn btn-primary d-flex align-items-center gap-2" onclick="openItemModal('create')"><i class="bi bi-plus-lg"></i> Tambah {{ $label }}</button>
    </div>
</div>

<ul class="nav nav-pills gap-2 mb-3">
    @foreach($labels as $key => $text)
        <li class="nav-item">
            <a href="{{ route('inventaris.index', ['kategori' => $key]) }}" class="nav-link px-4 {{ $kategori === $key ? 'active-tab' : '' }}"
               style="{{ $kategori === $key ? '' : 'background:#fff;border:1px solid var(--border-color);color:#3d475a;font-weight:500;' }} border-radius:8px;font-size:.87rem;">{{ $text }}</a>
        </li>
    @endforeach
</ul>

@if($report && ! empty($report['errors']))
<div class="card-modern overflow-hidden mb-3 import-report" style="border-color:#fecdd3;">
    <div class="px-4 py-3 d-flex align-items-start gap-3" style="background:#fff1f2;border-bottom:1px solid #fecdd3;">
        <i class="bi bi-exclamation-triangle-fill" style="color:#be123c;font-size:1.2rem;"></i>
        <div class="flex-grow-1">
            <div class="fw-bold" style="font-size:.92rem;color:#9f1239;">Import dibatalkan: {{ $report['error_count'] }} dari {{ $report['total'] }} baris bermasalah</div>
            <div style="font-size:.8rem;color:#be123c;">Tidak ada data yang disimpan. Perbaiki baris berikut lalu unggah ulang.</div>
        </div>
        <button type="button" class="btn-close" aria-label="Tutup" onclick="this.closest('.import-report').remove()"></button>
    </div>
    <div class="table-responsive" style="max-height:320px;overflow-y:auto;">
        <table class="table table-sm mb-0" style="font-size:.82rem;">
            <thead><tr><th class="ps-4" style="width:90px;">Baris</th><th style="width:220px;">Nama</th><th>Alasan</th></tr></thead>
            <tbody>
            @foreach($report['errors'] as $e)
                <tr>
                    <td class="ps-4 fw-semibold" style="color:#be123c;">{{ $e['row'] }}</td>
                    <td>{{ $e['nama'] !== '' ? $e['nama'] : '—' }}</td>
                    <td><ul class="mb-0 ps-3">@foreach($e['messages'] as $m)<li>{{ $m }}</li>@endforeach</ul></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<form method="GET" action="{{ route('inventaris.index') }}" class="card-modern p-3 mb-3">
    <input type="hidden" name="kategori" value="{{ $kategori }}">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-lg-4">
            <label class="form-label" for="search">Pencarian</label>
            <div class="input-group">
                <span class="input-group-text bg-white" style="border-color:var(--border-color);"><i class="bi bi-search"></i></span>
                <input type="text" id="search" name="search" value="{{ $search }}" maxlength="100" class="form-control" placeholder="Cari {{ strtolower($label) }}...">
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <label class="form-label" for="sort">Urutkan</label>
            <select id="sort" name="sort" class="form-select">
                @foreach($sortLabels as $key => $text)<option value="{{ $key }}" @selected($sort === $key)>{{ $text }}</option>@endforeach
            </select>
        </div>
        <div class="col-6 col-lg-2">
            <label class="form-label" for="direction">Arah</label>
            <select id="direction" name="direction" class="form-select">
                <option value="asc" @selected($direction === 'asc')>A → Z (Naik)</option>
                <option value="desc" @selected($direction === 'desc')>Z → A (Turun)</option>
            </select>
        </div>
        <div class="col-6 col-lg-1">
            <label class="form-label" for="per_page">Tampil</label>
            <select id="per_page" name="per_page" class="form-select">
                @foreach($perPageOptions as $n)<option value="{{ $n }}" @selected($perPage === $n)>{{ $n }}</option>@endforeach
            </select>
        </div>
        <div class="col-6 col-lg-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">Terapkan</button>
            <a href="{{ route('inventaris.index', ['kategori' => $kategori]) }}" class="btn btn-outline-silab" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </div>
</form>

<div class="card-modern overflow-hidden">
    <div class="table-responsive">
        <table class="table table-silab mb-0">
            <thead>
                <tr>
                    <th class="ps-4" style="width:56px;">No</th>
                    <th style="width:84px;">Gambar</th>
                    @foreach($config['columns'] as $col => [$text, $sortable])
                        <th>
                            @if($sortable)
                                @php $next = ($sort === $col && $direction === 'asc') ? 'desc' : 'asc'; @endphp
                                <a class="sort-link {{ $sort === $col ? 'active' : '' }}" href="{{ route('inventaris.index', array_merge(request()->query(), ['kategori' => $kategori, 'sort' => $col, 'direction' => $next, 'page' => null])) }}">
                                    {{ $text }} <i class="bi {{ $sort === $col ? ($direction === 'asc' ? 'bi-sort-up' : 'bi-sort-down') : 'bi-arrow-down-up' }}" style="font-size:.7rem;"></i>
                                </a>
                            @else {{ $text }} @endif
                        </th>
                    @endforeach
                    <th class="text-end pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($data as $item)
                @php
                    $payload = collect($fieldNames)->mapWithKeys(fn ($n) => [$n => $item->{$n}])->all() + ['gambar_url' => $item->gambar_url];
                    $bad = in_array($item->kondisi, ['Rusak', 'Hilang', 'Kadaluarsa', 'Tidak Tersedia'], true);
                @endphp
                <tr>
                    <td class="ps-4 text-muted">{{ $data->firstItem() + $loop->index }}</td>
                    <td>
                        @if($item->gambar_url)
                            <img src="{{ $item->gambar_url }}" alt="{{ $item->nama }}" class="thumb" loading="lazy" title="Klik untuk memperbesar"
                                 onclick="previewImage(this.src, this.alt)">
                        @else
                            <span class="thumb-empty"><i class="bi bi-image"></i></span>
                        @endif
                    </td>
                    @foreach($config['columns'] as $col => $meta)
                        <td>
                            @if($col === 'nama') <span class="fw-semibold">{{ $item->nama }}</span>
                            @elseif($col === 'kondisi')
                                @if($item->kondisi)
                                    <span class="badge rounded-pill" style="font-weight:600;padding:5px 12px;{{ $bad ? 'background:#fef2f2;color:#b42318;' : 'background:#ecfdf3;color:#146c43;' }}">{{ $item->kondisi }}</span>
                                @else — @endif
                            @elseif($col === 'keterangan')
                                <span class="d-inline-block text-truncate align-middle" style="max-width:240px;color:var(--text-muted);" title="{{ $item->keterangan }}">{{ $item->keterangan ?? '—' }}</span>
                            @else {{ $item->{$col} !== '' && $item->{$col} !== null ? $item->{$col} : '—' }} @endif
                        </td>
                    @endforeach
                    <td class="text-end pe-4">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-edit d-flex align-items-center gap-1" data-id="{{ $item->id }}" data-item="{{ json_encode($payload) }}" onclick="openItemModal('edit', this)"><i class="bi bi-pencil"></i> Edit</button>
                            <form action="{{ route('inventaris.destroy', ['kategori' => $kategori, 'id' => $item->id] + $listQuery) }}" method="POST"
                                  onsubmit="return confirm('Hapus {{ strtolower($label) }} &quot;{{ e($item->nama) }}&quot;? Tindakan ini tidak dapat dibatalkan.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-del d-flex align-items-center gap-1"><i class="bi bi-trash"></i> Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ $colspan }}" class="text-center py-5" style="color:var(--text-muted);">
                    <i class="bi bi-inbox fs-1 d-block mb-2" style="color:#c9bdb4;"></i>
                    {{ $search !== '' ? 'Tidak ada ' . strtolower($label) . ' yang cocok dengan “' . $search . '”.' : 'Belum ada data ' . strtolower($label) . '.' }}
                </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 px-4 py-3" style="border-top:1px solid var(--border-color);background:#fbfaf9;">
        <div style="font-size:.8rem;color:var(--text-muted);">
            {{ $data->total() > 0 ? 'Menampilkan ' . $data->firstItem() . '–' . $data->lastItem() . ' dari ' . $data->total() . ' data ' . $label : '0 data' }}
        </div>
        {{ $data->links('pagination.silab') }}
    </div>
</div>

<!-- Modal Tambah/Edit -->
<div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="itemForm" enctype="multipart/form-data" class="modal-content" autocomplete="off"
              data-store="{{ route('inventaris.store', $kategori) }}"
              data-update="{{ route('inventaris.update', ['kategori' => $kategori, 'id' => '__ID__']) }}"
              data-query="{{ json_encode($modalQuery) }}">
            @csrf
            <input type="hidden" name="_method" id="itemMethod" value="PUT" disabled>
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="itemModalTitle" style="font-size:1rem;">Tambah {{ $label }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    @foreach($fields as $f)
                        <div class="col-12 {{ in_array($f['name'], ['stok', 'satuan', 'kondisi']) ? 'col-md-6' : '' }}">
                            <label class="form-label" for="f_{{ $f['name'] }}">{{ $f['label'] }}@if($f['name'] !== 'keterangan') <span class="text-danger">*</span>@endif</label>
                            @if(in_array($f['type'], ['select', 'satuan']))
                                <select id="f_{{ $f['name'] }}" name="{{ $f['name'] }}" class="form-select @error($f['name']) is-invalid @enderror">
                                    <option value="">{{ $f['placeholder'] }}</option>
                                    @foreach($f['options'] as $opt)<option value="{{ $opt }}" @selected(old($f['name']) === $opt)>{{ $opt }}</option>@endforeach
                                </select>
                            @elseif($f['type'] === 'textarea')
                                <textarea id="f_{{ $f['name'] }}" name="{{ $f['name'] }}" rows="3" class="form-control @error($f['name']) is-invalid @enderror" placeholder="{{ $f['placeholder'] }}">{{ old($f['name']) }}</textarea>
                            @else
                                <input type="{{ $f['type'] }}" id="f_{{ $f['name'] }}" name="{{ $f['name'] }}" value="{{ old($f['name']) }}" @if($f['type'] === 'number') min="0" @endif class="form-control @error($f['name']) is-invalid @enderror" placeholder="{{ $f['placeholder'] }}">
                            @endif
                            @error($f['name'])<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    @endforeach
                    <div class="col-12">
                        <label class="form-label" for="f_gambar">Gambar</label>
                        <div class="d-flex align-items-center gap-3">
                            <img id="gambarPreview" src="" alt="" class="thumb" style="display:none;" onclick="previewImage(this.src, 'Gambar {{ $label }}')">
                            <input type="file" id="f_gambar" name="gambar" accept=".jpg,.jpeg,.png,.webp" class="form-control @error('gambar') is-invalid @enderror">
                        </div>
                        <div class="form-text" id="gambarHint">Format JPG, PNG, atau WEBP. Maksimal 2 MB.</div>
                        @error('gambar')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-silab" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('inventaris.import', $kategori) }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold" style="font-size:1rem;">Import Data {{ $label }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4">
                <ol class="ps-3 mb-3" style="font-size:.84rem;line-height:1.8;">
                    <li>Unduh template:
                        <a href="{{ route('inventaris.template', ['kategori' => $kategori, 'format' => 'xlsx']) }}" class="fw-semibold">Excel (.xlsx)</a> atau
                        <a href="{{ route('inventaris.template', ['kategori' => $kategori, 'format' => 'csv']) }}" class="fw-semibold">CSV</a>.
                    </li>
                    <li>Isi data mulai baris ke-2 ({{ implode(', ', array_keys(\App\Services\InventarisImportService::columns($kategori))) }}).</li>
                    <li>Pilih file lalu klik <strong>Import</strong>. Data yang sudah ada tidak akan diubah.</li>
                </ol>
                <label class="form-label" for="importFile">File Excel / CSV <span class="text-danger">*</span></label>
                <input type="file" id="importFile" name="file" accept=".xlsx,.xls,.csv" class="form-control @error('file') is-invalid @enderror">
                @error('file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-silab" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Import</button>
            </div>
        </form>
    </div>
</div>

@unless($isRoom)
<!-- Modal Satuan -->
<div class="modal fade" id="satuanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" style="font-size:1rem;">Kelola Satuan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('satuan.store') }}" method="POST" class="d-flex gap-2 mb-3">
                    @csrf
                    <input type="hidden" name="kategori" value="{{ $kategori }}">
                    <input type="text" name="nama" class="form-control" maxlength="50" placeholder="Nama satuan baru" required>
                    <button type="submit" class="btn btn-primary text-nowrap"><i class="bi bi-plus-lg"></i> Tambah</button>
                </form>
                <div style="max-height:320px;overflow-y:auto;">
                    @forelse($satuans as $s)
                        <div class="d-flex gap-2 align-items-center mb-2">
                            <form action="{{ route('satuan.update', $s->id) }}" method="POST" class="d-flex gap-2 flex-grow-1">
                                @csrf @method('PUT')
                                <input type="hidden" name="kategori" value="{{ $kategori }}">
                                <input type="text" name="nama" value="{{ $s->nama }}" class="form-control form-control-sm" maxlength="50" required>
                                <button type="submit" class="btn btn-edit" title="Simpan perubahan"><i class="bi bi-check-lg"></i></button>
                            </form>
                            <span class="badge badge-tab" title="Jumlah data yang memakai" style="min-width:34px;">{{ $satuanUsage[$s->nama] ?? 0 }}</span>
                            <form action="{{ route('satuan.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Hapus satuan &quot;{{ e($s->nama) }}&quot;?')">
                                @csrf @method('DELETE')
                                <input type="hidden" name="kategori" value="{{ $kategori }}">
                                <button type="submit" class="btn btn-del" title="Hapus"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    @empty
                        <div class="text-center py-3" style="color:var(--text-muted);">Belum ada satuan.</div>
                    @endforelse
                </div>
                <div class="form-text mt-2">Angka pada lencana = jumlah Alat/Bahan yang memakai satuan. Satuan yang sedang dipakai tidak dapat dihapus.</div>
            </div>
        </div>
    </div>
</div>
@endunless

@include('inventaris._preview')
@endsection

@push('scripts')
<script>
    const itemFields = {!! json_encode($fieldNames) !!};
    const itemLabel = {!! json_encode($label) !!};

    function openItemModal(mode, btn, keepOld) {
        const form = document.getElementById('itemForm');
        const edit = mode === 'edit';
        const id = edit ? (btn.dataset ? btn.dataset.id : btn) : null;

        document.getElementById('itemModalTitle').textContent = (edit ? 'Edit ' : 'Tambah ') + itemLabel;
        document.getElementById('itemMethod').disabled = !edit;
        form.action = edit
            ? form.dataset.update.replace('__ID__', id) + '?' + new URLSearchParams(JSON.parse(form.dataset.query || '{}')).toString()
            : form.dataset.store;

        const prev = document.getElementById('gambarPreview');
        if (!keepOld) {
            const item = edit ? JSON.parse(btn.dataset.item) : {};
            itemFields.forEach(n => {
                const el = document.getElementById('f_' + n);
                const v = item[n] ?? '';
                if (el.tagName === 'SELECT' && v !== '' && ![...el.options].some(o => o.value === v)) el.add(new Option(v, v));
                el.value = v;
            });
            document.getElementById('f_gambar').value = '';
            prev.src = item.gambar_url || '';
            prev.style.display = item.gambar_url ? '' : 'none';
            document.getElementById('gambarHint').textContent = (edit && item.gambar_url ? 'Pilih file baru untuk mengganti gambar. ' : '') + 'Format JPG, PNG, atau WEBP. Maksimal 2 MB.';
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
        }
        bootstrap.Modal.getOrCreateInstance(document.getElementById('itemModal')).show();
    }

    document.getElementById('f_gambar').addEventListener('change', function () {
        const prev = document.getElementById('gambarPreview');
        if (this.files[0]) { prev.src = URL.createObjectURL(this.files[0]); prev.style.display = ''; }
    });

    document.addEventListener('DOMContentLoaded', function () {
        @if(session('open_modal'))
            openItemModal('{{ session('open_modal')['mode'] }}', {!! session('open_modal')['id'] ? "'" . (int) session('open_modal')['id'] . "'" : 'null' !!}, true);
        @endif
        @if(session('open_satuan') && $kategori !== 'ruangan')
            bootstrap.Modal.getOrCreateInstance(document.getElementById('satuanModal')).show();
        @endif
        @if($errors->has('file'))
            bootstrap.Modal.getOrCreateInstance(document.getElementById('importModal')).show();
        @endif
    });
</script>
@endpush
