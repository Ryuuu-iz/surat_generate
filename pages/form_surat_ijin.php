<?php
/**
 * form_surat_ijin.php
 * -----------------------------------------------------------------
 * Form input untuk pembuatan "Surat Izin" + Live Preview realtime.
 * Tidak menggunakan database — semua data dikirim via POST ke
 * proses_surat_ijin.php saat tombol unduh ditekan.
 * -----------------------------------------------------------------
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Pembuatan Surat Izin</title>

<!-- CKEditor 5 (Classic Build) via CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

<link rel="stylesheet" href="../assets/css/form_surat_ijin.css">
</head>
<body>

<div class="app-wrapper">

    <!-- ======================= KOLOM KIRI : FORM ======================= -->
    <div class="form-column">
        <h2>Buat Surat Izin</h2>
        <p class="subtitle">Isi form di bawah — hasil akan tampil langsung pada pratinjau di sebelah kanan.</p>

        <form id="formSurat" action="../proses/proses_surat_ijin.php" method="POST" target="_blank">

            <div class="field-group">
                <label for="nomor_surat">Nomor Surat</label>
                <input type="text" id="nomor_surat" name="nomor_surat"
                       placeholder="Contoh: SI/123/VII/2026" oninput="updatePreview()">
            </div>

            <div class="field-group">
                <label for="nama">1. Nama</label>
                <input type="text" id="nama" name="nama"
                       placeholder="Contoh: Asri Ani" oninput="updatePreview()">
            </div>

            <div class="field-group">
                <label for="pangkat">2. Pangkat / NRP / NIP</label>
                <input type="text" id="pangkat" name="pangkat"
                       placeholder="Contoh: AKBP NRP 12345678" oninput="updatePreview()">
            </div>

            <div class="field-group">
                <label for="jabatan">3. Jabatan</label>
                <input type="text" id="jabatan" name="jabatan"
                       placeholder="Contoh: KEPALA BIDANG TEKNOLOGI INFORMASI KOMUNIKASI" oninput="updatePreview()">
            </div>

            <div class="field-group">
                <label for="kesatuan">4. Kesatuan</label>
                <input type="text" id="kesatuan" name="kesatuan"
                       placeholder="Contoh: Bidang TIK Polda Sulsel" oninput="updatePreview()">
            </div>

            <div class="field-group">
                <label for="alasan">5. Alasan Permohonan</label>
                <div id="alasan"></div>
                <textarea name="alasan" id="alasan_raw" style="display:none;"></textarea>
            </div>

            <div class="field-group">
                <label for="tujuan">6. Tujuan</label>
                <input type="text" id="tujuan" name="tujuan"
                       placeholder="Contoh: Makassar - Jakarta" oninput="updatePreview()">
            </div>

            <div class="field-group">
                <label for="pengikut">7. Pengikut</label>
                <div id="pengikut"></div>
                <textarea name="pengikut" id="pengikut_raw" style="display:none;"></textarea>
            </div>

            <div class="field-group">
                <label for="kendaraan">8. Kendaraan</label>
                <input type="text" id="kendaraan" name="kendaraan"
                       placeholder="Contoh: Mobil Dinas Avanza DD 1234 XX" oninput="updatePreview()">
            </div>

            <div class="field-group">
                <label for="tgl_berangkat">9. Berangkat Tanggal</label>
                <input type="text" id="tgl_berangkat" name="tgl_berangkat"
                       placeholder="Contoh: 10 Juli 2026" oninput="updatePreview()">
            </div>

            <div class="field-group">
                <label for="tgl_kembali">10. Kembali Tanggal</label>
                <input type="text" id="tgl_kembali" name="tgl_kembali"
                       placeholder="Contoh: 12 Juli 2026" oninput="updatePreview()">
            </div>

            <div class="field-group">
                <label for="catatan">11. Catatan</label>
                <div id="catatan"></div>
                <textarea name="catatan" id="catatan_raw" style="display:none;"></textarea>
            </div>

            <div class="field-group">
                <label for="bulan_tahun">Pada Tanggal (Dikeluarkan di Makassar)</label>
                <input type="text" id="bulan_tahun" name="bulan_tahun"
                       placeholder="Contoh: 8 Juli 2026" oninput="updatePreview()">
            </div>

            <div class="field-group">
                <label for="jabatan_ttd">Jabatan Penandatangan</label>
                <input type="text" id="jabatan_ttd" name="jabatan_ttd"
                       placeholder="Contoh: KEPALA BIDANG TEKNOLOGI INFORMASI KOMUNIKASI" oninput="updatePreview()">
            </div>

            <div class="field-group">
                <label for="nama_ttd">Nama Penandatangan</label>
                <input type="text" id="nama_ttd" name="nama_ttd"
                       placeholder="Contoh: Asri Ani" oninput="updatePreview()">
            </div>

            <div class="field-group">
                <label for="pangkat_nrp">Pangkat / NRP Penandatangan</label>
                <input type="text" id="pangkat_nrp" name="pangkat_nrp"
                       placeholder="Contoh: AKBP NRP 12345678" oninput="updatePreview()">
            </div>

            <div class="field-group">
                <label>Format Unduhan</label>
                <div class="format-choice">
                    <label><input type="radio" name="format_output" value="rtf" checked> Tetap Word (.rtf)</label>
                    <label><input type="radio" name="format_output" value="pdf"> Dokumen PDF (.pdf)</label>
                </div>
            </div>

            <button type="submit" class="btn-download">Unduh Surat</button>
        </form>
    </div>

    <!-- ======================= KOLOM KANAN : LIVE PREVIEW ======================= -->
    <div class="preview-column">
        <div class="paper">

            <!-- Kop Surat -->
            <div class="kop-surat">
                <div class="logo-box">LOGO<br>TRIBRATA</div>
                <div class="kop-text">
                    <div class="line1">Kepolisian Negara Republik Indonesia</div>
                    <div class="line2">Daerah Sulawesi Selatan</div>
                    <div class="line3">Bidang Teknologi Informasi Komunikasi</div>
                </div>
            </div>

            <!-- Judul -->
            <div class="judul-surat">
                <div class="title">SURAT IZIN</div>
                <div class="nomor">Nomor: <span id="prev_nomor_surat">…</span></div>
            </div>

            <div class="diberikan-kepada">Diberikan kepada:</div>

            <!-- 1. Nama -->
            <div class="row-field">
                <div class="nomor-urut">1.</div>
                <div class="label">Nama</div>
                <div class="titik-dua">:</div>
                <div class="isi" id="prev_nama">…</div>
            </div>

            <!-- 2. Pangkat -->
            <div class="row-field">
                <div class="nomor-urut">2.</div>
                <div class="label">Pangkat / NRP / NIP</div>
                <div class="titik-dua">:</div>
                <div class="isi" id="prev_pangkat">…</div>
            </div>

            <!-- 3. Jabatan -->
            <div class="row-field">
                <div class="nomor-urut">3.</div>
                <div class="label">Jabatan</div>
                <div class="titik-dua">:</div>
                <div class="isi" id="prev_jabatan">…</div>
            </div>

            <!-- 4. Kesatuan -->
            <div class="row-field">
                <div class="nomor-urut">4.</div>
                <div class="label">Kesatuan</div>
                <div class="titik-dua">:</div>
                <div class="isi" id="prev_kesatuan">…</div>
            </div>

            <!-- 5. Alasan -->
            <div class="row-field">
                <div class="nomor-urut">5.</div>
                <div class="label">Alasan Permohonan</div>
                <div class="titik-dua">:</div>
                <div class="isi" id="prev_alasan"><p>…</p></div>
            </div>

            <!-- 6. Tujuan -->
            <div class="row-field">
                <div class="nomor-urut">6.</div>
                <div class="label">Tujuan</div>
                <div class="titik-dua">:</div>
                <div class="isi" id="prev_tujuan">…</div>
            </div>

            <!-- 7. Pengikut -->
            <div class="row-field">
                <div class="nomor-urut">7.</div>
                <div class="label">Pengikut</div>
                <div class="titik-dua">:</div>
                <div class="isi" id="prev_pengikut"><p>…</p></div>
            </div>

            <!-- 8. Kendaraan -->
            <div class="row-field">
                <div class="nomor-urut">8.</div>
                <div class="label">Kendaraan</div>
                <div class="titik-dua">:</div>
                <div class="isi" id="prev_kendaraan">…</div>
            </div>

            <!-- 9. Berangkat tanggal -->
            <div class="row-field">
                <div class="nomor-urut">9.</div>
                <div class="label">Berangkat tanggal</div>
                <div class="titik-dua">:</div>
                <div class="isi" id="prev_tgl_berangkat">…</div>
            </div>

            <!-- 10. Kembali tanggal -->
            <div class="row-field">
                <div class="nomor-urut">10.</div>
                <div class="label">Kembali tanggal</div>
                <div class="titik-dua">:</div>
                <div class="isi" id="prev_tgl_kembali">…</div>
            </div>

            <!-- 11. Catatan -->
            <div class="row-field">
                <div class="nomor-urut">11.</div>
                <div class="label">Catatan</div>
                <div class="titik-dua">:</div>
                <div class="isi" id="prev_catatan"><p>…</p></div>
            </div>

            <!-- Blok penutup: tanggal & ttd -->
            <div class="blok-penutup">
                <div class="kolom-ttd">
                    <div class="baris-tanggal">
                        <div class="row-field">
                            <div class="label">Dikeluarkan di</div>
                            <div class="titik-dua">:</div>
                            <div class="isi">Makassar</div>
                        </div>
                        <div class="row-field">
                            <div class="label">Pada tanggal</div>
                            <div class="titik-dua">:</div>
                            <div class="isi" id="prev_bulan_tahun">…</div>
                        </div>
                    </div>

                    <div class="jabatan-ttd" id="prev_jabatan_ttd">…</div>
                    <div class="ruang-ttd"></div>
                    <div class="nama-ttd" id="prev_nama_ttd">…</div>
                    <div class="pangkat-ttd" id="prev_pangkat_nrp">…</div>
                </div>
            </div>

            <!-- Tembusan (tetap/statis, sudah baku dalam template) -->
            <div class="tembusan-box">
                <div class="judul-tembusan">Tembusan:</div>
                <div class="isi-tembusan">
                    <ol>
                        <li>Kapolda</li>
                        <li>Irwasda Polda Sulsel</li>
                        <li>Kabid Propam Polda Sulsel</li>
                    </ol>
                </div>
            </div>

        </div>
    </div>

</div>

<script>
/* =========================================================================
   Inisialisasi CKEditor 5 untuk field paragraf/rich-text (bisa berisi list)
   ========================================================================= */
