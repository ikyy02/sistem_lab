@extends('layouts.app')

@section('title', 'Kelola Data User')
@section('page-title', 'Kelola Data User')
@section('page-subtitle', 'Kelola akun pengguna berdasarkan kategori.')

@section('content')
@php
    $label = $labels[$kategori];
    $fields = $config['fields'];
    $listQuery = request()->only(['search', 'sort', 'direction', 'per_page', 'page']);
    $openModal = session('open_modal');
    $sortLabels = collect($config['columns'])->filter(fn ($c) => $c[1])->map(fn ($c) => $c[0]);
    $colspan = count($config['columns']) + 2;
    $modalQuery = request()->only(['search', 'sort', 'direction', 'per_page', 'page', 'kategori']);
    $fieldNames = array_column($fields, 'name');
@endphp

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="font-display fw-semibold mb-1" style="font-size:1.5rem;">Kelola Data User</h2>
        <p class="mb-0" style="font-size:.83rem;color:var(--text-muted);">Data setiap kategori dikelola secara terpisah.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-outline-silab d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#importModal"><i class="bi bi-file-earmark-arrow-up"></i> Import</button>
        <button type="button" class="btn btn-primary d-flex align-items-center gap-2" onclick="openUserModal('create')">
            <i class="bi bi-plus-lg"></i> Tambah {{ $label }}
        </button>
    </div>
</div>

<!-- Kategori -->
<ul class="nav nav-pills gap-2 mb-3">
    @foreach($labels as $key => $text)
        <li class="nav-item">
            <a href="{{ route('kelola-user.index', ['kategori' => $key]) }}"
               class="nav-link px-4 {{ $kategori === $key ? 'active-tab' : '' }}"
               style="{{ $kategori === $key ? '' : 'background:#fff;border:1px solid var(--border-color);color:#3d475a;font-weight:500;' }} border-radius:8px;font-size:.87rem;">
                {{ $text }}
            </a>
        </li>
    @endforeach
</ul>

@include('kelola-user._import_report')

<!-- Toolbar -->
<form method="GET" action="{{ route('kelola-user.index') }}" class="card-modern p-3 mb-3">
    <input type="hidden" name="kategori" value="{{ $kategori }}">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-lg-4">
            <label class="form-label" for="search">Pencarian</label>
            <div class="input-group">
                <span class="input-group-text bg-white" style="border-color:var(--border-color);"><i class="bi bi-search"></i></span>
                <input type="text" id="search" name="search" value="{{ $search }}" class="form-control" maxlength="100"
                       placeholder="Cari {{ strtolower($label) }}: {{ implode(', ', array_slice(array_map(fn($c) => strtolower($c[0]), $config['columns']), 0, 3)) }}...">
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <label class="form-label" for="sort">Urutkan</label>
            <select id="sort" name="sort" class="form-select">
                @foreach($sortLabels as $key => $text)
                    <option value="{{ $key }}" @selected($sort === $key)>{{ $text }}</option>
                @endforeach
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
                @foreach($perPageOptions as $n)
                    <option value="{{ $n }}" @selected($perPage === $n)>{{ $n }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-lg-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">Terapkan</button>
            <a href="{{ route('kelola-user.index', ['kategori' => $kategori]) }}" class="btn btn-outline-silab" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </div>
</form>

<!-- Tabel -->
<div class="card-modern overflow-hidden">
    <div class="table-responsive">
        <table class="table table-silab mb-0">
            <thead>
                <tr>
                    <th class="ps-4" style="width:56px;">No</th>
                    @foreach($config['columns'] as $col => [$text, $sortable])
                        <th>
                            @if($sortable)
                                @php $next = ($sort === $col && $direction === 'asc') ? 'desc' : 'asc'; @endphp
                                <a class="sort-link {{ $sort === $col ? 'active' : '' }}"
                                   href="{{ route('kelola-user.index', array_merge(request()->query(), ['kategori' => $kategori, 'sort' => $col, 'direction' => $next, 'page' => null])) }}">
                                    {{ $text }}
                                    <i class="bi {{ $sort === $col ? ($direction === 'asc' ? 'bi-sort-up' : 'bi-sort-down') : 'bi-arrow-down-up' }}" style="font-size:.7rem;"></i>
                                </a>
                            @else
                                {{ $text }}
                            @endif
                        </th>
                    @endforeach
                    <th class="text-end pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($data as $item)
                @php
                    $payload = collect($fields)->mapWithKeys(fn ($f) => [$f['name'] => $item->{$f['name']}])->all();
                @endphp
                <tr>
                    <td class="ps-4 text-muted">{{ $data->firstItem() + $loop->index }}</td>
                    @foreach($config['columns'] as $col => $meta)
                        <td>
                            @if($col === 'nama')
                                <span class="fw-semibold">{{ $item->nama }}</span>
                            @elseif(in_array($col, ['nuptk_nidn', 'id_pegawai'], true))
                                <span class="fw-semibold">{{ $item->{$col} }}</span>
                            @elseif($col === 'nim')
                                <span class="fw-semibold" style="color:var(--primary)">{{ $item->nim }}</span>
                            @else
                                {{ $item->{$col} ?? '—' }}
                            @endif
                        </td>
                    @endforeach
                    <td class="text-end pe-4">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-edit d-flex align-items-center gap-1"
                                    data-id="{{ $item->getKey() }}" data-item="{{ json_encode($payload) }}"
                                    onclick="openUserModal('edit', this)"><i class="bi bi-pencil"></i> Edit</button>
                            <form action="{{ route('kelola-user.destroy', ['kategori' => $kategori, 'key' => $item->getKey()] + $listQuery) }}" method="POST"
                                  onsubmit="return confirm('Hapus data {{ $label }} &quot;{{ e($item->nama) }}&quot;? Tindakan ini tidak dapat dibatalkan.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-del d-flex align-items-center gap-1"><i class="bi bi-trash"></i> Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $colspan }}" class="text-center py-5" style="color:var(--text-muted);">
                        <i class="bi bi-inbox fs-1 d-block mb-2" style="color:#c3cbdb;"></i>
                        @if($search !== '')
                            Tidak ada data {{ $label }} yang cocok dengan “{{ $search }}”.
                        @else
                            Belum ada data {{ $label }}.
                        @endif
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div>{{ $data->total() > 0 ? 'Menampilkan ' . $data->firstItem() . '–' . $data->lastItem() . ' dari ' . $data->total() . ' data ' . $label : '0 data' }}</div>
        {{ $data->links('pagination.silab') }}
    </div>
