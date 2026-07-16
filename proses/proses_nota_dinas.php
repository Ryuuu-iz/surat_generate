<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // jangan tampilkan warning ke output file yang sedang di-stream

// =====================================================================
// KONFIGURASI
// =====================================================================
define('TEMPLATE_DIR', __DIR__ . '/../templates/');
define('TEMPLATE_FILE', TEMPLATE_DIR . 'surat_dinas.rtf');
define('TEMP_DIR', __DIR__ . '/temp/'); // pastikan folder ini writable oleh web server

if (!is_dir(TEMP_DIR)) {
    @mkdir(TEMP_DIR, 0777, true);
}

// =====================================================================
// 1. AMBIL & BERSIHKAN INPUT DARI FORM
// =====================================================================
function ambil($key) {
    return isset($_POST[$key]) ? $_POST[$key] : '';
}

$data = [
    'nomor_surat' => ambil('nomor_surat'),
    'kepada'      => ambil('kepada'),
    'dari'        => ambil('dari'),
    'hal'         => ambil('hal'),
    'rujukan'     => ambil('rujukan'),     // HTML dari CKEditor
    'isi'         => ambil('isi'),         // HTML dari CKEditor
    'jabatan'     => ambil('jabatan'),
    'nama'        => ambil('nama'),
    'pangkat_nrp' => ambil('pangkat_nrp'),
];

$format_output = ambil('format_output') === 'pdf' ? 'pdf' : 'rtf';

if (trim($data['nomor_surat']) === '') {
    http_response_code(400);
    die('Nomor surat wajib diisi.');
}

// =====================================================================
// 2. FUNGSI KONVERSI TEKS -> RTF
// =====================================================================

/**
 * Meng-escape sebuah string PLAIN TEXT (bukan HTML) agar aman disisipkan
 * ke dalam RTF: backslash & kurung kurawal di-escape, karakter non-ASCII
 * (misalnya é, ü, "smart quotes") diubah menjadi \uNNNN? sesuai spesifikasi
 * RTF Unicode agar tidak korup ketika dibuka di Word.
 */
function escapeRtfPlainText($text) {
    // Escape karakter spesial RTF terlebih dahulu
    $text = str_replace(['\\', '{', '}'], ['\\\\', '\\{', '\\}'], $text);

    // Proses per-karakter (multi-byte aware) untuk unicode escaping
    $result = '';
    $length = mb_strlen($text, 'UTF-8');
    for ($i = 0; $i < $length; $i++) {
        $char = mb_substr($text, $i, 1, 'UTF-8');
        $code = mb_ord($char, 'UTF-8');

        if ($code < 128) {
            $result .= $char;
        } else {
            // RTF unicode escape: \uN? (N = signed 16-bit decimal code point)
            $signed = $code > 32767 ? $code - 65536 : $code;
            $result .= '\\u' . $signed . '?';
        }
    }
    return $result;
}

/**
 * htmlToRtf()
 * -----------------------------------------------------------------
 * Menerjemahkan HTML bawaan CKEditor 5 (<p>, <strong>, <em>, <u>,
 * <ul><li>, <ol><li>, <br>) menjadi kode sintaks RTF asli, sehingga
 * format bold/italic/underline/list yang diketik user tetap terjaga
 * saat dibuka di Microsoft Word.
 * -----------------------------------------------------------------
 */