const richFields = ['alasan', 'pengikut', 'catatan'];
const editors = {}; // menyimpan instance CKEditor per field

richFields.forEach(function (fieldName) {
    ClassicEditor
        .create(document.querySelector('#' + fieldName), {
            toolbar: ['bold', 'italic', 'underline', 'bulletedList', 'numberedList', '|', 'undo', 'redo']
        })
        .then(function (editor) {
            editors[fieldName] = editor;

            // Sinkron awal
            syncHiddenTextarea(fieldName);
            updatePreview();

            // Setiap kali isi editor berubah (termasuk bold/italic/list)
            editor.model.document.on('change:data', function () {
                syncHiddenTextarea(fieldName);
                updatePreview();
            });
        })
        .catch(function (error) {
            console.error('Gagal memuat CKEditor untuk ' + fieldName, error);
        });
});

// Menyalin HTML dari CKEditor ke textarea tersembunyi agar ikut ter-submit via form POST biasa
function syncHiddenTextarea(fieldName) {
    const raw = document.getElementById(fieldName + '_raw');
    if (editors[fieldName]) {
        raw.value = editors[fieldName].getData(); // HTML: <p>, <strong>, <ul><li>, dst
    }
}

/* =========================================================================
   Live Preview — dipanggil dari oninput (field biasa) & change:data (CKEditor)
   ========================================================================= */
