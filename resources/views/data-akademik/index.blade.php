@extends('layouts.app')

@section('title', 'Data Akademik')
@section('page-title', 'Data Akademik')
@section('page-subtitle', 'Kelola mata kuliah, kelas, semester, dan dosen Program Studi.')

@php
    $label = $tabs[$tab];
    $listQuery = request()->only(['tab', 'search', 'per_page', 'page']);
    $isSemester = $tab === 'semester';
    $open = $openModal;
@endphp

@section('content')

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="font-display fw-semibold mb-1" style="font-size:1.5rem;">Data Akademik</h2>
        <p class="mb-0" style="font-size:.83rem;color:var(--text-muted);">
            Mata kuliah, kelas, dan dosen dipakai pada form Jadwal Perkuliahan; perubahan di sini langsung mengikuti dropdown jadwal.
        </p>
    </div>
    @unless($isSemester)
    <button type="button" class="btn btn-primary d-flex align-items-center gap-2"
            onclick="openAkademikModal('create')"><i class="bi bi-plus-lg"></i> Tambah {{ $label }}</button>
    @endunless
</div>

{{-- Tab --}}
<ul class="nav nav-pills gap-2 mb-3">
    @foreach($tabs as $key => $text)
        <li class="nav-item">
            <a href="{{ route('data-akademik.index', ['tab' => $key]) }}"
               class="nav-link px-4 {{ $tab === $key ? 'active-tab' : '' }}"
               style="{{ $tab === $key ? '' : 'background:#fff;border:1px solid var(--border-color);color:#3d475a;font-weight:500;' }} border-radius:8px;font-size:.87rem;">{{ $text }}</a>
        </li>
    @endforeach
</ul>

