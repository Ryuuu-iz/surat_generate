<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // jangan tampilkan warning ke output file yang sedang di-stream

// =====================================================================
// KONFIGURASI
// =====================================================================
define('TEMPLATE_DIR', __DIR__ . '/../templates/');
define('TEMPLATE_FILE', TEMPLATE_DIR . 'surat_keluar.rtf');
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
    'nomor'        => ambil('nomor'),
    'tanggal'      => ambil('tanggal'),
    'tempat'       => ambil('tempat'),       // kota tujuan pada blok "Yth. ... di ... [tempat]"
    'klasifikasi'  => ambil('klasifikasi'),
    'lampiran'     => ambil('lampiran'),
    'hal'          => ambil('hal'),
    'kepada'       => ambil('kepada'),
    'jabatan'      => ambil('jabatan'),
    'rujukan'      => ambil('rujukan'),      // HTML dari daftar tambah/hapus rujukan
    'isi'          => ambil('isi'),          // HTML dari CKEditor
    'koordinasi'   => ambil('koordinasi'),   // HTML dari CKEditor
    'nama_ttd'     => ambil('nama_ttd'),
    'pangkat_nrp'  => ambil('pangkat_nrp'),
    'tembusan'     => ambil('tembusan'),     // HTML dari daftar tambah/hapus tembusan
];

$format_output = ambil('format_output') === 'pdf' ? 'pdf' : 'rtf';

