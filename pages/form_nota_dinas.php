<?php
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Pembuatan Nota Dinas</title>

<!-- CKEditor 5 (Classic Build) via CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

<link rel="stylesheet" href="../assets/css/form_nota_dinas.css">
</head>
<body>

<div class="app-wrapper">

    <!-- ======================= KOLOM KIRI : FORM ======================= -->
    <div class="form-column">
        <h2>Buat Nota Dinas</h2>
        <p class="subtitle">Isi form di bawah — hasil akan tampil langsung pada pratinjau di sebelah kanan.</p>

        <form id="formSurat" action="../proses/proses_nota_dinas.php" method="POST" target="_blank">

            <div class="field-group">
                <label for="nomor_surat">Nomor Surat</label>
                <input type="text" id="nomor_surat" name="nomor_surat"
                       placeholder="Contoh: ND/123/VII/2026" oninput="updatePreview()">
            </div>

            <div class="field-group">
                <label for="kepada">Kepada</label>
                <input type="text" id="kepada" name="kepada"
                       placeholder="Contoh: Kabidkeu Polda Sulsel" oninput="updatePreview()">
            </div>

            <div class="field-group">
                <label for="dari">Dari</label>
                <input type="text" id="dari" name="dari"
                       placeholder="Contoh: Kabid TIK Polda Sulsel" oninput="updatePreview()">
            </div>

            <div class="field-group">
                <label for="hal">Hal</label>
                <input type="text" id="hal" name="hal"
                       placeholder="Contoh: Permohonan Dukungan Anggaran" oninput="updatePreview()">
            </div>

            <div class="field-group">
                <label for="rujukan">1. Rujukan</label>
                <div id="rujukan"></div>
                <textarea name="rujukan" id="rujukan_raw" style="display:none;"></textarea>
            </div>

            <div class="field-group">
                <label for="isi">2. Isi</label>
                <div id="isi"></div>
                <textarea name="isi" id="isi_raw" style="display:none;"></textarea>
            </div>

            <div class="field-group">
                <label for="tembusan">Tembusan</label>
                <div id="tembusan">
                    <ol>
                        <li>Wakapolda Sulsel.</li>
                        <li>Irwasda Polda Sulsel.</li>
                        <li>Karorena Polda Sulsel.</li>
                        <li>Kabidkeu Polda Sulsel</li>
                    </ol>
                </div>
                <textarea name="tembusan" id="tembusan_raw" style="display:none;"></textarea>
            </div>

            <div class="field-group">
                <label for="jabatan">Jabatan Penandatangan</label>
                <input type="text" id="jabatan" name="jabatan"
                       placeholder="Contoh: KEPALA BIDANG TEKNOLOGI INFORMASI KOMUNIKASI" oninput="updatePreview()">
            </div>

            <div class="field-group">
                <label for="nama">Nama Penandatangan</label>
                <input type="text" id="nama" name="nama"
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
                <div class="title">NOTA DINAS</div>
                <div class="nomor">Nomor: <span id="prev_nomor_surat">…</span></div>
            </div>

            <!-- Kepada / Dari / Hal -->
            <div class="row-field">
                <div class="label">Kepada</div>
                <div class="titik-dua">:</div>
                <div class="isi" id="prev_kepada">…</div>
            </div>
            <div class="row-field">
                <div class="label">Dari</div>
                <div class="titik-dua">:</div>
                <div class="isi" id="prev_dari">…</div>
            </div>
            <div class="row-field">
                <div class="label">Hal</div>
                <div class="titik-dua">:</div>
                <div class="isi" id="prev_hal">…</div>
            </div>

            <div style="height:12px;"></div>

            <!-- 1. Rujukan -->
            <div class="numbered-block">
                <div class="nomor-urut">1.</div>
                <div class="konten">
                    <div class="rujukan-label">Rujukan:</div>
                    <div class="rujukan-sub">
                        <div class="huruf">a.</div>
                        <div class="konten" id="prev_rujukan"><p>…</p></div>
                    </div>
                </div>
            </div>

            <!-- 2. Isi -->
            <div class="numbered-block">
                <div class="nomor-urut">2.</div>
                <div class="konten" id="prev_isi"><p>…</p></div>
            </div>

            <!-- 3. Penutup (statis, sudah baku dalam template) -->
            <div class="numbered-block">
                <div class="nomor-urut">3.</div>
                <div class="konten">Demikian untuk menjadi maklumi.</div>
            </div>

            <!-- Blok penutup: tanggal & ttd -->
            <div class="blok-penutup">
                <div class="kolom-ttd">
                    <div class="tanggal-ttd">Makassar,       Juli 2026</div>
                    <div class="jabatan-ttd" id="prev_jabatan">…</div>
                    <div class="ruang-ttd"></div>
                    <div class="nama-ttd" id="prev_nama">…</div>
                    <div class="pangkat-ttd" id="prev_pangkat_nrp">…</div>
                </div>
            </div>

            <!-- Tembusan (dinamis, mengikuti isian form) -->
            <div class="tembusan-box">
                <div class="judul-tembusan">Tembusan:</div>
                <div class="isi-tembusan" id="prev_tembusan"><ol><li>…</li></ol></div>
            </div>

        </div>
    </div>

</div>

<script>
/* =========================================================================
   Inisialisasi CKEditor 5 untuk field paragraf/rich-text (bisa berisi list)
   ========================================================================= */
const richFields = ['rujukan', 'isi', 'tembusan'];
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
    setPlainText('prev_kepada', document.getElementById('kepada').value);
    setPlainText('prev_dari', document.getElementById('dari').value);
    setPlainText('prev_hal', document.getElementById('hal').value);
    setPlainText('prev_jabatan', document.getElementById('jabatan').value);
    setPlainText('prev_nama', document.getElementById('nama').value);
    setPlainText('prev_pangkat_nrp', document.getElementById('pangkat_nrp').value);

    // Field rich text -> innerHTML dari CKEditor (agar bold/italic/list ikut tampil)
    setRichHtml('prev_rujukan', 'rujukan');
    setRichHtml('prev_isi', 'isi');
    setRichHtml('prev_tembusan', 'tembusan');
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