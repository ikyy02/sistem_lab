<section class="card-modern p-3 p-md-4 pj-card mt-3">
    <h3 class="pj-judul">Yang Dipinjam</h3>
    <p class="pj-ket">Pilih ruangan, alat, atau bahan. Minimal satu item harus dipilih; ketiganya dapat dikombinasikan.</p>
    @error('detail')<div class="alert alert-danger py-2 mb-3" role="alert">{{ $message }}</div>@enderror
    @error('ruangan')<div class="alert alert-danger py-2 mb-3" role="alert">{{ $message }}</div>@enderror
    <div id="pesanItem" class="alert alert-warning py-2 mb-3 d-flex align-items-start gap-2" role="alert">
        <i class="bi bi-info-circle mt-1" aria-hidden="true"></i><span>Pilih minimal satu ruangan, alat, atau bahan yang ingin dipinjam sebelum mengajukan peminjaman.</span>
    </div>
    <div class="pj-kategori">
        <div class="pj-kategori-head">
            <h4>Ruangan (Maksimal 1)</h4>
            <button type="button" id="btnTambahRuangan" class="btn btn-outline-silab btn-sm d-inline-flex align-items-center gap-2" onclick="tambahRuangan()"><i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah Ruangan</button>
        </div>
        <div class="table-responsive pj-tabel">
            <table class="table table-silab mb-0">
<caption class="visually-hidden">{{ trim($__env->yieldContent('page-title')) }}</caption>
                <thead><tr><th scope="col" class="ps-3">Ruangan</th><th scope="col" class="text-end pe-3 pj-aksi">Aksi</th></tr></thead>
                <tbody id="ruanganRows"></tbody>
            </table>
            <div class="pj-kosong" data-kosong-untuk="ruanganRows">Belum ada ruangan dipilih.</div>
        </div>
    </div>
    <div class="pj-kategori">
        <div class="pj-kategori-head">
            <h4>Alat</h4>
            <button type="button" class="btn btn-outline-silab btn-sm d-inline-flex align-items-center gap-2" onclick="tambahBarang('alat')"><i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah Alat</button>
        </div>
        <div class="table-responsive pj-tabel">
            <table class="table table-silab mb-0">
<caption class="visually-hidden">{{ trim($__env->yieldContent('page-title')) }}</caption>
                <thead><tr><th scope="col" class="ps-3">Alat</th><th scope="col" class="pj-jumlah">Jumlah</th><th scope="col" class="text-end pe-3 pj-aksi">Aksi</th></tr></thead>
                <tbody id="alatRows"></tbody>
            </table>
            <div class="pj-kosong" data-kosong-untuk="alatRows">Belum ada alat dipilih.</div>
        </div>
    </div>
    <div class="pj-kategori">
        <div class="pj-kategori-head">
            <h4>Bahan</h4>
            <button type="button" class="btn btn-outline-silab btn-sm d-inline-flex align-items-center gap-2" onclick="tambahBarang('bahan')"><i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah Bahan</button>
        </div>
        <div class="table-responsive pj-tabel">
            <table class="table table-silab mb-0">
<caption class="visually-hidden">{{ trim($__env->yieldContent('page-title')) }}</caption>
                <thead><tr><th scope="col" class="ps-3">Bahan</th><th scope="col" class="pj-jumlah">Jumlah</th><th scope="col" class="text-end pe-3 pj-aksi">Aksi</th></tr></thead>
                <tbody id="bahanRows"></tbody>
            </table>
            <div class="pj-kosong" data-kosong-untuk="bahanRows">Belum ada bahan dipilih.</div>
        </div>
    </div>
</section>
