-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: sistem_lab
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `akuns`
--

DROP TABLE IF EXISTS `akuns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `akuns` (
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('mahasiswa','dosen','staff_prodi','laboran') NOT NULL,
  `wajib_ganti_password` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `akuns`
--

LOCK TABLES `akuns` WRITE;
/*!40000 ALTER TABLE `akuns` DISABLE KEYS */;
INSERT INTO `akuns` VALUES ('admin@silab.test','$2y$12$vJi.pJFUjRyCrqYsH4cNEOsXwjvNiZh/1eOsPrU117J.65VwRneDS','laboran',0),('ahmad.fauzi@politala.ac.id','Masuk12345@','dosen',0),('dosen1@silab.test','dosen123','dosen',0),('jessica@politala.ac.id','Masuk12345@','staff_prodi',0),('muhammad.ghalih@politala.ac.id','dosen123','dosen',0),('rina.marlina@politala.ac.id','dosen123','dosen',0),('rizkyragilmaulana25@mhs.politala.ac.id','$2y$12$uAJiuHGZ040Ryj3di93XhOQ/wH8l5OhVeLZ8PlC9ILupKM2Z8M8RS','mahasiswa',0);
/*!40000 ALTER TABLE `akuns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alat_bahans`
--

DROP TABLE IF EXISTS `alat_bahans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alat_bahans` (
  `id_katalog` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(50) NOT NULL,
  `jenis` enum('alat','bahan') NOT NULL,
  `id_satuan` bigint(20) unsigned NOT NULL,
  `stok` int(10) unsigned NOT NULL,
  `harga` decimal(15,2) NOT NULL,
  `id_ruangan` bigint(20) unsigned NOT NULL,
  `gambar` varchar(255) NOT NULL,
  `keterangan` text DEFAULT NULL,
  PRIMARY KEY (`id_katalog`),
  UNIQUE KEY `uq_alat_bahans_nama` (`nama`),
  KEY `ix_alat_bahans_jenis` (`jenis`),
  KEY `ix_alat_bahans_satuan` (`id_satuan`),
  KEY `ix_alat_bahans_ruangan` (`id_ruangan`),
  CONSTRAINT `fk_alat_bahans_ruangan` FOREIGN KEY (`id_ruangan`) REFERENCES `ruangans` (`id_ruangan`) ON UPDATE CASCADE,
  CONSTRAINT `fk_alat_bahans_satuan` FOREIGN KEY (`id_satuan`) REFERENCES `satuans` (`id_satuan`) ON UPDATE CASCADE,
  CONSTRAINT `ck_alat_bahans_harga` CHECK (`harga` > 0)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alat_bahans`
--