@if($isSemester)

    {{-- Semester: nilai tetap sistem (Ganjil/Genap) + ringkasan pemakaian --}}
    <div class="row g-3 mb-3">
        @foreach($semesterInfo as $s)
        <div class="col-12 col-md-6">
            <div class="card-modern p-4 h-100">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:44px;height:44px;border-radius:10px;background:var(--gold-tint);color:var(--gold);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-calendar2 fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size:1.1rem;">Semester {{ $s['label'] }}</div>
                        <div style="font-size:.78rem;color:var(--text-muted);">Nilai tetap sistem</div>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between py-2" style="border-top:1px solid #EDF1F6;">
                    <span style="font-size:.85rem;">Jadwal perkuliahan</span>
                    <span class="badge-status badge-tab" style="font-size:.75rem;">{{ $s['jadwal'] }}</span>
                </div>
                <div class="d-flex align-items-center justify-content-between py-2" style="border-top:1px solid #EDF1F6;">
                    <span style="font-size:.85rem;">Mata kuliah</span>
                    <span class="badge-status badge-tab" style="font-size:.75rem;">{{ $s['mata_kuliah'] }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="card-modern p-4">
        <div class="d-flex gap-3">
            <i class="bi bi-info-circle-fill fs-5" style="color:var(--gold);"></i>
            <div style="font-size:.85rem;color:var(--text-muted);">
                Semester hanya bernilai <strong>Ganjil</strong> atau <strong>Genap</strong> dan ditetapkan oleh sistem,
                sehingga tidak dapat ditambah atau diubah. Nilai ini dipakai pada setiap data Jadwal Perkuliahan
                bersama tahun akademik (contoh: 2026/2027).
            </div>
        </div>
    </div>

@else

{{-- Pencarian --}}
<form method="GET" action="{{ route('data-akademik.index') }}" class="card-modern p-3 mb-3">
    <input type="hidden" name="tab" value="{{ $tab }}">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-lg-6">
            <label class="form-label" for="search">Pencarian</label>
            <div class="input-group">
                <span class="input-group-text bg-white" style="border-color:var(--border-color);"><i class="bi bi-search"></i></span>
                <input type="text" id="search" name="search" value="{{ $search }}" maxlength="100" class="form-control"
                       placeholder="Cari {{ strtolower($label) }}...">
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
            <a href="{{ route('data-akademik.index', ['tab' => $tab]) }}" class="btn btn-outline-silab" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </div>
</form>

<div class="card-modern overflow-hidden">
    <div class="table-responsive">
        <table class="table table-silab mb-0">
            <thead>
                <tr>
                    <th class="ps-4" style="width:56px;">No</th>
                    @if($tab === 'mata-kuliah')
                        <th style="width:130px;">Kode</th>
                        <th>Mata Kuliah</th>
                        <th style="width:80px;">SKS</th>
                        <th style="width:120px;">Semester</th>
                    @elseif($tab === 'kelas')
                        <th style="width:140px;">Kelas</th>
                        <th>Prodi</th>
                    @else
                        <th style="width:150px;">NUPTK/NIDN</th>
                        <th>Nama Dosen</th>
                        <th>Prodi</th>
                        <th>Email</th>
                        <th style="width:150px;">No. Telepon</th>
                        <th style="width:100px;">Status</th>
                    @endif
                    <th class="text-end pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($data as $item)
                @php
                    $itemKey = $item->getKey();
                    $itemName = $tab === 'mata-kuliah' ? $item->nama_mk : ($tab === 'kelas' ? $item->nama_kelas : $item->nama);
                    $itemData = $tab === 'mata-kuliah'
                        ? ['kode_mk' => $item->kode_mk, 'nama_mk' => $item->nama_mk, 'sks' => $item->sks, 'semester' => $item->semester]
                        : ($tab === 'kelas'
                            ? ['nama_kelas' => $item->nama_kelas, 'id_prodi' => $item->id_prodi]
                            : ['nuptk_nidn' => $item->nuptk_nidn, 'nama' => $item->nama, 'id_prodi' => $item->id_prodi, 'email' => $item->email, 'no_whatsapp' => $item->no_whatsapp, 'status' => $item->status ?? 'aktif']);
                @endphp
                <tr>
                    <td class="ps-4 text-muted">{{ $data->firstItem() + $loop->index }}</td>
                    @if($tab === 'mata-kuliah')
                        <td><span class="badge-status badge-tab" style="font-size:.72rem;">{{ $item->kode_mk }}</span></td>
                        <td>
                            <span class="fw-semibold">{{ $item->nama_mk }}</span>
                        </td>
                        <td>{{ $item->sks }}</td>
                        <td class="text-capitalize">{{ $item->semester }}</td>
                    @elseif($tab === 'kelas')
                        <td><span class="fw-semibold">{{ $item->nama_kelas }}</span></td>
                        <td>{{ $item->nama_prodi ?? '—' }}</td>
                    @else
                        <td><span class="badge-status badge-tab" style="font-size:.72rem;">{{ $item->nuptk_nidn }}</span></td>
                        <td>
                            <span class="fw-semibold">{{ $item->nama }}</span>
                        </td>
                        <td>{{ $item->nama_prodi ?? '—' }}</td>
                        <td>{{ $item->email }}</td>
                        <td>{{ $item->no_whatsapp ?? '—' }}</td>
                        <td>
                            @if(($item->status ?? 'aktif') === 'nonaktif')
                                <span class="badge-status badge-status-bad" style="font-size:.68rem;">Nonaktif</span>
                            @else
                                <span class="badge-status badge-tab" style="font-size:.68rem;">Aktif</span>
                            @endif
                        </td>
                    @endif
                    <td class="text-end pe-4">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-edit d-flex align-items-center gap-1"
                                    data-id="{{ $itemKey }}"
                                    data-item="{{ json_encode($itemData) }}"
                                    onclick="openAkademikModal('edit', this)"><i class="bi bi-pencil"></i> Edit</button>
                            <form action="{{ route('data-akademik.destroy', ['tab' => $tab, 'id' => $itemKey] + $listQuery) }}" method="POST"
                                  onsubmit="return confirm('Hapus {{ strtolower($label) }} &quot;{{ e($itemName) }}&quot;?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-del d-flex align-items-center gap-1"><i class="bi bi-trash"></i> Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ $tab === 'dosen' ? 7 : 6 }}" class="text-center py-5" style="color:var(--text-muted);">
                    <i class="bi bi-inbox fs-1 d-block mb-2" style="color:#c3cbdb;"></i>
                    {{ $search !== '' ? 'Tidak ada ' . strtolower($label) . ' yang cocok dengan “' . $search . '”.' : 'Belum ada data ' . strtolower($label) . '.' }}
                </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="table-footer">
        <div>{{ $data->total() > 0 ? 'Menampilkan ' . $data->firstItem() . '–' . $data->lastItem() . ' dari ' . $data->total() . ' data ' . strtolower($label) : '0 data' }}</div>
        {{ $data->links('pagination.silab') }}
    </div>
</div>

