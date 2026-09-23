<?php
/**
 * TCPDF Installer Script
 *
 * Download and extract TCPDF library
 * Run: php install_tcpdf.php
 */

// TCPDF download URL (using a stable release)
define('TCPDF_VERSION', '6.7.5');
define('TCPDF_URL', 'https://github.com/tecnickcom/TCPDF/archive/refs/tags/' . TCPDF_VERSION . '.zip');
define('TCPDF_DIR', __DIR__);

echo "TCPDF Installer for Bank Sampah Digital\n";
echo "========================================\n\n";

$tcpdfDir = TCPDF_DIR . '/tcpdf';

if (is_dir($tcpdfDir)) {
    echo "[OK] TCPDF directory already exists.\n";

    // Check if TCPDF is properly installed
    if (file_exists($tcpdfDir . '/tcpdf.php')) {
        echo "[OK] TCPDF is already installed correctly.\n";
        echo "\nYou can now use PDF export features!\n";
        exit(0);
    } else {
        echo "[WARN] TCPDF directory exists but seems incomplete.\n";
        echo "      Will attempt to reinstall...\n";
    }
}

echo "Downloading TCPDF v" . TCPDF_VERSION . "...\n";

$zipFile = TCPDF_DIR . '/tcpdf.zip';
$tempDir = TCPDF_DIR . '/tcpdf_temp';

// Clean up old files
if (is_dir($tempDir)) {
    recursiveDelete($tempDir);
}
if (file_exists($zipFile)) {
    unlink($zipFile);
}

// Download TCPDF
$context = stream_context_create([
    'http' => [
        'timeout' => 120,
        'header' => "User-Agent: Bank Sampah Installer/1.0\r\n"
    ]
]);

$downloadStarted = false;
$retryCount = 0;
$maxRetries = 3;

while (!$downloadStarted && $retryCount < $maxRetries) {
    echo "Attempt " . ($retryCount + 1) . "... ";

    $content = @file_get_contents(TCPDF_URL, false, $context);

    if ($content !== false && strlen($content) > 1000) {
        file_put_contents($zipFile, $content);
        echo "Downloaded " . round(strlen($content) / 1024 / 1024, 2) . " MB\n";
        $downloadStarted = true;
    } else {
        echo "Failed (trying again)...\n";
        $retryCount++;
        sleep(2);
    }
}

if (!$downloadStarted) {
    echo "\n[ERROR] Failed to download TCPDF automatically.\n\n";
    echo "Please download manually:\n";
    echo "1. Visit: https://github.com/tecnickcom/TCPDF/releases\n";
    echo "2. Download TCPDF version " . TCPDF_VERSION . " (Source code zip)\n";
    echo "3. Extract the zip file\n";
    echo "4. Copy the 'TCPDF' folder contents to: " . TCPDF_DIR . "/tcpdf/\n";
    echo "\nMake sure the file structure is:\n";
    echo "  lib/tcpdf/tcpdf.php\n";
    echo "  lib/tcpdf/config/tcpdf_config.php\n";
    echo "  etc...\n";
    exit(1);
}

// Extract zip
echo "Extracting...";

$zip = new ZipArchive();
if ($zip->open($zipFile) === true) {
    $zip->extractTo($tempDir);
    $zip->close();

    // Move TCPDF folder
    $sourceDir = $tempDir . '/TCPDF-' . TCPDF_VERSION;
    if (is_dir($sourceDir)) {
        rename($sourceDir, $tcpdfDir);
    }

    echo " Done!\n";

    // Clean up
    recursiveDelete($tempDir);
    unlink($zipFile);

    // Verify
    if (file_exists($tcpdfDir . '/tcpdf.php')) {
        echo "\n[SUCCESS] TCPDF installed successfully!\n";
        echo "Location: " . $tcpdfDir . "\n";
        echo "\nYou can now use PDF export features!\n";
    } else {
        echo "\n[ERROR] Installation completed but tcpdf.php not found.\n";
        exit(1);
    }
} else {
    echo " Failed to extract!\n";
    exit(1);
}

function recursiveDelete($dir)
{
    if (!is_dir($dir)) {
        return;
    }

    $files = array_diff(scandir($dir), ['.', '..']);

    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? recursiveDelete($path) : unlink($path);
    }

    rmdir($dir);
}