function updatePreview() {
    // Field teks biasa -> textContent (aman dari XSS, tidak perlu formatting)
    setPlainText('prev_nomor_surat', document.getElementById('nomor_surat').value);
    setPlainText('prev_nama', document.getElementById('nama').value);
    setPlainText('prev_pangkat', document.getElementById('pangkat').value);
    setPlainText('prev_jabatan', document.getElementById('jabatan').value);
    setPlainText('prev_kesatuan', document.getElementById('kesatuan').value);
    setPlainText('prev_tujuan', document.getElementById('tujuan').value);
    setPlainText('prev_kendaraan', document.getElementById('kendaraan').value);
    setPlainText('prev_tgl_berangkat', document.getElementById('tgl_berangkat').value);
    setPlainText('prev_tgl_kembali', document.getElementById('tgl_kembali').value);
    setPlainText('prev_bulan_tahun', document.getElementById('bulan_tahun').value);
    setPlainText('prev_jabatan_ttd', document.getElementById('jabatan_ttd').value);
    setPlainText('prev_nama_ttd', document.getElementById('nama_ttd').value);
    setPlainText('prev_pangkat_nrp', document.getElementById('pangkat_nrp').value);

    // Field rich text -> innerHTML dari CKEditor (agar bold/italic/list ikut tampil)
    setRichHtml('prev_alasan', 'alasan');
    setRichHtml('prev_pengikut', 'pengikut');
    setRichHtml('prev_catatan', 'catatan');
}

function setPlainText(previewId, value) {
    const el = document.getElementById(previewId);
    el.textContent = value.trim() !== '' ? value : '…';
}

function setRichHtml(previewId, fieldName) {
    const el = document.getElementById(previewId);
    if (editors[fieldName]) {
        const data = editors[fieldName].getData();
        el.innerHTML = data.trim() !== '' ? data : '<p>…</p>';
    }
}
</script>

</body>
</html>