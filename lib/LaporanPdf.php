<?php
/**
 * PDF Export Helper for Bank Sampah Reports
 *
 * Uses TCPDF library for professional PDF output
 * Download TCPDF from: https://github.com/tecnickcom/TCPDF
 * Extract to: lib/tcpdf/
 */

$tcpdfPath = __DIR__ . '/tcpdf/tcpdf.php';

if (!file_exists($tcpdfPath)) {
    die('TCPDF not found. Please run: php lib/install_tcpdf.php');
}

require_once $tcpdfPath;

class LaporanPdf
{
    private $pdf;
    private $appName;
    private $bankName;

    public function __construct()
    {
        // Create PDF document
        $this->pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Document info
        $this->pdf->SetCreator('Bank Sampah Digital');
        $this->pdf->SetAuthor(APP_NAME);
        $this->pdf->SetTitle('Laporan Operasional Bank Sampah');
        $this->pdf->SetSubject('Laporan Operasional');

        // Set margins
        $this->pdf->SetMargins(15, 20, 15);
        $this->pdf->SetHeaderMargin(5);
        $this->pdf->SetFooterMargin(10);

        // Set auto page breaks
        $this->pdf->SetAutoPageBreak(true, 25);

        // Set default font
        $this->pdf->SetFont('helvetica', '', 10);

        $this->appName = defined('APP_NAME') ? APP_NAME : 'Bank Sampah Digital';
    }

    /**
     * Generate Laporan Operasional PDF
     */
    public function generateLaporanOperasional($statsBeli, $statsJual, $tanggalFrom, $tanggalTo, $bankName = '')
    {
        $this->bankName = $bankName;

        // Add page
        $this->pdf->AddPage();

        // Header
        $this->renderHeader('LAPORAN OPERASIONAL', $bankName);

        // Period info
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->Cell(0, 6, 'Periode: ' . date('d/m/Y', strtotime($tanggalFrom)) . ' - ' . date('d/m/Y', strtotime($tanggalTo)), 0, 1, 'L');
        $this->pdf->Cell(0, 6, 'Dicetak: ' . date('d/m/Y H:i'), 0, 1, 'L');
        $this->pdf->Ln(5);

        // A. Transaksi Beli
        $this->renderSectionHeader('A. TRANSAKSI BELI');
        $this->renderStatsBeli($statsBeli);
        $this->pdf->Ln(8);

        // B. Transaksi Jual
        $this->renderSectionHeader('B. TRANSAKSI JUAL');
        $this->renderStatsJual($statsJual);
        $this->pdf->Ln(8);

        // Footer signature
        $this->renderFooter();

        // Output
        return $this->pdf->Output('laporan_operasional_' . date('Ymd') . '.pdf', 'D');
    }

    /**
     * Generate Laporan Pencairan PDF
     */
    public function generateLaporanPencairan($pengajuans, $statistik, $bankName = '')
    {
        $this->bankName = $bankName;

        $this->pdf->AddPage();

        $this->renderHeader('LAPORAN PENGajuan PENCAIRAN', $bankName);
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->Cell(0, 6, 'Dicetak: ' . date('d/m/Y H:i'), 0, 1, 'L');
        $this->pdf->Ln(5);

        // Summary
        $this->renderSectionHeader('RINGKASAN');
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell(60, 7, 'Menunggu Persetujuan:', 1, 0, 'L');
        $this->pdf->Cell(0, 7, ($statistik['DIAJUKAN']['jumlah'] ?? 0) . ' pengajuan', 1, 1, 'L');
        $this->pdf->Cell(60, 7, 'Disetujui:', 1, 0, 'L');
        $this->pdf->Cell(0, 7, ($statistik['DISETUJUI']['jumlah'] ?? 0) . ' pengajuan', 1, 1, 'L');
        $this->pdf->Cell(60, 7, 'Ditolak:', 1, 0, 'L');
        $this->pdf->Cell(0, 7, ($statistik['DITOLAK']['jumlah'] ?? 0) . ' pengajuan', 1, 1, 'L');
        $this->pdf->Cell(60, 7, 'Dicairkan:', 1, 0, 'L');
        $this->pdf->Cell(0, 7, ($statistik['DICAIRKAN']['jumlah'] ?? 0) . ' pengajuan', 1, 1, 'L');
        $this->pdf->Ln(8);

        // Detail table
        if (!empty($pengajuans)) {
            $this->renderSectionHeader('DETAIL PENGAJUAN');

            // Table header
            $this->pdf->SetFont('helvetica', 'B', 8);
            $this->pdf->SetFillColor(46, 125, 50);
            $this->pdf->SetTextColor(255);
            $this->pdf->Cell(30, 7, 'No. Pengajuan', 1, 0, 'C', true);
            $this->pdf->Cell(45, 7, 'Nasabah', 1, 0, 'C', true);
            $this->pdf->Cell(30, 7, 'Jumlah', 1, 0, 'C', true);
            $this->pdf->Cell(25, 7, 'Metode', 1, 0, 'C', true);
            $this->pdf->Cell(25, 7, 'Status', 1, 0, 'C', true);
            $this->pdf->Cell(30, 7, 'Tanggal', 1, 1, 'C', true);

            // Table rows
            $this->pdf->SetFont('helvetica', '', 8);
            $this->pdf->SetTextColor(0);

            foreach ($pengajuans as $i => $p) {
                $fill = ($i % 2 == 0);
                $this->pdf->SetFillColor(245, 245, 245);

                $this->pdf->Cell(30, 6, $p['no_pengajuan'], 1, 0, 'C', $fill);
                $this->pdf->Cell(45, 6, substr($p['nama_nasabah'] ?? '-', 0, 25), 1, 0, 'L', $fill);
                $this->pdf->Cell(30, 6, 'Rp ' . number_format($p['jumlah'], 0, ',', '.'), 1, 0, 'R', $fill);
                $this->pdf->Cell(25, 6, $p['metode'], 1, 0, 'C', $fill);
                $this->pdf->Cell(25, 6, $p['status'], 1, 0, 'C', $fill);
                $this->pdf->Cell(30, 6, date('d/m/Y', strtotime($p['diajukan_at'])), 1, 1, 'C', $fill);
            }
        }

        $this->renderFooter();

        return $this->pdf->Output('laporan_pencairan_' . date('Ymd') . '.pdf', 'D');
    }

