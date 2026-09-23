<?php
/**
 * Nasabah Controller
 *
 * Controller untuk modul Nasabah
 * Compatible dengan PHP 7.3+
 */

class NasabahController
{
    /** @var Nasabah */
    private $nasabahModel;

    /** @var MutasiSaldo */
    private $mutasiModel;

    /** @var PengajuanPencairan */
    private $pencairanModel;

    public function __construct()
    {
        // Require login untuk semua method
        requireLogin();
        // Require role NASABAH
        requireRole(['NASABAH'], 'dashboard');

        // Load models
        require_once MODEL_PATH . '/Nasabah.php';
        require_once MODEL_PATH . '/MutasiSaldo.php';
        require_once MODEL_PATH . '/PengajuanPencairan.php';

        $this->nasabahModel = new Nasabah();
        $this->mutasiModel = new MutasiSaldo();
        $this->pencairanModel = new PengajuanPencairan();
    }

    /**
     * Get data Nasabah dari session user_id
     * Mengambil dari database untuk memastikan data valid
     *
     * @return array|null
     */
    private function getCurrentNasabah()
    {
        $userId = currentUserId();
        if (!$userId) {
            return null;
        }
        return $this->nasabahModel->findByUserId($userId);
    }

    /**
     * Dashboard - Tampilkan ringkasan saldo dan aktivitas terbaru
     */
    public function dashboard(): void
    {
        $nasabah = $this->getCurrentNasabah();

        if (!$nasabah) {
            setFlashMessage('error', 'Data akun tidak ditemukan.');
            redirect('logout');
            return;
        }

        $saldo = $this->nasabahModel->getSaldo($nasabah['id']);
        $saldoTersedia = $this->nasabahModel->getSaldoTersedia($nasabah['id']);
        $riwayatTerbaru = $this->mutasiModel->getRiwayat($nasabah['id'], 5);

        // Ambil pengajuan aktif
        $pengajuanAktif = $this->pencairanModel->getAktifByNasabah($nasabah['id']);
        $totalTerikat = $this->pencairanModel->getTotalTerikat($nasabah['id']);

        $user = currentUser();
        $flash = getFlashMessage();

        include VIEW_PATH . '/nasabah/dashboard.php';
    }

    /**
     * Riwayat - Tampilkan semua riwayat mutasi saldo
     */
    public function riwayat(): void
    {
        $nasabah = $this->getCurrentNasabah();

        if (!$nasabah) {
            setFlashMessage('error', 'Data akun tidak ditemukan.');
            redirect('logout');
            return;
        }

        // Pagination
        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        // Ambil data
        $riwayat = $this->mutasiModel->getRiwayatLengkap($nasabah['id'], $perPage, $offset);
        $totalData = $this->mutasiModel->countRiwayat($nasabah['id']);
        $totalPages = max(1, ceil($totalData / $perPage));

        $user = currentUser();
        $flash = getFlashMessage();

        include VIEW_PATH . '/nasabah/riwayat.php';
    }

    /**
     * Cairkan - Form dan proses pengajuan pencairan
     */
    public function cairkan(): void
    {
        $nasabah = $this->getCurrentNasabah();

        if (!$nasabah) {
            setFlashMessage('error', 'Data akun tidak ditemukan.');
            redirect('logout');
            return;
        }

        $saldo = $this->nasabahModel->getSaldo($nasabah['id']);
        $saldoTersedia = $this->nasabahModel->getSaldoTersedia($nasabah['id']);

        // Ambil pengajuan aktif
        $pengajuanAktif = $this->pencairanModel->getAktifByNasabah($nasabah['id']);
        $totalTerikat = $this->pencairanModel->getTotalTerikat($nasabah['id']);

        $user = currentUser();
        $flash = getFlashMessage();
        $errors = [];
        $oldInput = [];

        // Proses form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->prosesPengajuan($nasabah, $saldoTersedia, $oldInput);
        }

        // Jika tidak ada error dan berhasil
        if (empty($errors) && isset($_POST['submit'])) {
            // Akan redirect, tidak perlu render view
            return;
        }

        include VIEW_PATH . '/nasabah/cairkan.php';
    }

    /**
     * Proses pengajuan pencairan
     *
     * @param array $nasabah
     * @param float $saldoTersedia
     * @param array $oldInput
     * @return array Error messages
     */
    private function prosesPengajuan(array $nasabah, float $saldoTersedia, array &$oldInput): array
    {
        // Ambil data dari form
        $oldInput = [
            'jumlah' => $_POST['jumlah'] ?? '',
            'metode' => $_POST['metode'] ?? '',
            'tujuan_transfer' => $_POST['tujuan_transfer'] ?? ''
        ];

        // Validasi
        $errors = PengajuanPencairan::validate($oldInput, $saldoTersedia);

        if (!empty($errors)) {
            return $errors;
        }

        // Konversi jumlah ke float
        $jumlah = (float) str_replace(['.', ','], ['', '.'], $oldInput['jumlah']);

        // Siapkan data untuk disimpan
        $data = [
            'nasabah_id' => $nasabah['id'],
            'jumlah' => $jumlah,
            'metode' => $oldInput['metode'],
            'tujuan_transfer' => ($oldInput['metode'] === 'TRANSFER') ? trim($oldInput['tujuan_transfer']) : null
        ];

        // Simpan pengajuan
        $pengajuanId = $this->pencairanModel->create($data);

        if ($pengajuanId) {
            setFlashMessage('success', 'Pengajuan pencairan berhasil diajukan. Nomor: ' . $this->pencairanModel->generateNoPengajuan());
            redirect('nasabah/cairkan');
            return [];
        } else {
            $errors[] = 'Gagal menyimpan pengajuan. Silakan coba lagi.';
            return $errors;
        }
    }

    /**
     * Method placeholder - akan diimplementasi di fase berikutnya
     */
    public function setor(): void
    {
        echo "<h1>Setor Sampah</h1>";
        echo "<p>[Placeholder] Fitur setor sampah akan diimplementasi di fase berikutnya.</p>";
        echo "<a href='" . base_url('nasabah/dashboard') . "'>Kembali ke Dashboard</a>";
    }

    /**
     * Method placeholder - akan diimplementasi di fase berikutnya
     */
    public function profil(): void
    {
        echo "<h1>Profil Saya</h1>";
        echo "<p>[Placeholder] Fitur edit profil akan diimplementasi di fase berikutnya.</p>";
        echo "<a href='" . base_url('nasabah/dashboard') . "'>Kembali ke Dashboard</a>";
    }

    /**
     * Redirect index ke dashboard
     */
    public function index(): void
    {
        $this->dashboard();
    }
}
