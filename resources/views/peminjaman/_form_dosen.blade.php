<section class="card-modern p-3 p-md-4 pj-card">
    <h3 class="pj-judul">Jadwal Peminjaman</h3>
    <p class="pj-ket" id="hint_waktu">
        Jenis peminjaman: <strong>Pembelajaran</strong> (ditetapkan otomatis). Jadwal berlaku untuk semua yang dipinjam:
        hari operasional ({{ implode(', ', $hariKerja) }}), pukul {{ $jamBuka }} – {{ $jamTutup }}, selesai pada tanggal yang sama.
    </p>
    <div class="row g-3">
        <div class="col-12 col-md-6">
            <label class="form-label" for="tanggal_peminjaman">Waktu Mulai</label>
            <input type="datetime-local" id="tanggal_peminjaman" name="tanggal_peminjaman" value="{{ $nilaiPinjam }}" aria-describedby="hint_waktu"
                   @if($awalIzin) min="{{ $awalIzin->format('Y-m-d\TH:i') }}" @endif
                   class="form-control @error('tanggal_peminjaman') is-invalid @enderror" required>
            @error('tanggal_peminjaman')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label" for="tanggal_rencana_kembali">Waktu Selesai</label>
            <input type="datetime-local" id="tanggal_rencana_kembali" name="tanggal_rencana_kembali" value="{{ $nilaiKembali }}" aria-describedby="hint_waktu"
                   class="form-control @error('tanggal_rencana_kembali') is-invalid @enderror" required>
            @error('tanggal_rencana_kembali')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</section>