if (trim($data['nomor']) === '') {
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
    // 2.2b. <p class="ruj-sp-before">...</p> -> paragraf ini diberi jarak
    //       SEBELUM-nya (RTF \sb) alih-alih paragraf/baris baru terpisah.
    //       Dipakai pada item Rujukan ke-2 dst supaya ada 1x spasi kosong
    //       dari huruf sebelumnya (a. -> b.) TANPA memicu Word menghitung-
    //       nya sebagai item auto-numbering baru (auto-numbering huruf
    //       sudah bawaan dari template Word, \ls1). \sb DITARUH DI AWAL
    //       paragraf (sebelum isi teksnya) karena itu satu-satunya posisi
    //       yang sah untuk kontrol paragraf RTF -- kalau ditaruh di
    //       tengah/akhir paragraf (setelah teksnya), Word bisa salah
    //       menafsirkannya sebagai baris tambahan, yang memicu efek
    //       "jenjang"/stretch dari perataan justify (\qj).
    //       Harus diproses SEBELUM aturan <p> generik di bawah.
    // -----------------------------------------------------------------
    $html = preg_replace_callback('/<p\s+class="ruj-sp-before"[^>]*>(.*?)<\/p>/is', function ($m) {
        return "\x01PARSPACEBEFORE\x02" . $m[1] . "\x01PAREND\x02";
    }, $html);

    // -----------------------------------------------------------------
    // 2.2c. <p class="ruj-sp-tail"></p> -> satu baris kosong sungguhan
    //       SETELAH huruf rujukan yang terakhir (sebelum lanjut ke bagian
    //       "2. Isi"). Tidak perlu \pard\ls1 lagi di sini karena tidak ada
    //       item huruf lain sesudahnya yang perlu dilanjutkan.
    // -----------------------------------------------------------------
    $html = preg_replace('/<p\s+class="ruj-sp-tail"[^>]*>\s*<\/p>/is', "\x01PARTAIL\x02", $html);

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
                // \contextualspace pada style s30 (dipakai paragraf daftar
                // Rujukan) MENYEMBUNYIKAN spasi \sa/\sb antar paragraf yang
                // masih satu list/style -- override kecil per-paragraf saja
                // ternyata tidak cukup mengalahkannya. Solusi yang pasti
                // kelihatan: keluar dulu dari list (\pard biasa, tanpa ls1
                // & tanpa contextualspace) untuk 1 baris KOSONG sungguhan,
                // baru \pard lagi untuk masuk balik ke list (\ls1) demi
                // lanjut ke huruf berikutnya. \pard (bukan \pard\plain)
                // supaya format karakter (font/ukuran) yang sudah aktif
                // tidak ikut ter-reset.
                case 'PARSPACEBEFORE':
                    $rtf .= '\\pard\\intbl\\ql\\li0\\ri0\\sa0\\sb0\\par '
                          . '\\pard\\intbl\\qj\\fi-246\\li246\\ri0\\sa240\\ls1\\contextualspace ';
                    break;
                // Baris kosong sungguhan setelah huruf terakhir (tidak perlu
                // \pard\ls1 lagi karena tidak ada item huruf sesudahnya).
                case 'PARTAIL':
                    $rtf .= '\\pard\\intbl\\ql\\li0\\ri0\\sa0\\sb0\\par ';
                    break;
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
// Tanggal surat: template punya placeholder ${tempat_tanggal}.
// Kota tidak lagi di-hardcode "Makassar" -- diambil dari field 'tempat' pada
// form. Jika field tempat dikosongkan, tampilkan tanggal saja (tanpa nama
// kota & tanpa koma menggantung).
$tanggalInput  = trim($data['tanggal']) !== '' ? $data['tanggal'] : '12 Juni 2026';
$tempatInput   = trim($data['tempat']);
$tempatTanggal = $tempatInput !== '' ? ($tempatInput . ', ' . $tanggalInput) : $tanggalInput;

$rtfValues = [
    'nomor'          => escapeRtfPlainText($data['nomor']),
    'klasifikasi'    => escapeRtfPlainText($data['klasifikasi']),
    'lampiran'       => escapeRtfPlainText($data['lampiran']),
    'hal'            => escapeRtfPlainText($data['hal']),
    'kepada'         => escapeRtfPlainText($data['kepada']),
    'jabatan'        => escapeRtfPlainText($data['jabatan']),
    'rujukan'        => htmlToRtf($data['rujukan']),
    'isi'            => htmlToRtf($data['isi']),
    'koordinasi'     => htmlToRtf($data['koordinasi']),
    'nama_ttd'       => escapeRtfPlainText($data['nama_ttd']),
    'pangkat_nrp'    => escapeRtfPlainText($data['pangkat_nrp']),
    'tembusan'       => htmlToRtf($data['tembusan']),
    'tempat_tanggal' => escapeRtfPlainText($tempatTanggal),
    'tempat'         => escapeRtfPlainText($tempatInput),
];

// =====================================================================
// 4. BACA TEMPLATE & LAKUKAN REPLACE
// =====================================================================
if (!file_exists(TEMPLATE_FILE)) {
    http_response_code(500);
    die('Template surat_keluar.rtf tidak ditemukan di folder templates/.');
}

$template = file_get_contents(TEMPLATE_FILE);

// PENTING: Word menyimpan RTF dengan word-wrap pada baris-baris panjang.
// Placeholder seperti ${nomor} bisa "terpotong" oleh newline mentah
// (mis. "$\{\nnomor\}"). Newline mentah TIDAK signifikan dalam RTF
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
//      teksnya menjadi "$\{\hich\af1\dbch\af13\loch\f1 nomor\}"
//      dan str_replace() literal di atas tidak menemukannya.
//
//      Kasus lain (mis. placeholder ${jabatan} pada template ini): Word
//      menyimpan teks placeholder dalam beberapa RUN pemformatan terpisah
//      karena diketik/direvisi pada waktu berbeda (insrsid berbeda),
//      sehingga muncul batas grup RTF "}{...}" DI TENGAH placeholder,
//      contoh: "$\{}{\rtlch...\insrsid123 jabatan}{\rtlch...\}\cell }".
//      $noise di bawah menoleransi kode format RTF *maupun* batas grup
//      "{"/"}" tersebut, baik sebelum maupun sesudah nama key.
// -----------------------------------------------------------------
$noise = '(?:\\\\[a-zA-Z]+-?\d*|[{} ])*';
foreach ($rtfValues as $key => $value) {
    $pattern = '/\$\\\\\{' . $noise . preg_quote($key, '/') . $noise . '\\\\\}/';
    $templateFinal = preg_replace_callback($pattern, function ($m) use ($value) {
        return $value;
    }, $templateFinal, 1);
}

// =====================================================================
// 5. TENTUKAN NAMA FILE OUTPUT
// =====================================================================
$namaFileDasar = 'Surat_Keluar_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $data['nomor'] ?: date('Ymd_His'));

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