    private function renderHeader($title, $subtitle = '')
    {
        // Title
        $this->pdf->SetFont('helvetica', 'B', 16);
        $this->pdf->SetTextColor(46, 125, 50);
        $this->pdf->Cell(0, 10, $this->appName, 0, 1, 'C');

        $this->pdf->SetFont('helvetica', 'B', 14);
        $this->pdf->SetTextColor(0);
        $this->pdf->Cell(0, 8, $title, 0, 1, 'C');

        if ($subtitle) {
            $this->pdf->SetFont('helvetica', '', 10);
            $this->pdf->Cell(0, 6, $subtitle, 0, 1, 'C');
        }

        // Line
        $this->pdf->SetDrawColor(46, 125, 50);
        $this->pdf->SetLineWidth(0.5);
        $this->pdf->Line(15, $this->pdf->GetY(), 195, $this->pdf->GetY());
        $this->pdf->Ln(5);
    }

    private function renderSectionHeader($text)
    {
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->SetFillColor(232, 245, 233);
        $this->pdf->Cell(0, 8, $text, 0, 1, 'L', true);
        $this->pdf->Ln(2);
    }

    private function renderStatsBeli($stats)
    {
        $this->pdf->SetFont('helvetica', '', 10);

        $cols = [
            ['label' => 'Total Transaksi', 'value' => number_format($stats['total_transaksi'] ?? 0)],
            ['label' => 'Total Berat', 'value' => number_format($stats['total_berat'] ?? 0, 1) . ' kg'],
            ['label' => 'Total Nilai Beli', 'value' => 'Rp ' . number_format($stats['total_nilai'] ?? 0, 0, ',', '.')],
            ['label' => 'Ke Tabungan', 'value' => 'Rp ' . number_format($stats['total_tabungan'] ?? 0, 0, ',', '.')],
            ['label' => 'Ke Tunai', 'value' => 'Rp ' . number_format($stats['total_tunai'] ?? 0, 0, ',', '.')],
        ];

        foreach ($cols as $col) {
            $this->pdf->Cell(50, 6, $col['label'] . ':', 0, 0, 'L');
            $this->pdf->Cell(0, 6, $col['value'], 0, 1, 'L');
        }
    }

    private function renderStatsJual($stats)
    {
        $this->pdf->SetFont('helvetica', '', 10);

        $cols = [
            ['label' => 'Total Transaksi', 'value' => number_format($stats['total_transaksi'] ?? 0)],
            ['label' => 'Total Berat', 'value' => number_format($stats['total_berat'] ?? 0, 1) . ' kg'],
            ['label' => 'Total Penjualan', 'value' => 'Rp ' . number_format($stats['total_nilai'] ?? 0, 0, ',', '.')],
            ['label' => 'Piutang', 'value' => 'Rp ' . number_format($stats['total_belum_bayar'] ?? 0, 0, ',', '.')],
        ];

        foreach ($cols as $col) {
            $this->pdf->Cell(50, 6, $col['label'] . ':', 0, 0, 'L');
            $this->pdf->Cell(0, 6, $col['value'], 0, 1, 'L');
        }
    }

    private function renderFooter()
    {
        $this->pdf->Ln(15);
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->Cell(0, 6, 'Mengetahui,', 0, 1, 'R');
        $this->pdf->Ln(15);
        $this->pdf->Cell(0, 6, '________________________', 0, 1, 'R');
        $this->pdf->Cell(0, 5, 'Pencetak Laporan', 0, 1, 'R');
    }
}