function htmlToRtf($html) {
    if (trim($html) === '') {
        return '';
    }

    $html = str_replace(["\r\n", "\r", "\n"], '', $html); // rapikan whitespace sumber

    // -----------------------------------------------------------------
    // 2.1. Proses daftar bernomor <ol><li>...</li></ol> -> "1.\tab teks\par"
    // -----------------------------------------------------------------
    $html = preg_replace_callback('/<ol[^>]*>(.*?)<\/ol>/is', function ($m) {
        $itemsHtml = $m[1];
        $counter = 1;
        $itemsHtml = preg_replace_callback('/<li[^>]*>(.*?)<\/li>/is', function ($li) use (&$counter) {
            $marker = "\x01NUM:" . $counter . "\x02";
            $counter++;
            return $marker . $li[1] . "\x03LIEND\x02";
        }, $itemsHtml);
        return $itemsHtml;
    }, $html);

    // -----------------------------------------------------------------
    // 2.2. Proses daftar berpoin <ul><li>...</li></ul> -> "\bullet\tab teks\par"
    // -----------------------------------------------------------------
    $html = preg_replace_callback('/<ul[^>]*>(.*?)<\/ul>/is', function ($m) {
        $itemsHtml = $m[1];
        $itemsHtml = preg_replace(
            '/<li[^>]*>(.*?)<\/li>/is',
            "\x01BULLET\x02$1\x03LIEND\x02",
            $itemsHtml
        );
        return $itemsHtml;
    }, $html);

    // -----------------------------------------------------------------
    // 2.3. Tag inline formatting -> token unik (aman dari escaping nanti)
    // -----------------------------------------------------------------
    $map = [
        '/<strong>/i' => "\x01B_ON\x02",
        '/<\/strong>/i' => "\x01B_OFF\x02",
        '/<b>/i' => "\x01B_ON\x02",
        '/<\/b>/i' => "\x01B_OFF\x02",
        '/<em>/i' => "\x01I_ON\x02",
        '/<\/em>/i' => "\x01I_OFF\x02",
        '/<i>/i' => "\x01I_ON\x02",
        '/<\/i>/i' => "\x01I_OFF\x02",
        '/<u>/i' => "\x01U_ON\x02",
        '/<\/u>/i' => "\x01U_OFF\x02",
        '/<br\s*\/?>/i' => "\x01LINEBREAK\x02",
        '/<\/p>/i' => "\x01PAREND\x02",
        '/<p[^>]*>/i' => '', // pembuka <p> tidak perlu marker, hanya penutup yang jadi \par
    ];
    $html = preg_replace(array_keys($map), array_values($map), $html);

    // -----------------------------------------------------------------
    // 2.4. Buang sisa tag HTML yang tidak dikenali (mis. <span>, <a>, dll)
    // -----------------------------------------------------------------
    $html = strip_tags($html);

    // -----------------------------------------------------------------
    // 2.5. Decode HTML entity (&nbsp;, &amp;, &lt;, dst) menjadi karakter asli
    // -----------------------------------------------------------------
    $html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');

    // -----------------------------------------------------------------
    // 2.6. Escape teks polos (di luar token) menjadi RTF-safe.
    //      Trik: pecah berdasarkan token \x01...\x02 / \x03LIEND\x02,
    //      lalu escape hanya bagian teksnya, token dibiarkan utuh.
    // -----------------------------------------------------------------
    $parts = preg_split('/(\x01[A-Z_:0-9]+\x02|\x03LIEND\x02)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
    $rtf = '';
    foreach ($parts as $part) {
        if (preg_match('/^\x01([A-Z_]+)(?::(\d+))?\x02$/', $part, $mm)) {
            switch ($mm[1]) {
                case 'B_ON':       $rtf .= '\\b ';      break;
                case 'B_OFF':      $rtf .= '\\b0 ';     break;
                case 'I_ON':       $rtf .= '\\i ';      break;
                case 'I_OFF':      $rtf .= '\\i0 ';     break;
                case 'U_ON':       $rtf .= '\\ul ';     break;
                case 'U_OFF':      $rtf .= '\\ulnone '; break;
                case 'LINEBREAK':  $rtf .= '\\line ';   break;
                case 'PAREND':     $rtf .= '\\par ';    break;
                case 'BULLET':     $rtf .= '\\bullet \\tab '; break;
                case 'NUM':        $rtf .= $mm[2] . '.\\tab '; break;
                default: break;
            }
        } elseif ($part === "\x03LIEND\x02") {
            $rtf .= '\\par ';
        } else {
            $rtf .= escapeRtfPlainText($part);
        }
    }

    // Rapikan: hilangkan satu \par berlebih di akhir (template sudah
    // menyediakan \par penutup setelah placeholder)
    $rtf = preg_replace('/\\\\par\s*$/', '', trim($rtf));

    return trim($rtf);
}

// =====================================================================
// 3. SIAPKAN NILAI RTF UNTUK SETIAP PLACEHOLDER
// =====================================================================
$rtfValues = [
    'nomor_surat' => escapeRtfPlainText($data['nomor_surat']),
    'kepada'      => escapeRtfPlainText($data['kepada']),
    'dari'        => escapeRtfPlainText($data['dari']),
    'hal'         => escapeRtfPlainText($data['hal']),
    'rujukan'     => htmlToRtf($data['rujukan']),
    'isi'         => htmlToRtf($data['isi']),
    'jabatan'     => escapeRtfPlainText($data['jabatan']),
    'nama'        => escapeRtfPlainText($data['nama']),
    'pangkat_nrp' => escapeRtfPlainText($data['pangkat_nrp']),
];

// =====================================================================
// 4. BACA TEMPLATE & LAKUKAN REPLACE
// =====================================================================
if (!file_exists(TEMPLATE_FILE)) {
    http_response_code(500);
    die('Template surat_dinas.rtf tidak ditemukan di folder templates/.');
}

$template = file_get_contents(TEMPLATE_FILE);

// PENTING: Word menyimpan RTF dengan word-wrap pada baris-baris panjang.
// Placeholder seperti ${nomor_surat} bisa "terpotong" oleh newline mentah
// (mis. "$\{\nnomor_surat\}"). Newline mentah TIDAK signifikan dalam RTF
// (aman dihapus), jadi kita normalisasi dulu agar placeholder utuh
// sebelum proses str_replace.
$template = str_replace(["\r\n", "\r", "\n"], '', $template);

// Di dalam RTF, karakter { dan } selalu ter-escape menjadi \{ dan \}.
// Maka placeholder ${nama} tersimpan secara harfiah sebagai: $\{nama\}
$searchReplace = [];
foreach ($rtfValues as $key => $value) {
    $searchReplace['$\\{' . $key . '\\}'] = $value;
}

$templateFinal = str_replace(array_keys($searchReplace), array_values($searchReplace), $template);

// -----------------------------------------------------------------
// 4.1. Fallback berbasis regex.
//      Kadang Word menyisipkan kode RTF pemformatan (mis. \hich\af1
//      \dbch\af13\loch\f1) DI DALAM tanda kurung placeholder, sehingga
//      teksnya menjadi "$\{\hich\af1\dbch\af13\loch\f1 nomor_surat\}"
//      dan str_replace() literal di atas tidak menemukannya. Regex ini
//      menoleransi kode RTF semacam itu di antara "$\{" dan nama key.
// -----------------------------------------------------------------
foreach ($rtfValues as $key => $value) {
    $pattern = '/\$\\\\\{(?:\\\\[a-zA-Z]+-?\d*[ ]?)*' . preg_quote($key, '/') . '\\\\\}/';
    $templateFinal = preg_replace_callback($pattern, function ($m) use ($value) {
        return $value;
    }, $templateFinal, 1);
}

// =====================================================================
// 5. TENTUKAN NAMA FILE OUTPUT
// =====================================================================
$namaFileDasar = 'Nota_Dinas_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $data['nomor_surat'] ?: date('Ymd_His'));

// =====================================================================
// 6. CABANG FORMAT OUTPUT: RTF vs PDF
// =====================================================================
if ($format_output === 'rtf') {

    kirimFileRtfLangsung($templateFinal, $namaFileDasar);

} else {

    kirimFilePdfViaWordCOM($templateFinal, $namaFileDasar);

}

// =====================================================================
// FUNGSI: kirim file RTF langsung ke browser (tanpa disimpan permanen)
// =====================================================================
function kirimFileRtfLangsung($rtfContent, $namaFileDasar) {
    $filename = $namaFileDasar . '.rtf';

    header('Content-Type: application/rtf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($rtfContent));
    echo $rtfContent;
    exit;
}

// =====================================================================
// FUNGSI: konversi RTF -> PDF via LibreOffice headless (soffice/libreoffice).
// Cocok untuk server Linux/Mac (kebanyakan hosting & cPanel) yang tidak
// punya MS Word/COM. Membutuhkan fungsi exec() tidak di-disable dan
// binary "soffice" atau "libreoffice" tersedia di PATH server.
// =====================================================================
function konversiRtfKePdfLibreOffice($tempRtfPath, $uniqueId) {
    if (!function_exists('exec') && !function_exists('shell_exec')) {
        return false; // exec di-disable oleh hosting (pengaturan php.ini)
    }

    // 1. Cari lokasi binary LibreOffice di server
    $kandidatBinary = ['soffice', 'libreoffice'];
    $sofficeBin = null;
    foreach ($kandidatBinary as $bin) {
        $lokasi = @shell_exec('command -v ' . escapeshellarg($bin) . ' 2>/dev/null');
        $lokasi = trim((string) $lokasi);
        if ($lokasi !== '') {
            $sofficeBin = $lokasi;
            break;
        }
    }
    if ($sofficeBin === null) {
        return false; // LibreOffice tidak terpasang di server
    }

    // 2. Jalankan konversi: soffice --headless --convert-to pdf --outdir <temp> <file.rtf>
    $outDir = escapeshellarg(rtrim(TEMP_DIR, '/'));
    $rtfArg = escapeshellarg($tempRtfPath);
    // --env:UserInstallation dipakai agar tiap request punya profil sendiri
    // (mencegah bentrok bila banyak permintaan berjalan bersamaan)
    $userProfileDir = TEMP_DIR . 'lo_profile_' . $uniqueId;
    $envArg = escapeshellarg('-env:UserInstallation=file://' . $userProfileDir);

    $cmd = escapeshellcmd($sofficeBin)
        . ' --headless --nologo --nofirststartwizard ' . $envArg
        . ' --convert-to pdf --outdir ' . $outDir . ' ' . $rtfArg . ' 2>&1';

    @exec($cmd, $outputLog, $returnCode);

    // Hapus folder profil sementara LibreOffice
    if (is_dir($userProfileDir)) {
        @exec('rm -rf ' . escapeshellarg($userProfileDir));
    }

    // 3. Nama file hasil konversi = nama file RTF asal, ekstensi diganti .pdf
    $expectedPdfPath = TEMP_DIR . pathinfo($tempRtfPath, PATHINFO_FILENAME) . '.pdf';

    return file_exists($expectedPdfPath) ? $expectedPdfPath : false;
}

// =====================================================================
// FUNGSI: konversi ke PDF asli. Mencoba beberapa metode berurutan:
//   a) COM Word.Application (hanya tersedia di Windows + MS Word terpasang)
//   b) LibreOffice headless (umum tersedia di server Linux)
//   c) Fallback: kirim RTF apabila kedua metode di atas gagal/tidak ada
// =====================================================================
function kirimFilePdfViaWordCOM($rtfContent, $namaFileDasar) {
    $uniqueId    = uniqid('surat_', true);
    $tempRtfPath = TEMP_DIR . $uniqueId . '.rtf';
    $tempPdfPath = TEMP_DIR . $uniqueId . '.pdf';

    // 6.1. Tulis file RTF sementara ke server
    file_put_contents($tempRtfPath, $rtfContent);

    $conversionSukses = false;

    // 6.2. METODE A: COM Word.Application (hanya ada di PHP untuk Windows)
    if (class_exists('COM')) {
        $word = null;
        $doc  = null;
        try {
            // Path Windows wajib absolute & pakai backslash
            $absoluteRtfPath = str_replace('/', '\\', realpath($tempRtfPath));
            $absolutePdfPath = str_replace('/', '\\', dirname(realpath($tempRtfPath))) . '\\' . $uniqueId . '.pdf';

            $word = new COM('Word.Application');
            if (!$word) {
                throw new Exception('Tidak dapat menginisialisasi Word.Application.');
            }
            $word->Visible = false;
            $word->DisplayAlerts = 0; // wdAlertsNone

            $doc = $word->Documents->Open($absoluteRtfPath);

            // wdFormatPDF = 17
            $doc->SaveAs($absolutePdfPath, 17);

            $doc->Close(false);
            $word->Quit();

            $doc  = null;
            $word = null;

            if (file_exists($tempPdfPath)) {
                $conversionSukses = true;
            }
        } catch (Throwable $e) {
            // Bersihkan proses Word yang mungkin masih menggantung
            try {
                if ($doc)  { $doc->Close(false); }
                if ($word) { $word->Quit(); }
            } catch (Throwable $ignored) {
                // abaikan, memang sedang proses pembersihan setelah error
            }
            $doc  = null;
            $word = null;
            $conversionSukses = false;

            error_log('Gagal konversi PDF via COM Word: ' . $e->getMessage());
        }
    }

    // 6.3. METODE B: LibreOffice headless — dicoba jika COM tidak tersedia/gagal
    if (!$conversionSukses) {
        $hasilLibreOffice = konversiRtfKePdfLibreOffice($tempRtfPath, $uniqueId);
        if ($hasilLibreOffice !== false) {
            $tempPdfPath = $hasilLibreOffice;
            $conversionSukses = true;
        } else {
            error_log('Gagal konversi PDF via LibreOffice (binary tidak ditemukan atau exec di-disable).');
        }
    }

    // =================================================================
    // 6.4. Jika konversi PDF berhasil (dari metode manapun) -> kirim PDF
    // =================================================================
    if ($conversionSukses && file_exists($tempPdfPath)) {
        $pdfContent = file_get_contents($tempPdfPath);

        // Bersihkan file sementara di server
        @unlink($tempRtfPath);
        @unlink($tempPdfPath);

        $filename = $namaFileDasar . '.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdfContent));
        echo $pdfContent;
        exit;
    }

    // =================================================================
    // 6.4. FALLBACK: COM & LibreOffice gagal / tidak tersedia -> kirim RTF
    // =================================================================
    @unlink($tempRtfPath);
    @unlink($tempPdfPath);

    $filename = $namaFileDasar . '.rtf';
    header('Content-Type: application/rtf');
    header('X-Konversi-PDF: gagal-fallback-rtf'); // penanda untuk debugging/log
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($rtfContent));
    echo $rtfContent;
    exit;
}