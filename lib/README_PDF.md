# PDF Export - TCPDF Installation

## Quick Install

Run the installer script:

```bash
cd d:/xampp/htdocs/belajar_ai
php lib/install_tcpdf.php
```

## Manual Install

If automatic download fails, install TCPDF manually:

1. **Download TCPDF** from:
   - https://github.com/tecnickcom/TCPDF/releases
   - Download version 6.7.5 (or latest stable)

2. **Extract the zip file**

3. **Copy contents** to `lib/tcpdf/`:
   ```
   lib/
   └── tcpdf/
       ├── tcpdf.php          ← main file
       ├── tcpdf_barcodes_1d.php
       ├── tcpdf_barcodes_2d.php
       ├── tcpdf_import.php
       ├── tcpdf_parser.php
       ├── config/
       │   └── tcpdf_config.php
       ├── include/
       │   └── ...
       └── fonts/
           └── ...
   ```

4. **Verify installation** - you should see:
   ```
   lib/tcpdf/tcpdf.php
   lib/tcpdf/config/tcpdf_config.php
   ```

## Testing

After installation, visit:
- Laporan page → Click "Export PDF" button
- Pencairan page → Click "Export PDF" button

## Troubleshooting

### "TCPDF not found" error
- Make sure `lib/tcpdf/tcpdf.php` exists
- Run `php lib/install_tcpdf.php` again

### PDF not downloading
- Check PHP has write permissions
- Ensure no output before PDF generation
