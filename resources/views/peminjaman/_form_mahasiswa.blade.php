@php $jenis = old('jenis_peminjaman', 'pribadi'); @endphp
<section class="card-modern p-3 p-md-4 pj-card">
    <h3 class="pj-judul">Data dan Jadwal Peminjaman</h3>
    <p class="pj-ket">Jadwal berlaku untuk semua ruangan, alat, dan bahan yang dipilih.</p>
    <div class="row g-3">
        <div class="col-12 col-md-6">
            <label class="form-label" for="jenis_peminjaman">Jenis Peminjaman</label>
            <select id="jenis_peminjaman" name="jenis_peminjaman" class="form-select @error('jenis_peminjaman') is-invalid @enderror" onchange="toggleDosen(this.value)">
                <option value="pribadi" @selected($jenis === 'pribadi')>{{ \App\Models\Peminjaman::LABEL_JENIS['pribadi'] }}</option>
                <option value="atas_dosen" @selected($jenis === 'atas_dosen')>{{ \App\Models\Peminjaman::LABEL_JENIS['atas_dosen'] }}</option>
            </select>
            @error('jenis_peminjaman')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12 col-md-6" id="boxDosen" style="display:none;">
            <label class="form-label" for="email_dosen">Dosen Penanggung Jawab</label>
            <select id="email_dosen" name="email_dosen" class="form-select @error('email_dosen') is-invalid @enderror">
                <option value="">Pilih dosen...</option>
                @foreach($opsiDosen as $d)
                    <option value="{{ $d->email }}" @selected(old('email_dosen') === $d->email)>{{ $d->nama }}</option>
                @endforeach
            </select>
            @error('email_dosen')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label" for="tanggal_peminjaman">Waktu Mulai</label>
            <input type="datetime-local" id="tanggal_peminjaman" name="tanggal_peminjaman" value="{{ $nilaiPinjam }}"
                   @if($roleDosen && $awalIzin) min="{{ $awalIzin->format('Y-m-d\TH:i') }}" @endif
                   class="form-control @error('tanggal_peminjaman') is-invalid @enderror" required>
            @error('tanggal_peminjaman')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label" for="tanggal_rencana_kembali">Waktu Selesai</label>
            <input type="datetime-local" id="tanggal_rencana_kembali" name="tanggal_rencana_kembali" value="{{ $nilaiKembali }}"
                   class="form-control @error('tanggal_rencana_kembali') is-invalid @enderror" required>
            @error('tanggal_rencana_kembali')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <label class="form-label" for="keterangan">Keterangan</label>
            <textarea id="keterangan" name="keterangan" rows="3" maxlength="500" required
                      class="form-control @error('keterangan') is-invalid @enderror"
                      placeholder="Contoh: Praktikum jaringan komputer kelas A">{{ old('keterangan') }}</textarea>
            @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</section>