{{-- Modal Mata Kuliah --}}
<div class="modal fade" id="mkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="mkForm" class="modal-content" autocomplete="off"
              data-store="{{ route('data-akademik.store', ['tab' => 'mata-kuliah']) }}"
              data-update="{{ route('data-akademik.update', ['tab' => 'mata-kuliah', 'id' => '__ID__']) }}"
              data-query="{{ json_encode($listQuery) }}">
            @csrf
            <input type="hidden" name="_method" id="mkMethod" value="PUT" disabled>
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="mkModalTitle" style="font-size:1rem;">Tambah Mata Kuliah</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-12 col-sm-6">
                        <label class="form-label" for="f_kode_mk">Kode Mata Kuliah <span class="text-danger">*</span></label>
                        <input type="text" id="f_kode_mk" name="kode_mk" value="{{ old('kode_mk') }}" maxlength="15"
                               placeholder="Contoh: IF-101" class="form-control @error('kode_mk') is-invalid @enderror">
                        @error('kode_mk')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label" for="f_sks">SKS <span class="text-danger">*</span></label>
                        <input type="number" id="f_sks" name="sks" value="{{ old('sks') }}" min="1" max="10"
                               placeholder="2" class="form-control @error('sks') is-invalid @enderror">
                        @error('sks')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="f_nama_mk">Nama Mata Kuliah <span class="text-danger">*</span></label>
                        <input type="text" id="f_nama_mk" name="nama_mk" value="{{ old('nama_mk') }}" maxlength="100"
                               placeholder="Contoh: Basis Data" class="form-control @error('nama_mk') is-invalid @enderror">
                        @error('nama_mk')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="f_semester_mk">Semester <span class="text-danger">*</span></label>
                        <select id="f_semester_mk" name="semester" class="form-select @error('semester') is-invalid @enderror">
                            <option value="ganjil" @selected(old('semester') === 'ganjil')>Ganjil</option>
                            <option value="genap" @selected(old('semester') === 'genap')>Genap</option>
                        </select>
                        @error('semester')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
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

{{-- Modal Kelas --}}
<div class="modal fade" id="kelasModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="kelasForm" class="modal-content" autocomplete="off"
              data-store="{{ route('data-akademik.store', ['tab' => 'kelas']) }}"
              data-update="{{ route('data-akademik.update', ['tab' => 'kelas', 'id' => '__ID__']) }}"
              data-query="{{ json_encode($listQuery) }}">
            @csrf
            <input type="hidden" name="_method" id="kelasMethod" value="PUT" disabled>
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="kelasModalTitle" style="font-size:1rem;">Tambah Kelas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="f_nama_kelas">Nama Kelas <span class="text-danger">*</span></label>
                        <input type="text" id="f_nama_kelas" name="nama_kelas" value="{{ old('nama_kelas') }}" maxlength="100"
                               placeholder="Contoh: 1A" class="form-control @error('nama_kelas') is-invalid @enderror">
                        @error('nama_kelas')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="f_id_prodi">Prodi <span class="text-danger">*</span></label>
                        <select id="f_id_prodi" name="id_prodi" class="form-select @error('id_prodi') is-invalid @enderror">
                            <option value="">— Pilih Prodi —</option>
                            @foreach($prodiOptions as $id => $teks)
                                <option value="{{ $id }}" @selected((string) old('id_prodi') === (string) $id)>{{ $teks }}</option>
                            @endforeach
                        </select>
                        @error('id_prodi')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
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