LOCK TABLES `alat_bahans` WRITE;
/*!40000 ALTER TABLE `alat_bahans` DISABLE KEYS */;
INSERT INTO `alat_bahans` VALUES (2,'Switch Cisco Catalyst 24 Port','alat',1,4,3200000.00,3,'switch-cisco-catalyst.jpg','Switch manageable untuk praktikum routing & switching.'),(3,'Router Mikrotik RB750Gr3','alat',1,8,850000.00,3,'router-mikrotik-rb750.jpg','Router board 5-port Gigabit Ethernet untuk praktikum jaringan komputer.'),(4,'Crimping Tool RJ45 Pro','alat',2,12,125000.00,3,'crimping-tool-rj45.jpg','Tang crimping kabel UTP Cat5e dan Cat6.'),(5,'LAN Cable Tester Digital','alat',1,8,95000.00,3,'lan-cable-tester.jpg','Tester pin kabel jaringan RJ45 dan RJ11.'),(6,'Multimeter Digital Sanwa','alat',1,5,350000.00,3,'multimeter-digital.jpg','Pengukur tegangan, arus, dan hambatan digital.'),(7,'Proyektor Epson EB-X500','alat',1,3,5400000.00,5,'proyektor-epson.jpg','Proyektor HDMI/VGA untuk presentasi praktikum multimedia.'),(8,'Drawing Tablet Huion Inspiroy','alat',1,10,650000.00,5,'drawing-tablet.jpg','Pen tablet grafis untuk desain UI/UX dan multimedia interaktif.'),(9,'Barcode Scanner USB','alat',1,5,280000.00,7,'barcode-scanner-usb.jpg','Pemindai barcode 1D/2D untuk praktikum sistem informasi & kasir.'),(10,'Kabel UTP Cat6 Belden','bahan',7,300,8500.00,3,'kabel-utp-cat6.jpg','Kabel jaringan Cat6 unshielded twisted pair.'),(11,'Konektor RJ45 Cat6','bahan',3,200,2500.00,3,'konektor-rj45.jpg','Modular plug konektor RJ45 Cat6 berlapis emas.'),(12,'Larutan Pembersih Kontak (Contact Cleaner)','bahan',5,15,45000.00,3,'contact-cleaner.jpg','Cairan semprot pembersih komponen elektronik dan soket RAM/Motherboard.'),(13,'Thermal Paste Pasta Pendingin CPU','bahan',3,20,35000.00,3,'thermal-paste.jpg','Pasta pendingin konduktivitas tinggi untuk heatsink prosesor.');
/*!40000 ALTER TABLE `alat_bahans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('siarka-cache-5c785c036466adea360111aa28563bfd556b5fba','i:1;',1795446529),('siarka-cache-5c785c036466adea360111aa28563bfd556b5fba:timer','i:1795446529;',1795446529),('siarka-cache-pengajuan:rizkyragilmaulana25@mhs.politala.ac.id:a831b00c-c177-41ec-9f91-ca2a2175a306','i:1;',1795447069),('siarka-cache-wa:cooldown:6285751401610','b:1;',1795449040);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dosens`
--

DROP TABLE IF EXISTS `dosens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dosens` (
  `nuptk_nidn` varchar(30) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `id_prodi` bigint(20) unsigned NOT NULL,
  `email` varchar(50) NOT NULL,
  `no_whatsapp` varchar(20) DEFAULT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  PRIMARY KEY (`nuptk_nidn`),
  UNIQUE KEY `uq_dosens_email` (`email`),
  KEY `ix_dosens_prodi` (`id_prodi`),
  CONSTRAINT `fk_dosens_akun` FOREIGN KEY (`email`) REFERENCES `akuns` (`email`) ON UPDATE CASCADE,
  CONSTRAINT `fk_dosens_prodi` FOREIGN KEY (`id_prodi`) REFERENCES `prodis` (`id_prodi`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dosens`
--

LOCK TABLES `dosens` WRITE;
/*!40000 ALTER TABLE `dosens` DISABLE KEYS */;
INSERT INTO `dosens` VALUES ('0012058501','Dr. Surya Wijaya, M.Kom.',2,'dosen1@silab.test','081234567890','aktif'),('0015088201','Dr. Ahmad Fauzi, M.T.',1,'ahmad.fauzi@politala.ac.id','082155667788','aktif'),('0018099003','Muhammad Ghalih, M.Kom.',4,'muhammad.ghalih@politala.ac.id','081399887766','aktif'),('0021048802','Rina Marlina, M.Cs.',1,'rina.marlina@politala.ac.id','085244332211','aktif');
/*!40000 ALTER TABLE `dosens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `faqs`
--

DROP TABLE IF EXISTS `faqs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kategori` varchar(40) NOT NULL DEFAULT 'Umum',
  `pertanyaan` varchar(255) NOT NULL,
  `jawaban` text NOT NULL,
  `urutan` smallint(5) unsigned NOT NULL DEFAULT 0,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `faqs_aktif_urutan_index` (`aktif`,`urutan`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faqs`
--

LOCK TABLES `faqs` WRITE;
/*!40000 ALTER TABLE `faqs` DISABLE KEYS */;
INSERT INTO `faqs` VALUES (1,'Akun','Bagaimana cara mahasiswa memperoleh akun SIARKA?','Akun mahasiswa dibuatkan secara terpusat oleh staf prodi atau laboran berdasarkan data registrasi akademik awal semester. Gunakan email institusi mahasiswa Anda untuk masuk ke SIARKA.',1,1,'2026-11-11 11:06:19','2026-11-11 11:06:19'),(2,'Peminjaman','Berapa hari batas waktu pengajuan peminjaman laboratorium?','Permohonan peminjaman mandiri dianjurkan diajukan paling lambat 2 (dua) hari kerja sebelum tanggal pelaksanaan kegiatan agar laboran dan dosen penanggung jawab dapat memverifikasi ketersediaan ruang.',2,1,'2026-11-11 11:06:19','2026-11-11 11:06:19'),(3,'Peminjaman','Mengapa tombol Ajukan Peminjaman tidak bisa dipakai?','Satu akun hanya boleh memiliki satu peminjaman aktif per hari. Ajukan peminjaman baru setelah peminjaman sebelumnya berstatus Selesai atau Dibatalkan. Periksa menu Peminjaman Saya untuk melihat pengajuan yang masih menunggu.',3,1,'2026-11-11 11:06:19','2026-11-11 11:06:19'),(4,'Peminjaman','Apakah mahasiswa boleh menggunakan laboratorium di akhir pekan?','Peminjaman di akhir pekan hanya diperkenankan untuk kegiatan resmi institusi, perlombaan akademik berskala nasional, atau penelitian tugas akhir khusus dengan surat rekomendasi resmi dosen pembimbing dan persetujuan Ketua Jurusan.',4,1,'2026-11-11 11:06:19','2026-11-11 11:06:19'),(5,'Peminjaman','Apa perbedaan Peminjaman Saya dan Riwayat Peminjaman?','Peminjaman Saya menampilkan pengajuan yang belum diputus (status Menunggu Persetujuan). Riwayat Peminjaman menampilkan pengajuan yang sudah diputus: Disetujui, Selesai, Ditolak, atau Dibatalkan.',5,1,'2026-11-11 11:06:19','2026-11-11 11:06:19'),(6,'Fasilitas','Apa yang harus dilakukan jika terjadi kerusakan perangkat saat praktikum?','Segera laporkan kepada laboran atau asisten piket di meja layanan. Jangan mencoba memperbaiki perangkat keras kelistrikan sendiri tanpa pendampingan teknisi resmi laboratorium. Kejadian dicatat pada incident log.',6,1,'2026-11-11 11:06:19','2026-11-11 11:06:19'),(7,'Akun','Bagaimana cara mengganti password akun SIARKA?','Buka menu Profil lalu pilih bagian ganti password. Jika akun baru dibuatkan dan ditandai wajib ganti password, SIARKA akan meminta penggantian password sebelum menu lain dapat diakses.',7,1,'2026-11-11 11:06:19','2026-11-11 11:06:19'),(8,'Fasilitas','Ke mana harus menghubungi jika ada kendala teknis di laboratorium?','Hubungi laboran melalui meja layanan pada jam kerja (Senin–Kamis 08.00–16.00, Jumat 08.00–11.30 dan 13.30–16.30 WITA) atau gunakan kanal pengaduan yang tersedia di laboratorium.',8,1,'2026-11-11 11:06:19','2026-11-11 11:06:19');
/*!40000 ALTER TABLE `faqs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jadwals`
--

DROP TABLE IF EXISTS `jadwals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jadwals` (
  `id_jadwal` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_kelas` bigint(20) unsigned NOT NULL,
  `nuptk_nidn` varchar(30) NOT NULL,
  `id_ruangan` bigint(20) unsigned NOT NULL,
  `hari` enum('senin','selasa','rabu','kamis','jumat','sabtu') NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `status` enum('aktif','dibatalkan') NOT NULL,
  `tahun_akademik` varchar(9) NOT NULL,
  `semester` enum('ganjil','genap') NOT NULL,
  `id_mata_kuliah` bigint(20) unsigned DEFAULT NULL,
  `id_periode` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_jadwal`),
  KEY `ix_jadwals_bentrok` (`id_ruangan`,`hari`,`tahun_akademik`,`semester`),
  KEY `ix_jadwals_kelas` (`id_kelas`),
  KEY `ix_jadwals_dosen` (`nuptk_nidn`),
  KEY `ix_jadwals_matakuliah` (`id_mata_kuliah`),
  KEY `ix_jadwals_periode` (`id_periode`),
  CONSTRAINT `fk_jadwals_dosen` FOREIGN KEY (`nuptk_nidn`) REFERENCES `dosens` (`nuptk_nidn`) ON UPDATE CASCADE,
  CONSTRAINT `fk_jadwals_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON UPDATE CASCADE,
  CONSTRAINT `fk_jadwals_matakuliah` FOREIGN KEY (`id_mata_kuliah`) REFERENCES `mata_kuliahs` (`id_mata_kuliah`) ON UPDATE CASCADE,
  CONSTRAINT `fk_jadwals_periode` FOREIGN KEY (`id_periode`) REFERENCES `periode_jadwals` (`id_periode`) ON UPDATE CASCADE,
  CONSTRAINT `fk_jadwals_ruangan` FOREIGN KEY (`id_ruangan`) REFERENCES `ruangans` (`id_ruangan`) ON UPDATE CASCADE,
  CONSTRAINT `ck_jadwals_jam` CHECK (`jam_mulai` < `jam_selesai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jadwals`
--

LOCK TABLES `jadwals` WRITE;
/*!40000 ALTER TABLE `jadwals` DISABLE KEYS */;
/*!40000 ALTER TABLE `jadwals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kelas`
--

DROP TABLE IF EXISTS `kelas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kelas` (
  `id_kelas` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_kelas` varchar(100) NOT NULL,
  `id_prodi` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id_kelas`),
  UNIQUE KEY `uq_kelas_prodi_nama` (`id_prodi`,`nama_kelas`),
  CONSTRAINT `fk_kelas_prodi` FOREIGN KEY (`id_prodi`) REFERENCES `prodis` (`id_prodi`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kelas`
--

LOCK TABLES `kelas` WRITE;
/*!40000 ALTER TABLE `kelas` DISABLE KEYS */;
INSERT INTO `kelas` VALUES (1,'1A',1),(2,'1B',1),(3,'1C',1),(4,'1D',1),(5,'1E',1),(6,'2A',1),(7,'2B',1),(8,'2C',1),(9,'2D',1),(10,'2E',1),(11,'3A',1),(12,'3B',1),(13,'3C',1),(14,'3D',1),(15,'3E',1),(16,'4A',1),(17,'4B',1),(18,'4C',1),(19,'4D',1),(20,'4E',1),(21,'1A',2),(22,'1B',2),(23,'1C',2),(24,'1D',2),(25,'1E',2),(26,'2A',2),(27,'2B',2),(28,'2C',2),(29,'2D',2),(30,'2E',2),(31,'3A',2),(32,'3B',2),(33,'3C',2),(34,'3D',2),(35,'3E',2),(36,'4A',2),(37,'4B',2),(38,'4C',2),(39,'4D',2),(40,'4E',2),(41,'1A',3),(42,'1B',3),(43,'1C',3),(44,'1D',3),(45,'1E',3),(46,'2A',3),(47,'2B',3),(48,'2C',3),(49,'2D',3),(50,'2E',3),(51,'3A',3),(52,'3B',3),(53,'3C',3),(54,'3D',3),(55,'3E',3),(56,'4A',3),(57,'4B',3),(58,'4C',3),(59,'4D',3),(60,'4E',3),(61,'1A',4),(62,'1B',4),(63,'1C',4),(64,'1D',4),(65,'1E',4),(66,'2A',4),(67,'2B',4),(68,'2C',4),(69,'2D',4),(70,'2E',4),(71,'3A',4),(72,'3B',4),(73,'3C',4),(74,'3D',4),(75,'3E',4),(76,'4A',4),(77,'4B',4),(78,'4C',4),(79,'4D',4),(80,'4E',4);
/*!40000 ALTER TABLE `kelas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `laborans`
--

DROP TABLE IF EXISTS `laborans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `laborans` (
  `id_pegawai` varchar(30) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(50) NOT NULL,
  `no_whatsapp` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_pegawai`),
  UNIQUE KEY `uq_laborans_email` (`email`),
  CONSTRAINT `fk_laborans_akun` FOREIGN KEY (`email`) REFERENCES `akuns` (`email`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `laborans`
--

LOCK TABLES `laborans` WRITE;
/*!40000 ALTER TABLE `laborans` DISABLE KEYS */;
INSERT INTO `laborans` VALUES ('LAB0001','Administrator','admin@silab.test','081200000000');
/*!40000 ALTER TABLE `laborans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mahasiswas`
--

DROP TABLE IF EXISTS `mahasiswas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mahasiswas` (
  `nim` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `id_prodi` bigint(20) unsigned NOT NULL,
  `email` varchar(50) NOT NULL,
  `no_whatsapp` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`nim`),
  UNIQUE KEY `uq_mahasiswas_email` (`email`),
  KEY `ix_mahasiswas_prodi` (`id_prodi`),
  CONSTRAINT `fk_mahasiswas_akun` FOREIGN KEY (`email`) REFERENCES `akuns` (`email`) ON UPDATE CASCADE,
  CONSTRAINT `fk_mahasiswas_prodi` FOREIGN KEY (`id_prodi`) REFERENCES `prodis` (`id_prodi`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mahasiswas`
--

LOCK TABLES `mahasiswas` WRITE;
/*!40000 ALTER TABLE `mahasiswas` DISABLE KEYS */;
INSERT INTO `mahasiswas` VALUES ('2501301027','rizky ragil maulana',1,'rizkyragilmaulana25@mhs.politala.ac.id','085751401610');
/*!40000 ALTER TABLE `mahasiswas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mata_kuliah_pemetaan_log`
--

DROP TABLE IF EXISTS `mata_kuliah_pemetaan_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mata_kuliah_pemetaan_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_mata_kuliah` bigint(20) unsigned NOT NULL,
  `id_mata_kuliah_baru` bigint(20) unsigned DEFAULT NULL,
  `id_prodi` bigint(20) unsigned DEFAULT NULL,
  `kode_mk` varchar(15) NOT NULL,
  `nama_mk` varchar(100) NOT NULL,
  `status` varchar(20) NOT NULL,
  `jumlah_jadwal` int(10) unsigned NOT NULL DEFAULT 0,
  `keterangan` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mata_kuliah_pemetaan_log`
--

LOCK TABLES `mata_kuliah_pemetaan_log` WRITE;
/*!40000 ALTER TABLE `mata_kuliah_pemetaan_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `mata_kuliah_pemetaan_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mata_kuliahs`
--

DROP TABLE IF EXISTS `mata_kuliahs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mata_kuliahs` (
  `id_mata_kuliah` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_mk` varchar(15) NOT NULL,
  `nama_mk` varchar(100) NOT NULL,
  `sks` tinyint(3) unsigned NOT NULL,
  `semester` enum('ganjil','genap') NOT NULL,
  `id_prodi` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_mata_kuliah`),
  UNIQUE KEY `uq_mata_kuliahs_prodi_kode` (`id_prodi`,`kode_mk`),
  KEY `ix_mata_kuliahs_nama` (`nama_mk`),
  KEY `ix_mata_kuliahs_prodi` (`id_prodi`),
  CONSTRAINT `fk_mata_kuliahs_prodi` FOREIGN KEY (`id_prodi`) REFERENCES `prodis` (`id_prodi`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mata_kuliahs`
--

LOCK TABLES `mata_kuliahs` WRITE;
/*!40000 ALTER TABLE `mata_kuliahs` DISABLE KEYS */;
/*!40000 ALTER TABLE `mata_kuliahs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_09_18_000001_create_alat_bahans_table',1),(5,'2026_09_18_000002_create_mahasiswas_table',1),(6,'2026_09_24_000001_create_dosens_table',1),(7,'2026_09_28_000001_add_auth_and_user_categories',1),(8,'2026_10_05_000001_add_satuans_and_gambar',1),(9,'2026_10_12_000001_add_unit_conversion_to_alat_bahans',1),(10,'2026_10_19_000001_create_prodi_and_kelas_master',1),(11,'2026_10_26_000001_change_user_primary_keys',1),(12,'2026_10_26_000002_create_master_options',1),(13,'2026_10_26_000003_drop_master_options',1),(14,'2026_11_02_000001_revisi_final_struktur_16_tabel',1),(15,'2026_11_09_000001_create_pengaturan_operasional',1),(16,'2026_11_10_000001_add_persetujuan_and_alasan_to_peminjamans',1),(17,'2026_11_10_000001_create_mata_kuliahs_dan_relasi_jadwal',1),(18,'2026_11_11_000001_hapus_tabel_hari_libur',1),(19,'2026_11_12_000001_add_status_to_dosens_table',1),(20,'2026_11_12_000001_tambah_jenis_pembelajaran_pada_peminjamans',1),(21,'2026_11_17_000001_create_kelas_mahasiswas_table',1),(22,'2026_11_24_000001_add_dibatalkan_status_to_peminjamans',1),(23,'2026_11_24_000002_create_periode_jadwals',1),(24,'2026_12_01_000001_tambah_gambar_pada_ruangans',1),(25,'2026_12_01_000002_create_notifications_table',1),(26,'2026_12_02_000001_create_wa_logs_table',1),(27,'2026_12_02_000001_tambah_persetujuan_dosen_pada_peminjamans',1),(28,'2026_12_03_000001_lepas_relasi_kelas_mahasiswas',1),(29,'2026_12_04_000001_tambah_wajib_ganti_password_pada_akuns',1),(30,'2026_12_05_000001_tambah_prodi_pada_mata_kuliahs',1),(31,'2026_12_10_000001_add_no_whatsapp_to_peminjamans_table',1),(32,'2026_12_11_000001_create_sops_table',2),(33,'2026_12_11_000002_create_faqs_table',3);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` varchar(50) NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES ('1697a4ae-d623-436a-8d7b-1aea6f061fe4','App\\Notifications\\PengajuanBaru','akun','dosen1@silab.test','{\"judul\":\"Pengajuan Peminjaman Baru\",\"pesan\":\"Ada pengajuan peminjaman baru yang menunggu persetujuan Anda. Peminjaman #2 oleh rizky ragil maulana.\"}',NULL,'2026-11-23 15:59:14','2026-11-23 15:59:14'),('24e5c88d-b44e-46c5-8127-2423a3a9b957','App\\Notifications\\PengajuanBaru','akun','dosen1@silab.test','{\"judul\":\"Pengajuan Peminjaman Baru\",\"pesan\":\"Ada pengajuan peminjaman baru yang menunggu persetujuan Anda. Peminjaman #3 oleh rizky ragil maulana.\"}',NULL,'2026-11-23 16:00:20','2026-11-23 16:00:20'),('3e790d5c-3a14-4217-b6e7-c12e8a1f05ff','App\\Notifications\\PeminjamanDisetujui','akun','rizkyragilmaulana25@mhs.politala.ac.id','{\"judul\":\"Peminjaman Disetujui\",\"pesan\":\"Pengajuan peminjaman Anda telah disetujui. Peminjaman #1 oleh rizky ragil maulana.\"}',NULL,'2026-11-20 16:54:06','2026-11-20 16:54:06'),('6cf3f0c2-fa32-40b9-8e98-d2d8590904d6','App\\Notifications\\PengajuanBaru','akun','admin@silab.test','{\"judul\":\"Pengajuan Peminjaman Baru\",\"pesan\":\"Ada pengajuan peminjaman baru yang menunggu persetujuan Anda. Peminjaman #4 oleh rizky ragil maulana.\"}',NULL,'2026-11-23 16:07:56','2026-11-23 16:07:56'),('6fc18842-37b5-4ed9-905f-38ffa572a9fb','App\\Notifications\\PengajuanDiterima','akun','rizkyragilmaulana25@mhs.politala.ac.id','{\"judul\":\"Pengajuan Peminjaman Diterima\",\"pesan\":\"Pengajuan peminjaman Anda telah kami terima dan sedang menunggu persetujuan. Peminjaman #2 oleh rizky ragil maulana.\"}',NULL,'2026-11-23 15:59:13','2026-11-23 15:59:13'),('ade8dc42-8eb4-45eb-b7ab-e7f50c23cc88','App\\Notifications\\PeminjamanDisetujui','akun','rizkyragilmaulana25@mhs.politala.ac.id','{\"judul\":\"Peminjaman Disetujui\",\"pesan\":\"Pengajuan peminjaman Anda telah disetujui. Peminjaman #4 oleh rizky ragil maulana.\"}',NULL,'2026-11-23 16:09:18','2026-11-23 16:09:18'),('be96b517-6a33-4c04-a0f6-0c45a174de99','App\\Notifications\\PengajuanDiterima','akun','rizkyragilmaulana25@mhs.politala.ac.id','{\"judul\":\"Pengajuan Peminjaman Diterima\",\"pesan\":\"Pengajuan peminjaman Anda telah kami terima dan sedang menunggu persetujuan. Peminjaman #4 oleh rizky ragil maulana.\"}',NULL,'2026-11-23 16:07:53','2026-11-23 16:07:53'),('ced90d96-580d-4cd6-a22a-1223a6a3c6ba','App\\Notifications\\PengajuanBaru','akun','admin@silab.test','{\"judul\":\"Pengajuan Peminjaman Baru\",\"pesan\":\"Ada pengajuan peminjaman baru yang menunggu persetujuan Anda. Peminjaman #2 oleh rizky ragil maulana.\"}',NULL,'2026-11-23 15:59:16','2026-11-23 15:59:16'),('cfa448e1-3238-4652-8b8d-13fc572e3156','App\\Notifications\\PeminjamanDisetujui','akun','rizkyragilmaulana25@mhs.politala.ac.id','{\"judul\":\"Peminjaman Disetujui\",\"pesan\":\"Pengajuan peminjaman Anda telah disetujui. Peminjaman #1 oleh rizky ragil maulana.\"}',NULL,'2026-11-20 16:51:38','2026-11-20 16:51:38'),('e1cbf53c-398c-46b9-b13d-3216a8b715bb','App\\Notifications\\PeminjamanDisetujui','akun','rizkyragilmaulana25@mhs.politala.ac.id','{\"judul\":\"Peminjaman Disetujui\",\"pesan\":\"Pengajuan peminjaman Anda telah disetujui. Peminjaman #2 oleh rizky ragil maulana.\"}',NULL,'2026-11-23 15:59:18','2026-11-23 15:59:18'),('e8230820-4719-498d-ae02-eff089145aa7','App\\Notifications\\PengajuanDiterima','akun','rizkyragilmaulana25@mhs.politala.ac.id','{\"judul\":\"Pengajuan Peminjaman Diterima\",\"pesan\":\"Pengajuan peminjaman Anda telah kami terima dan sedang menunggu persetujuan. Peminjaman #3 oleh rizky ragil maulana.\"}',NULL,'2026-11-23 16:00:18','2026-11-23 16:00:18');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `peminjaman_details`
--

DROP TABLE IF EXISTS `peminjaman_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `peminjaman_details` (
  `id_detail` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_peminjaman` bigint(20) unsigned NOT NULL,
  `id_katalog` bigint(20) unsigned NOT NULL,
  `jumlah` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_detail`),
  UNIQUE KEY `uq_pdetail_peminjaman_katalog` (`id_peminjaman`,`id_katalog`),
  KEY `ix_pdetail_katalog` (`id_katalog`),
  CONSTRAINT `fk_pdetail_katalog` FOREIGN KEY (`id_katalog`) REFERENCES `alat_bahans` (`id_katalog`) ON UPDATE CASCADE,
  CONSTRAINT `fk_pdetail_peminjaman` FOREIGN KEY (`id_peminjaman`) REFERENCES `peminjamans` (`id_peminjaman`) ON UPDATE CASCADE,
  CONSTRAINT `ck_pdetail_jumlah` CHECK (`jumlah` > 0)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `peminjaman_details`
--

LOCK TABLES `peminjaman_details` WRITE;
/*!40000 ALTER TABLE `peminjaman_details` DISABLE KEYS */;
INSERT INTO `peminjaman_details` VALUES (1,1,4,1),(2,1,11,1),(3,2,2,1),(4,3,2,1),(5,4,6,1),(6,5,2,1);
/*!40000 ALTER TABLE `peminjaman_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `peminjaman_ruangans`
--

DROP TABLE IF EXISTS `peminjaman_ruangans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `peminjaman_ruangans` (
  `id_peminjaman_ruangan` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_peminjaman` bigint(20) unsigned NOT NULL,
  `id_ruangan` bigint(20) unsigned NOT NULL,
  `tanggal_mulai` datetime NOT NULL,
  `tanggal_selesai` datetime NOT NULL,
  `keterangan` text DEFAULT NULL,
  PRIMARY KEY (`id_peminjaman_ruangan`),
  UNIQUE KEY `uq_pruangan_peminjaman_ruangan` (`id_peminjaman`,`id_ruangan`),
  KEY `ix_pruangan_ruangan` (`id_ruangan`),
  CONSTRAINT `fk_pruangan_peminjaman` FOREIGN KEY (`id_peminjaman`) REFERENCES `peminjamans` (`id_peminjaman`) ON UPDATE CASCADE,
  CONSTRAINT `fk_pruangan_ruangan` FOREIGN KEY (`id_ruangan`) REFERENCES `ruangans` (`id_ruangan`) ON UPDATE CASCADE,
  CONSTRAINT `ck_pruangan_waktu` CHECK (`tanggal_mulai` < `tanggal_selesai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `peminjaman_ruangans`
--

LOCK TABLES `peminjaman_ruangans` WRITE;
/*!40000 ALTER TABLE `peminjaman_ruangans` DISABLE KEYS */;
/*!40000 ALTER TABLE `peminjaman_ruangans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `peminjamans`
--

DROP TABLE IF EXISTS `peminjamans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `peminjamans` (
  `id_peminjaman` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email_peminjam` varchar(50) NOT NULL,
  `no_whatsapp` varchar(20) DEFAULT NULL,
  `jenis_peminjaman` enum('pribadi','atas_dosen','pembelajaran') NOT NULL,
  `email_dosen` varchar(50) DEFAULT NULL,
  `tanggal_pengajuan` datetime NOT NULL,
  `tanggal_peminjaman` datetime NOT NULL,
  `tanggal_rencana_kembali` datetime NOT NULL,
  `status` enum('menunggu','disetujui','ditolak','selesai','dibatalkan') NOT NULL,
  `keterangan` text DEFAULT NULL,
  `alasan_ditolak` text DEFAULT NULL,
  `email_pemroses` varchar(50) DEFAULT NULL,
  `tanggal_persetujuan` datetime DEFAULT NULL,
  `tanggal_persetujuan_dosen` datetime DEFAULT NULL,
  PRIMARY KEY (`id_peminjaman`),
  KEY `ix_peminjamans_status` (`status`),
  KEY `ix_peminjamans_tgl_pinjam` (`tanggal_peminjaman`),
  KEY `ix_peminjamans_peminjam` (`email_peminjam`),
  KEY `ix_peminjamans_dosen` (`email_dosen`),
  KEY `ix_peminjamans_pemroses` (`email_pemroses`),
  CONSTRAINT `fk_peminjamans_dosen` FOREIGN KEY (`email_dosen`) REFERENCES `akuns` (`email`) ON UPDATE CASCADE,
  CONSTRAINT `fk_peminjamans_peminjam` FOREIGN KEY (`email_peminjam`) REFERENCES `akuns` (`email`) ON UPDATE CASCADE,
  CONSTRAINT `fk_peminjamans_pemroses` FOREIGN KEY (`email_pemroses`) REFERENCES `akuns` (`email`) ON UPDATE CASCADE,
  CONSTRAINT `ck_peminjamans_tanggal` CHECK (`tanggal_pengajuan` <= `tanggal_peminjaman` and `tanggal_peminjaman` <= `tanggal_rencana_kembali`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `peminjamans`
--

LOCK TABLES `peminjamans` WRITE;
/*!40000 ALTER TABLE `peminjamans` DISABLE KEYS */;
INSERT INTO `peminjamans` VALUES (1,'rizkyragilmaulana25@mhs.politala.ac.id',NULL,'pribadi',NULL,'2026-11-11 14:32:00','2026-11-12 08:00:00','2026-11-12 14:00:00','selesai','praktikum',NULL,'admin@silab.test','2026-11-11 22:38:39',NULL),(2,'rizkyragilmaulana25@mhs.politala.ac.id',NULL,'atas_dosen','dosen1@silab.test','2026-11-23 22:58:00','2026-11-24 09:00:00','2026-11-24 11:00:00','selesai',NULL,NULL,'admin@silab.test','2026-11-23 22:58:38','2026-11-23 22:58:38'),(3,'rizkyragilmaulana25@mhs.politala.ac.id',NULL,'atas_dosen','dosen1@silab.test','2026-11-23 23:00:00','2026-11-25 08:30:00','2026-11-25 11:30:00','selesai',NULL,NULL,NULL,NULL,NULL),(4,'rizkyragilmaulana25@mhs.politala.ac.id',NULL,'pribadi',NULL,'2026-11-23 23:07:00','2026-11-24 08:00:00','2026-11-24 09:00:00','selesai','prktek bro',NULL,'admin@silab.test','2026-11-23 23:09:14',NULL),(5,'rizkyragilmaulana25@mhs.politala.ac.id','085751401610','atas_dosen','dosen1@silab.test','2026-11-20 08:00:00','2026-11-21 09:00:00','2026-11-22 11:00:00','selesai',NULL,NULL,'admin@silab.test','2026-11-20 09:00:00','2026-11-20 08:30:00');
/*!40000 ALTER TABLE `peminjamans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengaturan_operasional`
--

DROP TABLE IF EXISTS `pengaturan_operasional`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pengaturan_operasional` (
  `id` tinyint(3) unsigned NOT NULL,
  `jam_buka` time NOT NULL,
  `jam_tutup` time NOT NULL,
  `hari_operasional` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengaturan_operasional`
--

LOCK TABLES `pengaturan_operasional` WRITE;
/*!40000 ALTER TABLE `pengaturan_operasional` DISABLE KEYS */;
INSERT INTO `pengaturan_operasional` VALUES (1,'08:00:00','16:00:00','senin,selasa,rabu,kamis,jumat');
/*!40000 ALTER TABLE `pengaturan_operasional` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengembalian_details`
--

DROP TABLE IF EXISTS `pengembalian_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pengembalian_details` (
  `id_detail_pengembalian` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_pengembalian` bigint(20) unsigned NOT NULL,
  `id_detail_peminjaman` bigint(20) unsigned NOT NULL,
  `jumlah_dikembalikan` int(10) unsigned NOT NULL,
  `kondisi` enum('baik','rusak') DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  PRIMARY KEY (`id_detail_pengembalian`),
  KEY `ix_pgdetail_pengembalian` (`id_pengembalian`),
  KEY `ix_pgdetail_detail_pinjam` (`id_detail_peminjaman`),
  CONSTRAINT `fk_pgdetail_detail_pinjam` FOREIGN KEY (`id_detail_peminjaman`) REFERENCES `peminjaman_details` (`id_detail`) ON UPDATE CASCADE,
  CONSTRAINT `fk_pgdetail_pengembalian` FOREIGN KEY (`id_pengembalian`) REFERENCES `pengembalians` (`id_pengembalian`) ON UPDATE CASCADE,
  CONSTRAINT `ck_pgdetail_jumlah` CHECK (`jumlah_dikembalikan` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengembalian_details`
--

LOCK TABLES `pengembalian_details` WRITE;
/*!40000 ALTER TABLE `pengembalian_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `pengembalian_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengembalians`
--

DROP TABLE IF EXISTS `pengembalians`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pengembalians` (
  `id_pengembalian` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_peminjaman` bigint(20) unsigned NOT NULL,
  `tanggal_pengembalian` datetime NOT NULL,
  `email_penerima` varchar(50) NOT NULL,
  `keterangan` text DEFAULT NULL,
  PRIMARY KEY (`id_pengembalian`),
  KEY `ix_pengembalians_peminjaman` (`id_peminjaman`),
  KEY `ix_pengembalians_penerima` (`email_penerima`),
  CONSTRAINT `fk_pengembalians_peminjaman` FOREIGN KEY (`id_peminjaman`) REFERENCES `peminjamans` (`id_peminjaman`) ON UPDATE CASCADE,
  CONSTRAINT `fk_pengembalians_penerima` FOREIGN KEY (`email_penerima`) REFERENCES `akuns` (`email`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengembalians`
--

LOCK TABLES `pengembalians` WRITE;
/*!40000 ALTER TABLE `pengembalians` DISABLE KEYS */;
/*!40000 ALTER TABLE `pengembalians` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `periode_jadwals`
--

DROP TABLE IF EXISTS `periode_jadwals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `periode_jadwals` (
  `id_periode` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_berakhir` date NOT NULL,
  `status` enum('berjalan','arsip') NOT NULL DEFAULT 'berjalan',
  PRIMARY KEY (`id_periode`),
  KEY `ix_periode_jadwals_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `periode_jadwals`
--

LOCK TABLES `periode_jadwals` WRITE;
/*!40000 ALTER TABLE `periode_jadwals` DISABLE KEYS */;
/*!40000 ALTER TABLE `periode_jadwals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prodis`
--

DROP TABLE IF EXISTS `prodis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prodis` (
  `id_prodi` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_prodi` varchar(50) NOT NULL,
  PRIMARY KEY (`id_prodi`),
  UNIQUE KEY `uq_prodis_nama_prodi` (`nama_prodi`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prodis`
--

LOCK TABLES `prodis` WRITE;
/*!40000 ALTER TABLE `prodis` DISABLE KEYS */;
INSERT INTO `prodis` VALUES (3,'D3 Akuntansi'),(1,'D3 Teknologi Informasi'),(4,'D4 Bisnis Digital'),(2,'D4 Teknologi Rekayasa Komputer Jaringan');
/*!40000 ALTER TABLE `prodis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ruangans`
--

DROP TABLE IF EXISTS `ruangans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ruangans` (
  `id_ruangan` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_ruangan` varchar(50) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `gambar` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_ruangan`),
  UNIQUE KEY `uq_ruangans_nama_ruangan` (`nama_ruangan`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ruangans`
--

LOCK TABLES `ruangans` WRITE;
/*!40000 ALTER TABLE `ruangans` DISABLE KEYS */;
INSERT INTO `ruangans` VALUES (3,'Lab Linus Torvalds','Laboratorium Komputer & Jaringan (Lantai 2)','lt.jpg'),(4,'Lab Linus Torvalds 2','Laboratorium Praktikum Sistem Operasi & Jaringan (Lantai 2)','lt2.jpg'),(5,'Lab Steve Jobs','Laboratorium Desain, UI/UX & Pemrograman Visual (Lantai 2)','sj.jpg'),(6,'Lab Steve Jobs 2','Laboratorium Multimedia & Pengembangan Mobile (Lantai 2)','sj2.jpg'),(7,'Lab Bill Gates','Laboratorium Aplikasi Bisnis & Komputer Perkantoran (Lantai 1)','bg.jpg'),(8,'Lab Bill Gates 2','Laboratorium Sistem Informasi & Basis Data (Lantai 1)','bg2.jpg'),(9,'Lab Kenneth Thompson','Laboratorium Pemrograman Dasar & Algoritma (Lantai 3)','kt.jpg'),(10,'Lab Kenneth Thompson 2','Laboratorium Rekayasa Perangkat Lunak Lanjut (Lantai 3)','kt2.jpg');
/*!40000 ALTER TABLE `ruangans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `satuans`
--

DROP TABLE IF EXISTS `satuans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `satuans` (
  `id_satuan` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_satuan` varchar(50) NOT NULL,
  PRIMARY KEY (`id_satuan`),
  UNIQUE KEY `uq_satuans_nama_satuan` (`nama_satuan`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `satuans`
--

LOCK TABLES `satuans` WRITE;
/*!40000 ALTER TABLE `satuans` DISABLE KEYS */;
INSERT INTO `satuans` VALUES (5,'Botol'),(2,'Buah'),(10,'Gram'),(9,'Kilogram'),(8,'Liter'),(7,'Meter'),(6,'Pak'),(3,'Pcs'),(4,'Set'),(1,'Unit');
/*!40000 ALTER TABLE `satuans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('0tSJAcrsHSmwUAIHbghNSQ5cs19ZK3jNqPY7zJin',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','eyJfdG9rZW4iOiI4Z2pjZ3d4Yktxd3dvRDZKWDA1S29BZ2UxTmpNYVBqV1ZrMzhPbnFCIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1795498078),('2xehMdOl9yQqlYmTslHU9p03croj4rbRzzlUI2JN',NULL,'127.0.0.1','Python-urllib/3.12','eyJfdG9rZW4iOiJBWDNMR2RzRmIyTE5tbjg3SG5Qc0MyREpyUUlRNWJpNm5UNDBvSHIzIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9rYXRhbG9nP2plbmlzPXJ1YW5nYW4iLCJyb3V0ZSI6ImthdGFsb2cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1795498899),('3orpsEZe9mLLGamKBlUSxmodmypEZAKQuf2njKAQ',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJLWE9NRW1yVTB0RmdaWHlaZUZpN2tPUm1WOVFtNFRLT0tlWHpmV3daIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2xvZ2luIiwicm91dGUiOiJsb2dpbiJ9fQ==',1795446495),('4fZsivmB3eq8kvHhRaPdIl3aYSQTi8vhZ3dv26Pz',NULL,'127.0.0.1','Python-urllib/3.12','eyJfdG9rZW4iOiJIakJPc3d6WW9SR3UwWUxwYVJxRWI0ZmRCUEVMVlhOcXh2R3l2cFVUIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1795498882),('AG9mMautj5rGuhkrheeTVyeA0xWwV9gmm3XjOTl6',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJKTVJOcmZjeXZUVUc1VXE5R1Z5c0dQeEdaSWJ0a3R3bXZ0amxJaHFTIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL3BlbmdhanVhbi1wZW1pbmphbWFuIiwicm91dGUiOiJwZW5nYWp1YW4uaW5kZXgifSwic2lsYWJfdXNlciI6eyJyb2xlIjoibGFib3JhbiIsImtleSI6IkxBQjAwMDEiLCJuYW1hIjoiQWRtaW5pc3RyYXRvciIsImVtYWlsIjoiYWRtaW5Ac2lsYWIudGVzdCJ9fQ==',1795446555),('dAnaAsNnGduAO0ChijdHMUAVsjWPhJ40V5PEcn3y',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJ0NWJPdU5JejdRbWwxYVlVS2RyUjRFSUExNHF4TU1pY1BvdGFmYUpJIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2xvZ2luIiwicm91dGUiOiJsb2dpbiJ9fQ==',1794412693),('DyOW0nE4DL8x6tzHjB4qpZ7ehbCn8upO0EwleEK8',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','eyJfdG9rZW4iOiJ1SU9tOFEyTzlKRUNmYkJmVlROYUdUWEljSlQwZ2Zscm50Y1FsQ1RCIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1795498822),('euUuGjMAll519R8nP2oPH0B081Zzf7oqaPEf0wjP',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJjcUZNVU11V2pveFh1WFdoVTZYQ1pyZW4ySEJFTjhYbEVveGVHTUFqIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2xvZ2luIiwicm91dGUiOiJsb2dpbiJ9fQ==',1794412664),('fFYMLymZlPlnOHs4nutEzjDiwc2K5Me1gZrukAh7',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJxUEJHeW5wWVdvQXBHVUgzQ1Q4NHlham55QmZsVXU2Wmpud3lEajhrIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9rYXRhbG9nP2plbmlzPXJ1YW5nYW4mcGVyX3BhZ2U9MTAmc2VhcmNoPSZzb3J0PW5hbWEiLCJyb3V0ZSI6ImthdGFsb2cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJzaWxhYl91c2VyIjp7InJvbGUiOiJtYWhhc2lzd2EiLCJrZXkiOiIyNTAxMzAxMDI3IiwibmFtYSI6InJpemt5IHJhZ2lsIG1hdWxhbmEiLCJlbWFpbCI6InJpemt5cmFnaWxtYXVsYW5hMjVAbWhzLnBvbGl0YWxhLmFjLmlkIn19',1795499229),('fYaZiReETadXfSClcg9OS7nJUUJC5s7QLTrxMS5M',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJiNzZGcURJSWYzUjl5MEhreWdYaFZvRzl5YzBYU1doQU5hbkFiRWZkIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2xvZ2luIiwicm91dGUiOiJsb2dpbiJ9fQ==',1794409378),('GdrdStSIMMKfvGbtlaUz7XSCPQEpxpAdl0Ckkct2',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJ1d0hMNWp0UkZlWFFwRnpkVGowcEJnTzdORUVmTXVIZ3FpVVMwRzJ4IiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL3BlbmdhZGFhbi10cGtcL2V4cG9ydC1leGNlbCIsInJvdXRlIjoicGVuZ2FkYWFuLXRway5leHBvcnQtZXhjZWwifSwic2lsYWJfdXNlciI6eyJyb2xlIjoibGFib3JhbiIsImtleSI6IkxBQjAwMDEiLCJuYW1hIjoiQWRtaW5pc3RyYXRvciIsImVtYWlsIjoiYWRtaW5Ac2lsYWIudGVzdCJ9fQ==',1794416151),('gTtAtGX5JuLYPL6jTMU5KNwLqtsWv71kAoFtMKMJ',NULL,'127.0.0.1','Python-urllib/3.12','eyJfdG9rZW4iOiJmb0pDcE13RlFkY2VTTnE5WUJGazhlTXNMTFlWemViTm1ERUJqdFJQIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1795498861),('gYP2WRJFjykyfm3vZeOx803ogoAPrSLbqeddRCo8',NULL,'127.0.0.1','Python-urllib/3.12','eyJfdG9rZW4iOiJBNlJ5V3dXMXNiNzEzNWpvSEk0d0F0RlJoM3paUHF5WlNrU2o4Z09DIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9rYXRhbG9nP2plbmlzPXJ1YW5nYW4iLCJyb3V0ZSI6ImthdGFsb2cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1795498881),('M9eK0T7MGE1KJbYd2kOifEyqiw6TWEFZHsngFdhB',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJ2RmdJek5kM0JMZUJ0MVhMWE5oTXJPeFphSFVoanV5WEY0cFNTZkpKIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1794447534),('nooF9lR1AIrdFXypqKwmxY36yGlnvU57luW3OJlU',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJ1MHpCWjY4OFZaSGRmYlZRNU1KWUpYbkJ3aDFLN1ZWeGV1UHVFaVVTIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2xvZ2luIiwicm91dGUiOiJsb2dpbiJ9fQ==',1794411661),('skLHk09EAgZK0MKZ4FVqT7mgBuI7Emo8CNULeWpu',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','eyJfdG9rZW4iOiIwVFZwN2VPckZqUDA4Z3RicVpYTDNOUGtRWDlnSnBMZURlb2hMOUlaIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1795446419),('ZhrQt09xons5SENpbTJgTaUtAPT1NV9pO5GdN1Be',NULL,'127.0.0.1','Python-urllib/3.12','eyJfdG9rZW4iOiJRTlZGeGJXVDhEdEQxRVh1S3p1YzBtYndmdjYzdWVlc3BNQ2NSclR6IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1795498899),('Zr7Jpqh062pys5dr8hQ7T8adNVSReoxcaRo18b8z',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','eyJfdG9rZW4iOiI4ZFNESXpGOXpRZ2lxanNXb1RKUkNXNDhjN3FzWnh6QWZiSkRhb1JsIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2xvZ2luIiwicm91dGUiOiJsb2dpbiJ9fQ==',1794411432),('ZXmvrPK5uKgBt99oF8aSpJONxScKkgABbDNkQ0Z5',NULL,'127.0.0.1','Python-urllib/3.12','eyJfdG9rZW4iOiJKM2ZYQURmaUd1b0ZuVk5nd1paeGNlcDFFUENjbkxPeDdIRmU5UmhCIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9rYXRhbG9nP2plbmlzPXJ1YW5nYW4iLCJyb3V0ZSI6ImthdGFsb2cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1795498861);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sops`
--

DROP TABLE IF EXISTS `sops`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sops` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(10) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  `tujuan` text NOT NULL,
  `poin` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`poin`)),
  `urutan` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sops_kode_unique` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sops`
--

LOCK TABLES `sops` WRITE;
/*!40000 ALTER TABLE `sops` DISABLE KEYS */;
INSERT INTO `sops` VALUES (1,'SOP-01','Tata Tertib Ruang Laboratorium','Ketentuan berpakaian rapi, larangan membawa makanan/minuman, dan kewajiban menjaga kebersihan ruangan praktikum.','Menjaga ketertiban, kebersihan, dan keamanan perangkat laboratorium selama kegiatan praktikum berlangsung.','[\"Berpakaian rapi dan sopan (tanpa sandal jepit).\",\"Dilarang membawa makanan atau minuman ke dalam ruangan laboratorium.\",\"Tas diletakkan di tempat yang telah disediakan.\",\"Dilarang mengubah pengaturan sistem atau menginstal software tanpa izin laboran.\",\"Ponsel diatur dalam mode senyap selama kegiatan berlangsung.\",\"Rapikan meja dan kursi sebelum meninggalkan ruangan.\",\"Hanya pengguna yang tercatat dalam peminjaman yang boleh berada di ruangan.\"]',1,'2026-11-11 08:49:52','2026-11-11 08:49:52'),(2,'SOP-02','Peminjaman Ruang & Alat','Mekanisme reservasi online SIARKA, verifikasi dosen penanggung jawab, serta penyerahan kunci ruangan.','Menetapkan alur pengajuan dan persetujuan peminjaman agar ketersediaan fasilitas tercatat dan tidak berbenturan jadwal.','[\"Ajukan peminjaman melalui menu Ajukan Peminjaman pada SIARKA.\",\"Dosen penanggung jawab memverifikasi pengajuan mahasiswa.\",\"Laboran memeriksa ketersediaan alat\\/bahan dan benturan jadwal ruangan.\",\"Ambil kunci ruangan dengan menunjukkan bukti persetujuan dan identitas.\",\"Satu akun hanya boleh memiliki satu peminjaman aktif per hari.\",\"Peminjaman tidak boleh dialihkan kepada pihak lain.\",\"Peminjaman dibatalkan otomatis apabila kunci tidak diambil sesuai jadwal.\"]',2,'2026-11-11 08:49:52','2026-11-11 08:49:52'),(3,'SOP-03','Keselamatan & Kesehatan Kerja','Prosedur penanganan darurat kelistrikan, evakuasi kebakaran, dan pertolongan pertama di area laboratorium.','Memberi panduan tindakan cepat dan tepat pada kondisi darurat sehingga risiko cedera dan kerusakan dapat ditekan.','[\"Periksa kabel dan stop kontak sebelum perangkat digunakan.\",\"Bila terjadi percikan api atau bau gosong: matikan perangkat dan segera lapor laboran.\",\"Bila terjadi kebakaran: matikan sumber listrik, lakukan evakuasi, dan berkumpul di titik kumpul.\",\"Ketahui lokasi APAR, kotak P3K, dan pintu darurat sebelum memulai kegiatan.\",\"Setiap cedera wajib dicatat pada incident log laboratorium.\"]',3,'2026-11-11 08:49:52','2026-11-11 08:49:52'),(4,'SOP-04','Pengembalian & Pelaporan','Pemeriksaan fisik kondisi perangkat pasca-penggunaan, pencatatan insiden log, dan verifikasi akhir laboran.','Memastikan fasilitas kembali dalam kondisi tercatat sehingga kerusakan dapat ditelusuri dan ditindaklanjuti.','[\"Periksa kondisi komputer dan kebersihan ruangan setelah digunakan.\",\"Matikan perangkat, AC, dan lampu sebelum meninggalkan ruangan.\",\"Catat kerusakan atau kejadian tidak biasa pada incident log.\",\"Serahkan kunci ruangan kembali kepada laboran.\",\"Laboran menutup peminjaman dengan status Selesai.\",\"Kerusakan akibat kelalaian menjadi tanggung jawab peminjam.\"]',4,'2026-11-11 08:49:52','2026-11-11 08:49:52');
/*!40000 ALTER TABLE `sops` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_prodis`
--

DROP TABLE IF EXISTS `staff_prodis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_prodis` (
  `id_pegawai` varchar(30) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `id_prodi` bigint(20) unsigned NOT NULL,
  `email` varchar(50) NOT NULL,
  `no_whatsapp` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_pegawai`),
  UNIQUE KEY `uq_staff_prodis_email` (`email`),
  KEY `ix_staff_prodis_prodi` (`id_prodi`),
  CONSTRAINT `fk_staff_prodis_akun` FOREIGN KEY (`email`) REFERENCES `akuns` (`email`) ON UPDATE CASCADE,
  CONSTRAINT `fk_staff_prodis_prodi` FOREIGN KEY (`id_prodi`) REFERENCES `prodis` (`id_prodi`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_prodis`
--

LOCK TABLES `staff_prodis` WRITE;
/*!40000 ALTER TABLE `staff_prodis` DISABLE KEYS */;
INSERT INTO `staff_prodis` VALUES ('43575','jessicaaa',3,'jessica@politala.ac.id','085751401610');
/*!40000 ALTER TABLE `staff_prodis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wa_logs`
--

DROP TABLE IF EXISTS `wa_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wa_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_peminjaman` bigint(20) unsigned NOT NULL,
  `jenis` enum('terlambat') NOT NULL DEFAULT 'terlambat',
  `ke_ke` tinyint(4) NOT NULL,
  `nomor` varchar(20) NOT NULL,
  `status` enum('terkirim','gagal','dilewati') NOT NULL DEFAULT 'dilewati',
  `dikirim_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wa_logs_id_peminjaman_ke_ke_unique` (`id_peminjaman`,`ke_ke`),
  KEY `wa_logs_id_peminjaman_index` (`id_peminjaman`),
  CONSTRAINT `wa_logs_id_peminjaman_foreign` FOREIGN KEY (`id_peminjaman`) REFERENCES `peminjamans` (`id_peminjaman`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wa_logs`
--

LOCK TABLES `wa_logs` WRITE;
/*!40000 ALTER TABLE `wa_logs` DISABLE KEYS */;
INSERT INTO `wa_logs` VALUES (1,5,'terlambat',1,'085751401610','terkirim','2026-11-23 23:20:33','2026-11-23 16:20:40','2026-11-23 16:20:40');
/*!40000 ALTER TABLE `wa_logs` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-24 13:08:54
