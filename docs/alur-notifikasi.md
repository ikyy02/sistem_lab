AKUN
Laboran buat/aktifkan akun ─► AkunService::buat() commit
   └─► queue: AkunTerverifikasiNotification ─► email ke akun (…@mhs.politala.ac.id)
        (isi: nama, role, link login, TANPA password)

PEMINJAMAN
Peminjam ajukan ─► PeminjamanService::ajukan()
   ├─► email+in-app ke peminjam  (konfirmasi pengajuan diterima)
   └─► email+in-app ke penyetuju (dosen PJ bila "atas_dosen", selain itu semua Laboran)

Dosen/Laboran setujui ─► setujui()  ─► notif "Disetujui" ke peminjam (+ jadwal ambil/kembali)
Dosen/Laboran tolak   ─► tolak()    ─► notif "Ditolak + alasan" ke peminjam
Peminjam batalkan     ─► batalkan() ─► notif ke Laboran/dosen PJ

PENGEMBALIAN
Laboran catat ─► kembalikan() ─► notif ke peminjam (ringkasan item, kondisi baik/rusak,
                                   sebagian atau lengkap)
Status jadi selesai ─► notif "Selesai"

PENGINGAT (scheduler, opsional)
Setiap hari 07:00: H-1 rencana kembali ─► pengingat; lewat jatuh tempo ─► notif terlambat