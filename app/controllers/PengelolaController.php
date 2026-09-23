<?php
/**
 * Pengelola Controller
 *
 * Controller untuk fitur Pengelola
 * Compatible dengan PHP 7.3+
 */

class PengelolaController
{
    /** @var TransaksiBeli */
    private $transaksiBeli;

    /** @var TransaksiJual */
    private $transaksiJual;

    /** @var KategoriSampah */
    private $kategoriModel;

    /** @var HargaSampah */
    private $hargaModel;

    /** @var Pengepul */
    private $pengepulModel;

    /** @var Nasabah */
    private $nasabahModel;

    /** @var StokSampah */
    private $stokModel;

    /** @var BankSampah */
    private $bankSampahModel;

    /** @var PengajuanPencairan */
    private $pencairanModel;

    /** @var int */
    private $bankSampahId;

    public function __construct()
    {
        requireLogin();
        requireRole(['PENGELOLA'], 'dashboard');

        // Load models
        require_once MODEL_PATH . '/TransaksiBeli.php';
        require_once MODEL_PATH . '/TransaksiJual.php';
        require_once MODEL_PATH . '/KategoriSampah.php';
        require_once MODEL_PATH . '/HargaSampah.php';
        require_once MODEL_PATH . '/Pengepul.php';
        require_once MODEL_PATH . '/Nasabah.php';
        require_once MODEL_PATH . '/StokSampah.php';
        require_once MODEL_PATH . '/BankSampah.php';
        require_once MODEL_PATH . '/PengajuanPencairan.php';
        require_once MODEL_PATH . '/MutasiSaldo.php';

        $this->transaksiBeli = new TransaksiBeli();
        $this->transaksiJual = new TransaksiJual();
        $this->kategoriModel = new KategoriSampah();
        $this->hargaModel = new HargaSampah();
        $this->pengepulModel = new Pengepul();
        $this->nasabahModel = new Nasabah();
        $this->stokModel = new StokSampah();
        $this->bankSampahModel = new BankSampah();
        $this->pencairanModel = new PengajuanPencairan();
        $this->mutasiModel = new MutasiSaldo();

        // Ambil bank sampah dari session user
        $this->bankSampahId = $_SESSION['user_bank_sampah_id'] ?? null;

        if (!$this->bankSampahId) {
            setFlashMessage('warning', 'Anda belum terdaftar di bank sampah manapun.');
        }
    }

    public function index(): void
    {
        $this->dashboard();
    }

    /**
     * Dashboard Pengelola
     */
    public function dashboard(): void
    {
        $statsBeli = $this->transaksiBeli->getStatistik($this->bankSampahId);
        $statsJual = $this->transaksiJual->getStatistik($this->bankSampahId);
        $recentBeli = $this->transaksiBeli->getByBankSampah($this->bankSampahId, 5);
        $recentJual = $this->transaksiJual->getByBankSampah($this->bankSampahId, 5);
        $stokList = $this->stokModel->getByBankSampah($this->bankSampahId);
        $bankInfo = $this->bankSampahId ? $this->bankSampahModel->findById($this->bankSampahId) : null;

        // Statistik pencairan
        $statsPencairan = $this->pencairanModel->getStatistik($this->bankSampahId);

        $user = currentUser();
        $flash = getFlashMessage();

        include VIEW_PATH . '/pengelola/dashboard.php';
    }

    /**
     * Transaksi Beli - List
     */
    public function transaksiBeli(): void
    {
        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $tanggalFrom = $_GET['tanggal_from'] ?? null;
        $tanggalTo = $_GET['tanggal_to'] ?? null;

        $transaksis = $this->transaksiBeli->getByBankSampah($this->bankSampahId, $perPage, $offset, $tanggalFrom, $tanggalTo);
        $stats = $this->transaksiBeli->getStatistik($this->bankSampahId, $tanggalFrom, $tanggalTo);

        $user = currentUser();
        $flash = getFlashMessage();

        include VIEW_PATH . '/pengelola/transaksi_beli/index.php';
    }

