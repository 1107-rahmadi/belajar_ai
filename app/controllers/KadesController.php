<?php
/**
 * Kades Controller
 *
 * Controller untuk fitur Kepala Desa
 * Compatible dengan PHP 7.3+
 */

class KadesController
{
    /** @var TransaksiBeli */
    private $transaksiBeli;

    /** @var TransaksiJual */
    private $transaksiJual;

    /** @var Nasabah */
    private $nasabahModel;

    /** @var BankSampah */
    private $bankSampahModel;

    /** @var int|null */
    private $desaId;

    public function __construct()
    {
        requireLogin();
        requireRole(['KADES'], 'dashboard');

        require_once MODEL_PATH . '/TransaksiBeli.php';
        require_once MODEL_PATH . '/TransaksiJual.php';
        require_once MODEL_PATH . '/Nasabah.php';
        require_once MODEL_PATH . '/BankSampah.php';
        require_once MODEL_PATH . '/Desa.php';

        $this->transaksiBeli = new TransaksiBeli();
        $this->transaksiJual = new TransaksiJual();
        $this->nasabahModel = new Nasabah();
        $this->bankSampahModel = new BankSampah();

        $this->desaId = $_SESSION['user_desa_id'] ?? null;
    }

    public function index(): void
    {
        $this->dashboard();
    }

    /**
     * Dashboard Kepala Desa
     */
    public function dashboard(): void
    {
        // Ambil semua bank sampah di desa ini
        $banks = $this->bankSampahModel->getAll($this->desaId);
        $bankIds = array_column($banks, 'id');

        // Statistik agregat
        $stats = [
            'total_bank' => count($banks),
            'total_nasabah' => 0,
            'total_transaksi_beli' => 0,
            'total_transaksi_jual' => 0,
            'total_nilai_beli' => 0,
            'total_nilai_jual' => 0,
        ];

        // Hitung statistik per bank
        $bankStats = [];
        foreach ($banks as $bank) {
            $statBeli = $this->transaksiBeli->getStatistik($bank['id']);
            $statJual = $this->transaksiJual->getStatistik($bank['id']);

            $bankStats[$bank['id']] = [
                'bank' => $bank,
                'transaksi_beli' => $statBeli['total_transaksi'],
                'transaksi_jual' => $statJual['total_transaksi'],
                'nilai_beli' => $statBeli['total_nilai'],
                'nilai_jual' => $statJual['total_nilai'],
            ];

            $stats['total_transaksi_beli'] += $statBeli['total_transaksi'];
            $stats['total_transaksi_jual'] += $statJual['total_transaksi'];
            $stats['total_nilai_beli'] += $statBeli['total_nilai'];
            $stats['total_nilai_jual'] += $statJual['total_nilai'];
        }

        $user = currentUser();
        $flash = getFlashMessage();

        include VIEW_PATH . '/kades/dashboard.php';
    }

    /**
     * Monitoring Kegiatan
     */
    public function monitoring(): void
    {
        $banks = $this->bankSampahModel->getAll($this->desaId);

        // Ambil transaksi terbaru dari semua bank
        $recentTrans = [];
        foreach ($banks as $bank) {
            $beli = $this->transaksiBeli->getByBankSampah($bank['id'], 5);
            foreach ($beli as $b) {
                $b['jenis'] = 'BELI';
                $recentTrans[] = $b;
            }
            $jual = $this->transaksiJual->getByBankSampah($bank['id'], 3);
            foreach ($jual as $j) {
                $j['jenis'] = 'JUAL';
                $recentTrans[] = $j;
            }
        }

        // Sort berdasarkan tanggal
        usort($recentTrans, function($a, $b) {
            return strtotime($b['tanggal']) - strtotime($a['tanggal']);
        });
        $recentTrans = array_slice($recentTrans, 0, 20);

        $user = currentUser();
        $flash = getFlashMessage();

        include VIEW_PATH . '/kades/monitoring.php';
    }

    /**
     * Laporan Keuangan
     */
    public function laporan(): void
    {
        $tanggalFrom = $_GET['tanggal_from'] ?? date('Y-m-01');
        $tanggalTo = $_GET['tanggal_to'] ?? date('Y-m-d');
        $bankId = isset($_GET['bank_id']) ? (int) $_GET['bank_id'] : null;

        $banks = $this->bankSampahModel->getAll($this->desaId);

        // Ambil bank yang dipilih atau semua
        if ($bankId) {
            $selectedBanks = array_filter($banks, function($b) use ($bankId) {
                return $b['id'] == $bankId;
            });
        } else {
            $selectedBanks = $banks;
        }

        $stats = [
            'total_transaksi_beli' => 0,
            'total_berat_beli' => 0,
            'total_nilai_beli' => 0,
            'total_tabungan' => 0,
            'total_tunai' => 0,
            'total_transaksi_jual' => 0,
            'total_berat_jual' => 0,
            'total_nilai_jual' => 0,
            'total_piutang' => 0,
            'keuntungan' => 0,
        ];

        foreach ($selectedBanks as $bank) {
            $beli = $this->transaksiBeli->getStatistik($bank['id'], $tanggalFrom, $tanggalTo);
            $jual = $this->transaksiJual->getStatistik($bank['id']);

            $stats['total_transaksi_beli'] += $beli['total_transaksi'];
            $stats['total_berat_beli'] += $beli['total_berat'];
            $stats['total_nilai_beli'] += $beli['total_nilai'];
            $stats['total_tabungan'] += $beli['total_tabungan'];
            $stats['total_tunai'] += $beli['total_tunai'];
            $stats['total_transaksi_jual'] += $jual['total_transaksi'];
            $stats['total_berat_jual'] += $jual['total_berat'];
            $stats['total_nilai_jual'] += $jual['total_nilai'];
            $stats['total_piutang'] += $jual['total_belum_bayar'];
        }

        $stats['keuntungan'] = $stats['total_nilai_jual'] - $stats['total_nilai_beli'];

        $user = currentUser();
        $flash = getFlashMessage();

        include VIEW_PATH . '/kades/laporan.php';
    }

    /**
     * Export Laporan (Placeholder)
     */
    public function export(): void
    {
        $format = $_GET['format'] ?? 'pdf';

        // Placeholder untuk export
        setFlashMessage('info', 'Fitur export ' . strtoupper($format) . ' sedang dalam pengembangan.');
        redirect('kades/laporan');
    }
}
