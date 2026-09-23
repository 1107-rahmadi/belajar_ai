<?php
/**
 * Simple PDF Generator for Bank Sampah Reports
 *
 * Lightweight PDF generator without external dependencies
 */

class PdfGenerator
{
    private $pdf;
    private $pageWidth = 210; // A4 width in mm
    private $pageHeight = 297; // A4 height in mm
    private $margin = 15;
    private $headerHeight = 25;
    private $footerHeight = 15;
    private $currentY = 0;

    public function __construct()
    {
        $this->pdf = '';
        $this->pdf .= "%PDF-1.4\n";
        $this->pdf .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
    }

    /**
     * Add a new page
     */
    public function addPage()
    {
        $this->pdf .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $this->pdf .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 " . $this->pageWidth . " " . $this->pageHeight . "] >>\nendobj\n";
    }

    /**
     * Add content to PDF (simplified approach)
     */
    public function addContent($title, $subtitle, $data, $period)
    {
        // Build content object
        $content = $this->buildContent($title, $subtitle, $data, $period);
        $this->pdf .= "4 0 obj\n<< /Length " . strlen($content) . " >>\nstream\n" . $content . "\nendstream\nendobj\n";

        // Update page object with content
        $this->pdf .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 " . $this->pageWidth . " " . $this->pageHeight . "] /Contents 4 0 R >>\nendobj\n";
    }

    private function buildContent($title, $subtitle, $data, $period)
    {
        $stream = "BT\n";

        // Header
        $stream .= "/F1 16 Tf 85 280 Td (" . $this->escape($title) . ") Tj\n";
        $stream .= "/F2 10 Tf 0 -15 Td (" . $this->escape($subtitle) . ") Tj\n";
        $stream .= "/F2 9 Tf 0 -12 Td (Periode: " . $this->escape($period) . ") Tj\n";
        $stream .= "/F2 9 Tf 0 -10 Td (Dicetak: " . date('d/m/Y H:i') . ") Tj\n";

        // Line
        $stream .= "0 -5 m 195 0 m S\n";

        // Data section
        $y = 235;

        // Transaksi Beli
        $stream .= "/F1 12 Tf 15 " . $y . " Td (A. Transaksi Beli) Tj\n";
        $y -= 12;
        $stream .= "/F2 9 Tf 20 " . $y . " Td (Total Transaksi: " . $this->numberFormat($data['beli']['transaksi']) . ") Tj\n";
        $y -= 8;
        $stream .= "/F2 9 Tf 20 " . $y . " Td (Total Berat: " . number_format($data['beli']['berat'], 1) . " kg) Tj\n";
        $y -= 8;
        $stream .= "/F2 9 Tf 20 " . $y . " Td (Total Nilai Beli: Rp " . $this->numberFormat($data['beli']['nilai']) . ") Tj\n";
        $y -= 8;
        $stream .= "/F2 9 Tf 20 " . $y . " Td (Ke Tabungan: Rp " . $this->numberFormat($data['beli']['tabungan']) . ") Tj\n";
        $y -= 8;
        $stream .= "/F2 9 Tf 20 " . $y . " Td (Ke Tunai: Rp " . $this->numberFormat($data['beli']['tunai']) . ") Tj\n";

        // Transaksi Jual
        $y -= 15;
        $stream .= "/F1 12 Tf 15 " . $y . " Td (B. Transaksi Jual) Tj\n";
        $y -= 12;
        $stream .= "/F2 9 Tf 20 " . $y . " Td (Total Transaksi: " . $this->numberFormat($data['jual']['transaksi']) . ") Tj\n";
        $y -= 8;
        $stream .= "/F2 9 Tf 20 " . $y . " Td (Total Berat: " . number_format($data['jual']['berat'], 1) . " kg) Tj\n";
        $y -= 8;
        $stream .= "/F2 9 Tf 20 " . $y . " Td (Total Penjualan: Rp " . $this->numberFormat($data['jual']['nilai']) . ") Tj\n";
        $y -= 8;
        $stream .= "/F2 9 Tf 20 " . $y . " Td (Piutang: Rp " . $this->numberFormat($data['jual']['piutang']) . ") Tj\n";

        // Pencairan
        if (isset($data['pencairan'])) {
            $y -= 15;
            $stream .= "/F1 12 Tf 15 " . $y . " Td (C. Pengajuan Pencairan) Tj\n";
            $y -= 12;
            $stream .= "/F2 9 Tf 20 " . $y . " Td (Menunggu: " . $this->numberFormat($data['pencairan']['menunggu']) . " pengajuan) Tj\n";
            $y -= 8;
            $stream .= "/F2 9 Tf 20 " . $y . " Td (Disetujui: " . $this->numberFormat($data['pencairan']['disetujui']) . " pengajuan) Tj\n";
            $y -= 8;
            $stream .= "/F2 9 Tf 20 " . $y . " Td (Ditolak: " . $this->numberFormat($data['pencairan']['ditolak']) . " pengajuan) Tj\n";
            $y -= 8;
            $stream .= "/F2 9 Tf 20 " . $y . " Td (Dicairkan: " . $this->numberFormat($data['pencairan']['dicairkan']) . " pengajuan) Tj\n";
        }

        // Footer
        $stream .= "ET\n";

        return $stream;
    }

    private function escape($text)
    {
        return str_replace(['\\', '(', ')', "\n", "\r"], ['\\\\', '\\(', '\\)', ' ', ' '], $text);
    }

    private function numberFormat($number)
    {
        return number_format((float) $number, 0, ',', '.');
    }

    /**
     * Output the PDF
     */
    public function output($filename = 'laporan.pdf')
    {
        // Add font resources
        $this->pdf .= "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
        $this->pdf .= "6 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>\nendobj\n";

        // Add resources
        $this->pdf .= "7 0 obj\n<< /Font << /F1 5 0 R /F2 6 0 R >> >>\nendobj\n";

        // Update page with resources
        $this->pdf = str_replace(
            "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 " . $this->pageWidth . " " . $this->pageHeight . "] /Contents 4 0 R >>",
            "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 " . $this->pageWidth . " " . $this->pageHeight . "] /Contents 4 0 R /Resources 7 0 R >>",
            $this->pdf
        );

        // Output
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache');
        echo $this->pdf;
    }
}