    /**
     * Form Transaksi Beli
     */
    public function transaksiBeliForm(): void
    {
        $user = currentUser();
        $flash = getFlashMessage();
        $errors = [];

        // Ambil data untuk dropdown
        $kategoriList = $this->kategoriModel->getAll(true);
        $hargaList = $this->hargaModel->getLatestByBank($this->bankSampahId);
        $nasabahList = $this->nasabahModel->getAll(100, 0, $this->bankSampahId);

        // Buat map harga
        $hargaMap = [];
        foreach ($hargaList as $h) {
            $hargaMap[$h['kategori_id']] = [
                'harga_beli' => $h['harga_beli'],
                'kategori_nama' => $h['kategori_nama']
            ];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->prosesTransaksiBeli($hargaMap);
            if ($result['success']) {
                return;
            }
            $errors = $result['errors'];
        }

        include VIEW_PATH . '/pengelola/transaksi_beli/form.php';
    }

    /**
     * Proses transaksi beli
     */
    private function prosesTransaksiBeli(array $hargaMap): array
    {
        $errors = [];

        // Validasi
        $tipe = $_POST['tipe_penjual'] ?? '';
        $metode = $_POST['metode_bayar'] ?? '';

        if (empty($tipe)) {
            $errors[] = 'Tipe penjual harus dipilih.';
        }

        if (empty($metode)) {
            $errors[] = 'Metode bayar harus dipilih.';
        }

        if ($tipe === 'NASABAH' && empty($_POST['nasabah_id'])) {
            $errors[] = 'Nasabah harus dipilih.';
        }

        if ($tipe === 'NON_NASABAH' && empty(trim($_POST['nama_penjual'] ?? ''))) {
            $errors[] = 'Nama penjual harus diisi.';
        }

        // Ambil items
        $items = [];
        $totalBerat = 0;
        $totalNilai = 0;

        $kategoriIds = $_POST['kategori_id'] ?? [];

        foreach ($kategoriIds as $i => $kategoriId) {
            if (empty($kategoriId)) continue;

            $berat = (float) ($_POST['berat'][$i] ?? 0);
            if ($berat <= 0) continue;

            if (!isset($hargaMap[$kategoriId])) {
                $errors[] = 'Harga untuk kategori tidak ditemukan.';
                continue;
            }

            $harga = $hargaMap[$kategoriId]['harga_beli'];
            $subtotal = $berat * $harga;

            $items[] = [
                'kategori_id' => $kategoriId,
                'berat_kg' => $berat,
                'harga_per_kg' => $harga,
                'subtotal' => $subtotal
            ];

            $totalBerat += $berat;
            $totalNilai += $subtotal;
        }

        if (empty($items)) {
            $errors[] = 'Minimal harus ada 1 item sampah.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Simpan transaksi
        $data = [
            'bank_sampah_id' => $this->bankSampahId,
            'tipe_penjual' => $tipe,
            'nasabah_id' => $tipe === 'NASABAH' ? (int) $_POST['nasabah_id'] : null,
            'nama_penjual' => $tipe === 'NON_NASABAH' ? trim($_POST['nama_penjual']) : null,
            'metode_bayar' => $metode,
            'total_berat' => $totalBerat,
            'total_nilai' => $totalNilai,
            'petugas_id' => currentUserId(),
            'catatan' => trim($_POST['catatan'] ?? '')
        ];

        $transaksiId = $this->transaksiBeli->create($data, $items);

        if ($transaksiId) {
            // Update stok
            foreach ($items as $item) {
                $this->stokModel->tambah($this->bankSampahId, $item['kategori_id'], $item['berat_kg']);
            }

            setFlashMessage('success', 'Transaksi berhasil disimpan!');
            redirect('pengelola/transaksi-beli');
            return ['success' => true, 'errors' => []];
        } else {
            return ['success' => false, 'errors' => ['Gagal menyimpan transaksi.']];
        }
    }

    /**
     * Transaksi Jual - List
     */
    public function transaksiJual(): void
    {
        $transaksis = $this->transaksiJual->getByBankSampah($this->bankSampahId, 20);
        $stats = $this->transaksiJual->getStatistik($this->bankSampahId);
        $stokList = $this->stokModel->getByBankSampah($this->bankSampahId);
        $pengepulList = $this->pengepulModel->getAll($this->bankSampahId, 'AKTIF');

        $user = currentUser();
        $flash = getFlashMessage();

        include VIEW_PATH . '/pengelola/transaksi_jual/index.php';
    }

    /**
     * Form Transaksi Jual
     */
    public function transaksiJualForm(): void
    {
        $user = currentUser();
        $flash = getFlashMessage();
        $errors = [];

        $stokList = $this->stokModel->getByBankSampah($this->bankSampahId);
        $hargaList = $this->hargaModel->getLatestByBank($this->bankSampahId);
        $pengepulList = $this->pengepulModel->getAll($this->bankSampahId, 'AKTIF');

        // Buat map harga jual
        $hargaMap = [];
        foreach ($hargaList as $h) {
            $hargaMap[$h['kategori_id']] = [
                'harga_jual' => $h['harga_jual'],
                'kategori_nama' => $h['kategori_nama']
            ];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->prosesTransaksiJual($hargaMap);
            if ($result['success']) {
                return;
            }
            $errors = $result['errors'];
        }

        include VIEW_PATH . '/pengelola/transaksi_jual/form.php';
    }

    /**
     * Proses transaksi jual
     */
    private function prosesTransaksiJual(array $hargaMap): array
    {
        $errors = [];

        if (empty($_POST['pengepul_id'])) {
            $errors[] = 'Pengepul harus dipilih.';
        }

        // Ambil items
        $items = [];
        $totalBerat = 0;
        $totalNilai = 0;

        $kategoriIds = $_POST['kategori_id'] ?? [];

        foreach ($kategoriIds as $i => $kategoriId) {
            if (empty($kategoriId)) continue;

            $berat = (float) ($_POST['berat'][$i] ?? 0);
            if ($berat <= 0) continue;

            // Cek stok
            $stok = $this->stokModel->getStok($this->bankSampahId, $kategoriId);
            if ($berat > $stok) {
                $errors[] = 'Stok tidak cukup untuk ' . ($hargaMap[$kategoriId]['kategori_nama'] ?? 'kategori');
                continue;
            }

            if (!isset($hargaMap[$kategoriId])) {
                $errors[] = 'Harga jual untuk kategori tidak ditemukan.';
                continue;
            }

            $harga = $hargaMap[$kategoriId]['harga_jual'];
            $subtotal = $berat * $harga;

            $items[] = [
                'kategori_id' => $kategoriId,
                'berat_kg' => $berat,
                'harga_per_kg' => $harga,
                'subtotal' => $subtotal
            ];

            $totalBerat += $berat;
            $totalNilai += $subtotal;
        }

        if (empty($items)) {
            $errors[] = 'Minimal harus ada 1 item sampah.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Simpan transaksi
        $data = [
            'bank_sampah_id' => $this->bankSampahId,
            'pengepul_id' => (int) $_POST['pengepul_id'],
            'total_berat' => $totalBerat,
            'total_nilai' => $totalNilai,
            'petugas_id' => currentUserId(),
            'status_bayar' => isset($_POST['status_bayar']) ? 'LUNAS' : 'BELUM',
            'catatan' => trim($_POST['catatan'] ?? '')
        ];

        $transaksiId = $this->transaksiJual->create($data, $items);

        if ($transaksiId) {
            setFlashMessage('success', 'Transaksi jual berhasil disimpan!');
            redirect('pengelola/transaksi-jual');
            return ['success' => true, 'errors' => []];
        } else {
            return ['success' => false, 'errors' => ['Gagal menyimpan transaksi.']];
        }
    }

    /**
     * Kelola Kategori Sampah
     */
    public function kategori(): void
    {
        $kategoris = $this->kategoriModel->getAll();

        $user = currentUser();
        $flash = getFlashMessage();

        include VIEW_PATH . '/pengelola/kategori/index.php';
    }

    /**
     * Form Kategori
     */
    public function kategoriForm(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : null;
        $editKategori = null;

        if ($id) {
            $editKategori = $this->kategoriModel->findById($id);
            if (!$editKategori) {
                setFlashMessage('error', 'Kategori tidak ditemukan.');
                redirect('pengelola/kategori');
                return;
            }
        }

        $user = currentUser();
        $flash = getFlashMessage();
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->prosesKategoriSave($id);
            if (empty($errors)) {
                return;
            }
        }

        include VIEW_PATH . '/pengelola/kategori/form.php';
    }

    private function prosesKategoriSave($id): array
    {
        $errors = [];

        if (empty(trim($_POST['nama'] ?? ''))) {
            $errors[] = 'Nama kategori harus diisi.';
        }
        if (empty($_POST['jenis'])) {
            $errors[] = 'Jenis harus dipilih.';
        }

        if (!empty($errors)) {
            return $errors;
        }

        $data = [
            'nama' => trim($_POST['nama']),
            'jenis' => $_POST['jenis'],
            'satuan' => $_POST['satuan'] ?? 'kg',
            'aktif' => isset($_POST['aktif']) ? 1 : 0
        ];

        if ($id) {
            $this->kategoriModel->update($id, $data);
            setFlashMessage('success', 'Kategori berhasil diupdate.');
        } else {
            $this->kategoriModel->create($data);
            setFlashMessage('success', 'Kategori berhasil ditambahkan.');
        }

        redirect('pengelola/kategori');
        return [];
    }

    public function kategoriDelete(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : null;

        if (!$id) {
            setFlashMessage('error', 'ID tidak valid.');
            redirect('pengelola/kategori');
            return;
        }

        $this->kategoriModel->delete($id);
        setFlashMessage('success', 'Kategori berhasil dihapus.');
        redirect('pengelola/kategori');
    }

    /**
     * Kelola Harga Sampah
     */
    public function harga(): void
    {
        $hargaList = $this->hargaModel->getLatestByBank($this->bankSampahId);
        $kategoriList = $this->kategoriModel->getAll(true);

        $user = currentUser();
        $flash = getFlashMessage();

        include VIEW_PATH . '/pengelola/harga/index.php';
    }

    /**
     * Form Harga
     */
    public function hargaForm(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : null;
        $editHarga = null;

        if ($id) {
            $editHarga = $this->hargaModel->findById($id);
            if (!$editHarga) {
                setFlashMessage('error', 'Harga tidak ditemukan.');
                redirect('pengelola/harga');
                return;
            }
        }

        $kategoriList = $this->kategoriModel->getAll(true);

        $user = currentUser();
        $flash = getFlashMessage();
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->prosesHargaSave($id);
            if (empty($errors)) {
                return;
            }
        }

        include VIEW_PATH . '/pengelola/harga/form.php';
    }