{{-- Modal Dosen --}}
<div class="modal fade" id="dosenModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="dosenForm" class="modal-content" autocomplete="off"
              data-store="{{ route('data-akademik.store', ['tab' => 'dosen']) }}"
              data-update="{{ route('data-akademik.update', ['tab' => 'dosen', 'id' => '__ID__']) }}"
              data-query="{{ json_encode($listQuery) }}">
            @csrf
            <input type="hidden" name="_method" id="dosenMethod" value="PUT" disabled>
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="dosenModalTitle" style="font-size:1rem;">Tambah Dosen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-12 col-sm-6">
                        <label class="form-label" for="f_ds_nuptk_nidn">NUPTK/NIDN <span class="text-danger">*</span></label>
                        <input type="text" id="f_ds_nuptk_nidn" name="nuptk_nidn" value="{{ old('nuptk_nidn') }}" maxlength="30"
                               placeholder="NUPTK atau NIDN" class="form-control @error('nuptk_nidn') is-invalid @enderror">
                        @error('nuptk_nidn')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label" for="f_ds_status">Status <span class="text-danger">*</span></label>
                        <select id="f_ds_status" name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="aktif" @selected(old('status') === 'aktif')>Aktif</option>
                            <option value="nonaktif" @selected(old('status') === 'nonaktif')>Nonaktif</option>
                        </select>
                        @error('status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="f_ds_nama">Nama Dosen <span class="text-danger">*</span></label>
                        <input type="text" id="f_ds_nama" name="nama" value="{{ old('nama') }}" maxlength="100"
                               placeholder="Nama lengkap" class="form-control @error('nama') is-invalid @enderror">
                        @error('nama')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="f_ds_id_prodi">Prodi <span class="text-danger">*</span></label>
                        <select id="f_ds_id_prodi" name="id_prodi" class="form-select @error('id_prodi') is-invalid @enderror">
                            <option value="">— Pilih Prodi —</option>
                            @foreach($prodiOptions as $id => $teks)
                                <option value="{{ $id }}" @selected((string) old('id_prodi') === (string) $id)>{{ $teks }}</option>
                            @endforeach
                        </select>
                        @error('id_prodi')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="f_ds_email">Email <span class="text-danger">*</span></label>
                        <input type="email" id="f_ds_email" name="email" value="{{ old('email') }}" maxlength="50"
                               placeholder="nama@domain.ac.id" class="form-control @error('email') is-invalid @enderror">
                        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="f_ds_wa">Nomor Telepon/WhatsApp</label>
                        <input type="text" id="f_ds_wa" name="no_whatsapp" value="{{ old('no_whatsapp') }}" maxlength="20"
                               placeholder="08xxxxxxxxxx" class="form-control @error('no_whatsapp') is-invalid @enderror">
                        @error('no_whatsapp')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="f_ds_password">Password Akun Login <span class="text-danger">*</span></label>
                        <input type="password" id="f_ds_password" name="password" maxlength="100" autocomplete="new-password"
                               value="{{ old('password') }}" placeholder="Minimal 4 karakter"
                               class="form-control @error('password') is-invalid @enderror">
                        @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        <div class="form-text">Password dipakai untuk akun login dosen. Saat mengubah data, kosongkan bila password tidak ingin diganti.</div>
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

@endif
@endsection

@unless($isSemester)
@push('scripts')
<script>
    const akademikLabel = {!! json_encode($label) !!};
    const akademikTab = {!! json_encode($tab) !!};
    const akadConfig = {
        'mata-kuliah': { modal: 'mkModal', form: 'mkForm', method: 'mkMethod', title: 'mkModalTitle', fields: ['f_kode_mk', 'f_nama_mk', 'f_sks', 'f_semester_mk'], keys: ['kode_mk', 'nama_mk', 'sks', 'semester'] },
        'kelas': { modal: 'kelasModal', form: 'kelasForm', method: 'kelasMethod', title: 'kelasModalTitle', fields: ['f_nama_kelas', 'f_id_prodi'], keys: ['nama_kelas', 'id_prodi'] },
        'dosen': { modal: 'dosenModal', form: 'dosenForm', method: 'dosenMethod', title: 'dosenModalTitle', fields: ['f_ds_nuptk_nidn', 'f_ds_nama', 'f_ds_id_prodi', 'f_ds_email', 'f_ds_wa', 'f_ds_status', 'f_ds_password'], keys: ['nuptk_nidn', 'nama', 'id_prodi', 'email', 'no_whatsapp', 'status', 'password'] },
    };
    const cfg = akadConfig[akademikTab];

    function openAkademikModal(mode, btn, keepOld, forcedId) {
        const form = document.getElementById(cfg.form);
        const methodEl = document.getElementById(cfg.method);
        const titleEl = document.getElementById(cfg.title);
        const edit = mode === 'edit';
        const id = forcedId !== undefined && forcedId !== null
            ? forcedId
            : (btn && btn.dataset && btn.dataset.id ? btn.dataset.id : null);

        titleEl.textContent = (edit ? 'Edit ' : 'Tambah ') + akademikLabel;
        methodEl.disabled = !edit;
        form.action = edit
            ? form.dataset.update.replace('__ID__', id) + '?' + new URLSearchParams(JSON.parse(form.dataset.query || '{}')).toString()
            : form.dataset.store;

        if (!keepOld) {
            const item = edit ? JSON.parse(btn.dataset.item) : {};
            cfg.keys.forEach((key, i) => {
                const el = document.getElementById(cfg.fields[i]);
                if (el) el.value = item[key] ?? '';
            });
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
        }
        bootstrap.Modal.getOrCreateInstance(document.getElementById(cfg.modal)).show();
    }

    @if($open && $open['tab'] === $tab)
    document.addEventListener('DOMContentLoaded', function () {
        openAkademikModal('{{ $open['mode'] }}', null, true, {!! json_encode($open['id']) !!});
    });
    @endif
</script>
@endpush
@endunless