</div>

<!-- Modal Tambah/Edit -->
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="userForm" class="modal-content" autocomplete="off"
              data-store="{{ route('kelola-user.store', $kategori) }}"
              data-update="{{ route('kelola-user.update', ['kategori' => $kategori, 'key' => '__ID__']) }}"
              data-query="{{ json_encode($modalQuery) }}">
            @csrf
            <input type="hidden" name="_method" id="userMethod" value="POST" disabled>
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="userModalTitle" style="font-size:1rem;">Tambah {{ $label }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    @foreach($fields as $f)
                        <div class="col-12 {{ in_array($f['name'], ['nama', 'email', 'id_prodi']) ? '' : 'col-md-6' }}">
                            <label class="form-label" for="f_{{ $f['name'] }}">{{ $f['label'] }} @if($f['required'])<span class="text-danger">*</span>@endif</label>
                            @if(($f['type'] ?? '') === 'select')
                                <select id="f_{{ $f['name'] }}" name="{{ $f['name'] }}" class="form-select @error($f['name']) is-invalid @enderror">
                                    <option value="">{{ $f['placeholder'] }}</option>
                                    @foreach($f['options'] as $val => $opt)
                                        <option value="{{ $val }}" @selected((string) old($f['name']) === (string) $val)>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="{{ $f['type'] }}" id="f_{{ $f['name'] }}" name="{{ $f['name'] }}" value="{{ old($f['name']) }}"
                                       class="form-control @error($f['name']) is-invalid @enderror" placeholder="{{ $f['placeholder'] }}">
                            @endif
                            @error($f['name'])<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    @endforeach
                    <div class="col-12">
                        <label class="form-label" for="f_password">Password <span class="text-danger" id="pwStar">*</span></label>
                        <input type="text" id="f_password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 4 karakter">
                        <div class="form-text" id="pwHint" style="display:none;">Kosongkan jika password tidak diubah.</div>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-silab" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" id="userSubmit">Simpan</button>
            </div>
        </form>
    </div>
</div>

@include('kelola-user._import_modal')
@endsection

@push('scripts')
<script>
    const userFields = {!! json_encode($fieldNames) !!};
    const userLabel = {!! json_encode($label) !!};

    function openUserModal(mode, btn, keepOld) {
        const form = document.getElementById('userForm');
        const edit = mode === 'edit';
        const id = edit ? (btn.dataset ? btn.dataset.id : btn) : null;

        document.getElementById('userModalTitle').textContent = (edit ? 'Edit ' : 'Tambah ') + userLabel;
        document.getElementById('userMethod').value = 'PUT';
        document.getElementById('userMethod').disabled = !edit;
        document.getElementById('pwStar').style.display = edit ? 'none' : '';
        document.getElementById('pwHint').style.display = edit ? '' : 'none';

        if (edit) {
            const q = new URLSearchParams(JSON.parse(form.dataset.query || '{}'));
            form.action = form.dataset.update.replace('__ID__', encodeURIComponent(id)) + '?' + q.toString();
        } else {
            form.action = form.dataset.store;
        }

        if (!keepOld) {
            const item = edit ? JSON.parse(btn.dataset.item) : {};
            userFields.forEach(n => {
                const el = document.getElementById('f_' + n);
                const v = String(item[n] ?? '');
                el.value = v;
            });
            document.getElementById('f_password').value = '';
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
        }

        bootstrap.Modal.getOrCreateInstance(document.getElementById('userModal')).show();
    }

    @if($openModal)
    document.addEventListener('DOMContentLoaded', function () {
        openUserModal('{{ $openModal['mode'] }}', {!! $openModal['id'] ? json_encode((string) $openModal['id']) : 'null' !!}, true);
    });
    @endif
</script>
@endpush