    private function prosesHargaSave($id): array
    {
        $errors = [];

        if (empty($_POST['kategori_id'])) {
            $errors[] = 'Kategori harus dipilih.';
        }
        if (!is_numeric($_POST['harga_beli'] ?? '') || (float) $_POST['harga_beli'] <= 0) {
            $errors[] = 'Harga beli harus angka positif.';
        }
        if (!is_numeric($_POST['harga_jual'] ?? '') || (float) $_POST['harga_jual'] <= 0) {
            $errors[] = 'Harga jual harus angka positif.';
        }

        if (!empty($errors)) {
            return $errors;
        }

        $kategoriId = (int) $_POST['kategori_id'];

        if ($id) {
            // Update
            $data = [
                'harga_beli' => (float) $_POST['harga_beli'],
                'harga_jual' => (float) $_POST['harga_jual'],
                'berlaku_mulai' => $_POST['berlaku_mulai'] ?? date('Y-m-d'),
                'aktif' => isset($_POST['aktif']) ? 1 : 0
            ];
            $this->hargaModel->update($id, $data);
            setFlashMessage('success', 'Harga berhasil diupdate.');
        } else {
            // Nonaktifkan harga lama
            $this->hargaModel->deactivateOld($this->bankSampahId, $kategoriId);

            // Buat harga baru
            $data = [
                'bank_sampah_id' => $this->bankSampahId,
                'kategori_id' => $kategoriId,
                'harga_beli' => (float) $_POST['harga_beli'],
                'harga_jual' => (float) $_POST['harga_jual'],
                'berlaku_mulai' => $_POST['berlaku_mulai'] ?? date('Y-m-d'),
                'aktif' => 1
            ];
            $this->hargaModel->create($data);
            setFlashMessage('success', 'Harga berhasil ditambahkan.');
        }

        redirect('pengelola/harga');
        return [];
    }

