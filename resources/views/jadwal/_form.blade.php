{{-- Form bersama Tambah/Ubah Jadwal Perkuliahan. Variabel: $mataKuliahOptions, $kelasOptions, $dosenOptions, $ruanganOptions, $jadwal (opsional). --}}
@php
    $j = $jadwal ?? null;
    $jamInput = fn ($field) => substr((string) old($field, $j?->{$field} ?? ''), 0, 5);
    $hariLabel = ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu'];
@endphp

<div class="row g-3">
    <div class="col-12 col-md-6">
        <label class="form-label" for="f_matakuliah">Mata Kuliah <span class="text-danger">*</span></label>
        <select id="f_matakuliah" name="id_mata_kuliah" class="form-select @error('id_mata_kuliah') is-invalid @enderror" required>
            <option value="">— Pilih Mata Kuliah —</option>
            @foreach($mataKuliahOptions as $id => $teks)
                <option value="{{ $id }}" @selected((string) old('id_mata_kuliah', $j?->id_mata_kuliah) === (string) $id)>{{ $teks }}</option>
            @endforeach
        </select>
        @error('id_mata_kuliah')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        @if(empty($mataKuliahOptions))
            <div class="form-text">Belum ada data mata kuliah. Tambahkan terlebih dahulu pada menu <a href="{{ route('data-akademik.index', ['tab' => 'mata-kuliah']) }}">Data Akademik</a>.</div>
        @endif
    </div>

    <div class="col-12 col-md-6">
        <label class="form-label" for="f_kelas">Kelas <span class="text-danger">*</span></label>
        <select id="f_kelas" name="id_kelas" class="form-select @error('id_kelas') is-invalid @enderror" required>
            <option value="">— Pilih Kelas —</option>
            @foreach($kelasOptions as $id => $teks)
                <option value="{{ $id }}" @selected((string) old('id_kelas', $j?->id_kelas) === (string) $id)>{{ $teks }}</option>
            @endforeach
        </select>
        @error('id_kelas')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label class="form-label" for="f_dosen">Dosen <span class="text-danger">*</span></label>
        <select id="f_dosen" name="nuptk_nidn" class="form-select @error('nuptk_nidn') is-invalid @enderror" required>
            <option value="">— Pilih Dosen —</option>
            @foreach($dosenOptions as $id => $teks)
                <option value="{{ $id }}" @selected((string) old('nuptk_nidn', $j?->nuptk_nidn) === (string) $id)>{{ $teks }}</option>
            @endforeach
        </select>
        @error('nuptk_nidn')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        @if(empty($dosenOptions))
            <div class="form-text">Belum ada data dosen. Tambahkan terlebih dahulu pada menu <a href="{{ route('data-akademik.index', ['tab' => 'dosen']) }}">Data Akademik</a>.</div>
        @endif
    </div>

    <div class="col-12 col-md-6">
        <label class="form-label" for="f_ruangan">Ruangan <span class="text-danger">*</span></label>
        <select id="f_ruangan" name="id_ruangan" class="form-select @error('id_ruangan') is-invalid @enderror" required>
            <option value="">— Pilih Ruangan —</option>
            @foreach($ruanganOptions as $id => $teks)
                <option value="{{ $id }}" @selected((string) old('id_ruangan', $j?->id_ruangan) === (string) $id)>{{ $teks }}</option>
            @endforeach
        </select>
        @error('id_ruangan')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        @if(empty($ruanganOptions))
            <div class="form-text">Belum ada data ruangan. Hubungi Laboran/Admin untuk menambahkan ruangan.</div>
        @endif
    </div>

    <div class="col-12 col-md-4">
        <label class="form-label" for="f_hari">Hari <span class="text-danger">*</span></label>
        <select id="f_hari" name="hari" class="form-select @error('hari') is-invalid @enderror" required>
            <option value="">— Pilih Hari —</option>
            @foreach($hariLabel as $nilai => $teks)
                <option value="{{ $nilai }}" @selected(old('hari', $j?->hari) === $nilai)>{{ $teks }}</option>
            @endforeach
        </select>
        @error('hari')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    <div class="col-6 col-md-4">
        <label class="form-label" for="f_jam_mulai">Jam Mulai <span class="text-danger">*</span></label>
        <input type="time" id="f_jam_mulai" name="jam_mulai" value="{{ $jamInput('jam_mulai') }}"
               class="form-control @error('jam_mulai') is-invalid @enderror" required>
        @error('jam_mulai')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    <div class="col-6 col-md-4">
        <label class="form-label" for="f_jam_selesai">Jam Selesai <span class="text-danger">*</span></label>
        <input type="time" id="f_jam_selesai" name="jam_selesai" value="{{ $jamInput('jam_selesai') }}"
               class="form-control @error('jam_selesai') is-invalid @enderror" required>
        @error('jam_selesai')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-4">
        <label class="form-label" for="f_tahun">Tahun Akademik <span class="text-danger">*</span></label>
        <input type="text" id="f_tahun" name="tahun_akademik" maxlength="9" placeholder="2026/2027"
               value="{{ old('tahun_akademik', $j?->tahun_akademik) }}"
               class="form-control @error('tahun_akademik') is-invalid @enderror" required>
        @error('tahun_akademik')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    <div class="col-6 col-md-4">
        <label class="form-label" for="f_semester">Semester <span class="text-danger">*</span></label>
        <select id="f_semester" name="semester" class="form-select @error('semester') is-invalid @enderror" required>
            <option value="ganjil" @selected(old('semester', $j?->semester) === 'ganjil')>Ganjil</option>
            <option value="genap" @selected(old('semester', $j?->semester) === 'genap')>Genap</option>
        </select>
        @error('semester')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    <div class="col-6 col-md-4">
        <label class="form-label" for="f_status">Status <span class="text-danger">*</span></label>
        <select id="f_status" name="status" class="form-select @error('status') is-invalid @enderror" required>
            <option value="aktif" @selected(old('status', $j?->status ?? 'aktif') === 'aktif')>Aktif</option>
            <option value="dibatalkan" @selected(old('status', $j?->status) === 'dibatalkan')>Dibatalkan</option>
        </select>
        @error('status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
</div>