    /**
     * Kelola Pengepul
     */
    public function pengepul(): void
    {
        $pengepulList = $this->pengepulModel->getAll($this->bankSampahId);

        $user = currentUser();
        $flash = getFlashMessage();

        include VIEW_PATH . '/pengelola/pengepul/index.php';
    }

    /**
     * Form Pengepul
     */
    public function pengepulForm(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : null;
        $editPengepul = null;

        if ($id) {
            $editPengepul = $this->pengepulModel->findById($id);
            if (!$editPengepul) {
                setFlashMessage('error', 'Pengepul tidak ditemukan.');
                redirect('pengelola/pengepul');
                return;
            }
        }

        $user = currentUser();
        $flash = getFlashMessage();
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->prosesPengepulSave($id);
            if (empty($errors)) {
                return;
            }
        }

        include VIEW_PATH . '/pengelola/pengepul/form.php';
    }

    private function prosesPengepulSave($id): array
    {
        $errors = [];

        if (empty(trim($_POST['nama'] ?? ''))) {
            $errors[] = 'Nama pengepul harus diisi.';
        }

        if (!empty($errors)) {
            return $errors;
        }

        $data = [
            'bank_sampah_id' => $this->bankSampahId,
            'nama' => trim($_POST['nama']),
            'no_hp' => trim($_POST['no_hp'] ?? ''),
            'alamat' => trim($_POST['alamat'] ?? ''),
            'status' => $_POST['status'] ?? 'AKTIF'
        ];

        if ($id) {
            $this->pengepulModel->update($id, $data);
            setFlashMessage('success', 'Pengepul berhasil diupdate.');
        } else {
            $this->pengepulModel->create($data);
            setFlashMessage('success', 'Pengepul berhasil ditambahkan.');
        }

        redirect('pengelola/pengepul');
        return [];
    }

    public function pengepulDelete(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : null;

        if (!$id) {
            setFlashMessage('error', 'ID tidak valid.');
            redirect('pengelola/pengepul');
            return;
        }

        $this->pengepulModel->delete($id);
        setFlashMessage('success', 'Pengepul berhasil dihapus.');
        redirect('pengelola/pengepul');
    }

    /**
     * Kelola Nasabah
     */
    public function nasabah(): void
    {
        $nasabahList = $this->nasabahModel->getAll(50, 0, $this->bankSampahId);

        $user = currentUser();
        $flash = getFlashMessage();

        include VIEW_PATH . '/pengelola/nasabah/index.php';
    }

    /**
     * Laporan
     */
    public function laporan(): void
    {
        $tanggalFrom = $_GET['tanggal_from'] ?? date('Y-m-01');
        $tanggalTo = $_GET['tanggal_to'] ?? date('Y-m-d');

        $statsBeli = $this->transaksiBeli->getStatistik($this->bankSampahId, $tanggalFrom, $tanggalTo);
        $statsJual = $this->transaksiJual->getStatistik($this->bankSampahId);

        $user = currentUser();
        $flash = getFlashMessage();

        include VIEW_PATH . '/pengelola/laporan/index.php';
    }

    /**
     * Export Laporan Operasional ke PDF
     */
    public function laporanExportPdf(): void
    {
        requireLogin();
        requireRole(['PENGELOLA'], 'dashboard');

        $tanggalFrom = $_GET['tanggal_from'] ?? date('Y-m-01');
        $tanggalTo = $_GET['tanggal_to'] ?? date('Y-m-d');

        $statsBeli = $this->transaksiBeli->getStatistik($this->bankSampahId, $tanggalFrom, $tanggalTo);
        $statsJual = $this->transaksiJual->getStatistik($this->bankSampahId);

        $bankInfo = $this->bankSampahId ? $this->bankSampahModel->findById($this->bankSampahId) : null;

        // Load LaporanPdf helper
        require_once LIB_PATH . '/LaporanPdf.php';

        $pdf = new LaporanPdf();
        $pdf->generateLaporanOperasional(
            $statsBeli,
            $statsJual,
            $tanggalFrom,
            $tanggalTo,
            $bankInfo['nama'] ?? ''
        );
    }

    /**
     * Export Laporan Pencairan ke PDF
     */
    public function pencairanExportPdf(): void
    {
        requireLogin();
        requireRole(['PENGELOLA'], 'dashboard');

        $pengajuans = $this->pencairanModel->getAll($this->bankSampahId, null, 100, 0);
        $statistik = $this->pencairanModel->getStatistik($this->bankSampahId);

        $bankInfo = $this->bankSampahId ? $this->bankSampahModel->findById($this->bankSampahId) : null;

        // Load LaporanPdf helper
        require_once LIB_PATH . '/LaporanPdf.php';

        $pdf = new LaporanPdf();
        $pdf->generateLaporanPencairan(
            $pengajuans,
            $statistik,
            $bankInfo['nama'] ?? ''
        );
    }

    /**
     * Daftar Pengajuan Pencairan
     */
    public function pencairan(): void
    {
        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $statusFilter = $_GET['status'] ?? null;

        $pengajuans = $this->pencairanModel->getAll($this->bankSampahId, $statusFilter, $perPage, $offset);
        $totalData = $this->pencairanModel->countAll($this->bankSampahId, $statusFilter);
        $totalPages = max(1, ceil($totalData / $perPage));
        $statistik = $this->pencairanModel->getStatistik($this->bankSampahId);

        $user = currentUser();
        $flash = getFlashMessage();

        include VIEW_PATH . '/pengelola/pencairan/index.php';
    }

    /**
     * Detail Pengajuan Pencairan
     */
    public function pencairanDetail(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : null;

        if (!$id) {
            setFlashMessage('error', 'ID pengajuan tidak valid.');
            redirect('pengelola/pencairan');
            return;
        }

        $pengajuan = $this->pencairanModel->findById($id);

        if (!$pengajuan) {
            setFlashMessage('error', 'Pengajuan tidak ditemukan.');
            redirect('pengelola/pencairan');
            return;
        }

        // Cek apakah pengajuan milik bank sampah ini
        if ($pengajuan['bank_sampah_id'] != $this->bankSampahId) {
            setFlashMessage('error', 'Anda tidak memiliki akses ke pengajuan ini.');
            redirect('pengelola/pencairan');
            return;
        }

        $user = currentUser();
        $flash = getFlashMessage();

        include VIEW_PATH . '/pengelola/pencairan/detail.php';
    }

    /**
     * Setujui Pengajuan Pencairan
     */
    public function pencairanSetuju(): void
    {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : null;

        if (!$id) {
            setFlashMessage('error', 'ID pengajuan tidak valid.');
            redirect('pengelola/pencairan');
            return;
        }

        $pengajuan = $this->pencairanModel->findById($id);

        if (!$pengajuan) {
            setFlashMessage('error', 'Pengajuan tidak ditemukan.');
            redirect('pengelola/pencairan');
            return;
        }

        if ($pengajuan['bank_sampah_id'] != $this->bankSampahId) {
            setFlashMessage('error', 'Anda tidak memiliki akses ke pengajuan ini.');
            redirect('pengelola/pencairan');
            return;
        }

        if ($pengajuan['status'] !== PengajuanPencairan::STATUS_DIAJUKAN) {
            setFlashMessage('error', 'Pengajuan sudah diproses sebelumnya.');
            redirect('pengelola/pencairan');
            return;
        }

        // Update status menjadi DISETUJUI
        $success = $this->pencairanModel->updateStatus($id, PengajuanPencairan::STATUS_DISETUJUI, currentUserId());

        if ($success) {
            setFlashMessage('success', 'Pengajuan ' . $pengajuan['no_pengajuan'] . ' telah disetujui. Silakan proses pencairan.');
        } else {
            setFlashMessage('error', 'Gagal memproses pengajuan.');
        }

        redirect('pengelola/pencairan');
    }

    /**
     * Tolak Pengajuan Pencairan
     */
    public function pencairanTolak(): void
    {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : null;
        $alasan = trim($_POST['alasan'] ?? '');

        if (!$id) {
            setFlashMessage('error', 'ID pengajuan tidak valid.');
            redirect('pengelola/pencairan');
            return;
        }

        if (empty($alasan)) {
            setFlashMessage('error', 'Alasan penolakan harus diisi.');
            redirect('pengelola/pencairan/detail?id=' . $id);
            return;
        }

        $pengajuan = $this->pencairanModel->findById($id);

        if (!$pengajuan) {
            setFlashMessage('error', 'Pengajuan tidak ditemukan.');
            redirect('pengelola/pencairan');
            return;
        }

        if ($pengajuan['bank_sampah_id'] != $this->bankSampahId) {
            setFlashMessage('error', 'Anda tidak memiliki akses ke pengajuan ini.');
            redirect('pengelola/pencairan');
            return;
        }

        if ($pengajuan['status'] !== PengajuanPencairan::STATUS_DIAJUKAN) {
            setFlashMessage('error', 'Pengajuan sudah diproses sebelumnya.');
            redirect('pengelola/pencairan');
            return;
        }

        // Update status menjadi DITOLAK
        $success = $this->pencairanModel->updateStatus($id, PengajuanPencairan::STATUS_DITOLAK, currentUserId(), $alasan);

        if ($success) {
            setFlashMessage('success', 'Pengajuan ' . $pengajuan['no_pengajuan'] . ' telah ditolak.');
        } else {
            setFlashMessage('error', 'Gagal memproses pengajuan.');
        }

        redirect('pengelola/pencairan');
    }

    /**
     * Proses Pencairan (Tandai sudah dicairkan dan kurangi saldo)
     */
    public function pencairanCairkan(): void
    {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : null;

        if (!$id) {
            setFlashMessage('error', 'ID pengajuan tidak valid.');
            redirect('pengelola/pencairan');
            return;
        }

        $pengajuan = $this->pencairanModel->findById($id);

        if (!$pengajuan) {
            setFlashMessage('error', 'Pengajuan tidak ditemukan.');
            redirect('pengelola/pencairan');
            return;
        }

        if ($pengajuan['bank_sampah_id'] != $this->bankSampahId) {
            setFlashMessage('error', 'Anda tidak memiliki akses ke pengajuan ini.');
            redirect('pengelola/pencairan');
            return;
        }

        if ($pengajuan['status'] !== PengajuanPencairan::STATUS_DISETUJUI) {
            setFlashMessage('error', 'Pengajuan belum disetujui atau sudah dicairkan.');
            redirect('pengelola/pencairan');
            return;
        }

        // Update status menjadi DICAIRKAN
        $success = $this->pencairanModel->updateStatus($id, PengajuanPencairan::STATUS_DICAIRKAN, currentUserId());

        if ($success) {
            // Kurangi saldo农户 (updateSaldo menerima nilai negatif untuk mengurangi)
            $this->nasabahModel->updateSaldo($pengajuan['nasabah_id'], -$pengajuan['jumlah']);

            // Catat mutasi saldo
            $this->mutasiModel->create([
                'nasabah_id' => $pengajuan['nasabah_id'],
                'jenis' => 'PENCAIRAN',
                'jumlah' => $pengajuan['jumlah'],
                'referensi' => $pengajuan['no_pengajuan'],
                'keterangan' => 'Pencairan tabungan - ' . $pengajuan['metode'],
                'admin_id' => currentUserId()
            ]);

            setFlashMessage('success', 'Pengajuan ' . $pengajuan['no_pengajuan'] . ' telah dicairkan dan saldo农户 dikurangi.');
        } else {
            setFlashMessage('error', 'Gagal memproses pencairan.');
        }

        redirect('pengelola/pencairan');
    }
}
