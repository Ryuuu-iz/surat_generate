<?php
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Pembuatan Surat Keluar</title>

<!-- CKEditor 5 (Classic Build) via CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<link rel="stylesheet" href="../assets/css/form_surat_keluar.css">

</head>
<body>

<div class="app-wrapper">

    <!-- ======================= KOLOM KIRI : FORM ======================= -->
    <div class="form-column">
        <h2>Buat Surat Keluar</h2>
        <p class="subtitle">Isi form di bawah — hasil akan tampil langsung pada pratinjau di sebelah kanan.</p>

        <form id="formSurat" action="../proses/proses_surat_keluar.php" method="POST" target="_blank">

            <div class="field-group">
                <label for="nomor">Nomor Surat</label>
                <input type="text" id="nomor" name="nomor"
                       placeholder="Contoh: B/123/VII/2026/Bidtik" oninput="updatePreview()">
            </div>

            <div class="field-group">
                <label for="tempat">Tempat / Kota</label>
                <input type="text" id="tempat" name="tempat"
                       placeholder="Contoh: Makassar" oninput="updatePreview()">
            </div>

            <div class="field-group">
                <label for="tanggal">Tanggal Surat</label>
                <input type="text" id="tanggal" name="tanggal"
                       placeholder="Contoh: 20 Juli 2026" oninput="updatePreview()">
            </div>

            <div class="field-group">
                <label for="klasifikasi">Klasifikasi</label>
                <input type="text" id="klasifikasi" name="klasifikasi"
                       placeholder="Contoh: Biasa" oninput="updatePreview()">
            </div>

            <div class="field-group">
                <label for="lampiran">Lampiran</label>
                <input type="text" id="lampiran" name="lampiran"
                       placeholder="Contoh: 1 (satu) berkas" oninput="updatePreview()">
            </div>

            <div class="field-group">
                <label for="hal">Hal</label>
                <input type="text" id="hal" name="hal"
                       placeholder="Contoh: Permohonan Dukungan Personel" oninput="updatePreview()">
            </div>

            <div class="field-group">
                <label for="kepada">Kepada (Yth.)</label>
                <input type="text" id="kepada" name="kepada"
                       placeholder="Contoh: Kepala Bidang Propam Polda Sulsel" oninput="updatePreview()">
            </div>

            <div class="field-group">
                <label for="rujukan">1. Rujukan</label>
                <div class="list-field" id="rujukan_list"></div>
                <textarea name="rujukan" id="rujukan_raw" style="display:none;"></textarea>
                <button type="button" class="btn-download" style="width:auto; padding:10px 12px; margin:8px 0 0 0;" onclick="addRujukanItem()">+ Tambah Rujukan</button>
            </div>

            <div class="field-group">
                <label for="isi">2. Isi</label>
                <div id="isi"></div>
                <textarea name="isi" id="isi_raw" style="display:none;"></textarea>
            </div>

            <div class="field-group">
                <label for="koordinasi">3. Koordinasi</label>
                <div id="koordinasi"></div>
                <textarea name="koordinasi" id="koordinasi_raw" style="display:none;"></textarea>
            </div>

            <div class="field-group">
                <label for="jabatan">Jabatan Penandatangan (a.n. Kapolda)</label>
                <input type="text" id="jabatan" name="jabatan"
                       placeholder="Contoh: Kepala Bidang Teknologi Informasi" oninput="updatePreview()">
            </div>

            <div class="field-group">
                <label for="nama_ttd">Nama Penandatangan</label>
                <input type="text" id="nama_ttd" name="nama_ttd"
                       placeholder="Contoh: Asri Ani" oninput="updatePreview()">
            </div>

            <div class="field-group">
                <label for="pangkat_nrp">Pangkat / NRP</label>
                <input type="text" id="pangkat_nrp" name="pangkat_nrp"
                       placeholder="Contoh: AKBP NRP 12345678" oninput="updatePreview()">
            </div>

            <div class="field-group">
                <label for="tembusan">Tembusan</label>
                <div class="list-field" id="tembusan_list"></div>
                <textarea name="tembusan" id="tembusan_raw" style="display:none;"></textarea>
                <button type="button" class="btn-download" style="width:auto; padding:10px 12px; margin:8px 0 0 0;" onclick="addTembusanItem()">+ Tambah Tembusan</button>
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

            <!-- Kop Surat + Tanggal (sejajar, tanggal mentok kanan) -->
            <div class="kop-header-row">
                <div class="kop-surat">
                    <img src="../assets/img/logo/logo-polri-bnw.png" alt="Logo Polri" class="logo-kop">
                    <div class="kop-text-wrap">
                        <div class="kop-text">
                            <div class="line1">Kepolisian Negara Republik Indonesia</div>
                            <div class="line2">Daerah Sulawesi Selatan</div>
                        </div>
                        <div class="alamat-line">Jalan P. Kemerdekaan Km. 16 Makassar 90241</div>
                    </div>
                </div>

                <div class="tanggal-surat"><span id="prev_tempat">…</span>, <span id="prev_tanggal">…</span></div>
            </div>

            <!-- Nomor / Klasifikasi / Lampiran / Hal, dengan "Kepada" sejajar baris "Hal" di sebelah kanan -->
            <div class="info-kepada-row">
                <div class="header-info">
                    <div class="row-field">
                        <div class="label">Nomor</div>
                        <div class="titik-dua">:</div>
                        <div class="isi" id="prev_nomor">…</div>
                    </div>
                    <div class="row-field">
                        <div class="label">Klasifikasi</div>
                        <div class="titik-dua">:</div>
                        <div class="isi" id="prev_klasifikasi">…</div>
                    </div>
                    <div class="row-field">
                        <div class="label">Lampiran</div>
                        <div class="titik-dua">:</div>
                        <div class="isi" id="prev_lampiran">…</div>
                    </div>
                    <div class="row-field">
                        <div class="label">Hal</div>
                        <div class="titik-dua">:</div>
                        <div class="isi" id="prev_hal">…</div>
                    </div>
                </div>

                <div class="kepada-label">Kepada</div>
            </div>

            <!-- Yth -->
            <div class="yth-block">
                <span class="yth-label">Yth.</span>
                <span class="yth-isi" id="prev_kepada">…</span>
                <div class="yth-di">di</div>
                <div class="yth-tempat" id="prev_tempat_yth">…</div>
            </div>

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

            <!-- 3. Koordinasi -->
            <div class="numbered-block">
                <div class="nomor-urut">3.</div>
                <div class="konten" id="prev_koordinasi"><p>…</p></div>
            </div>

            <!-- 4. Penutup (statis, sudah baku dalam template) -->
            <div class="numbered-block">
                <div class="nomor-urut">4.</div>
                <div class="konten">Demikian untuk menjadi maklum.</div>
            </div>

            <!-- Blok penutup: Tembusan (kiri) & ttd (kanan), sejajar di bawah -->
            <div class="blok-penutup">
                <div class="tembusan-box">
                    <div class="judul-tembusan">Tembusan:</div>
                    <div class="isi-tembusan" id="prev_tembusan"><p>…</p></div>
                </div>
                <div class="kolom-ttd">
                    <div class="an-ttd"><span class="an-prefix">a.n.</span> Kepala Kepolisian Daerah Sulawesi Selatan</div>
                    <div class="jabatan-ttd" id="prev_jabatan">…</div>
                    <div class="ruang-ttd"></div>
                    <div class="nama-ttd" id="prev_nama_ttd">…</div>
                    <div class="pangkat-ttd" id="prev_pangkat_nrp">…</div>
                </div>
            </div>

        </div>
    </div>

</div>

<script>
/* =========================================================================
   Inisialisasi CKEditor 5 untuk field paragraf/rich-text (bisa berisi list)
   ========================================================================= */
const richFields = ['isi', 'koordinasi'];
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

function escapeHtml(text) {
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

/* =========================================================================
   Rujukan — daftar tambah/hapus. Item pertama tampil mengikuti huruf "a."
   yang sudah baku pada template. Antar item dipisah baris baru.
   ========================================================================= */
function createRujukanItem(text = '') {
    const item = document.createElement('div');
    item.className = 'dasar-item';
    item.innerHTML = `
        <div class="personnel-row">
            <label>Rujukan</label>
            <input type="text" name="rujukan_item[]" value="${escapeHtml(text)}" placeholder="Contoh: Surat Telegram Kapolri Nomor ST/123/VII/2026" oninput="updatePreview()">
        </div>
        <div class="dasar-actions">
            <button type="button" class="remove-dasar" onclick="removeRujukanItem(this)">Hapus</button>
        </div>
    `;
    return item;
}

function addRujukanItem(text = '') {
    const list = document.getElementById('rujukan_list');
    const item = createRujukanItem(text);
    list.appendChild(item);
    updateRujukanButtons();
    updatePreview();
}

function removeRujukanItem(button) {
    const item = button.closest('.dasar-item');
    if (item) {
        item.remove();
        updateRujukanButtons();
        updatePreview();
    }
}

function updateRujukanButtons() {
    const items = document.querySelectorAll('#rujukan_list .dasar-item');
    items.forEach((item, index) => {
        const btn = item.querySelector('.remove-dasar');
        if (btn) {
            btn.style.display = index === 0 && items.length === 1 ? 'none' : 'inline-flex';
        }
    });
}

function renderRujukanField() {
    const items = Array.from(document.querySelectorAll('input[name="rujukan_item[]"]'))
        .map(el => el.value.trim())
        .filter(value => value !== '');

    let html;
    if (items.length === 0) {
        html = '<p>…</p>';
    } else {
        html = items
            .map(value => `<p>${escapeHtml(value)}</p>`)
            .join('');
    }

    document.getElementById('prev_rujukan').innerHTML = html;
    document.getElementById('rujukan_raw').value = html;
}

/* =========================================================================
   Tembusan — daftar tambah/hapus, ditampilkan sebagai daftar bernomor.
   ========================================================================= */
function createTembusanItem(text = '') {
    const item = document.createElement('div');
    item.className = 'dasar-item';
    item.innerHTML = `
        <div class="personnel-row">
            <label>Tembusan</label>
            <input type="text" name="tembusan_item[]" value="${escapeHtml(text)}" placeholder="Contoh: Wakapolda Sulsel" oninput="updatePreview()">
        </div>
        <div class="dasar-actions">
            <button type="button" class="remove-dasar" onclick="removeTembusanItem(this)">Hapus</button>
        </div>
    `;
    return item;
}

function addTembusanItem(text = '') {
    const list = document.getElementById('tembusan_list');
    const item = createTembusanItem(text);
    list.appendChild(item);
    updateTembusanButtons();
    updatePreview();
}

function removeTembusanItem(button) {
    const item = button.closest('.dasar-item');
    if (item) {
        item.remove();
        updateTembusanButtons();
        updatePreview();
    }
}

function updateTembusanButtons() {
    const items = document.querySelectorAll('#tembusan_list .dasar-item');
    items.forEach((item, index) => {
        const btn = item.querySelector('.remove-dasar');
        if (btn) {
            btn.style.display = index === 0 && items.length === 1 ? 'none' : 'inline-flex';
        }
    });
}

function renderTembusanField() {
    const items = Array.from(document.querySelectorAll('input[name="tembusan_item[]"]'))
        .map(el => el.value.trim())
        .filter(value => value !== '');

    // Tidak lagi dibungkus <ol> / diberi nomor manual: paragraf placeholder
    // ${tembusan} pada template RTF sudah berupa list bernomor otomatis
    // dari Word (angka "1." muncul sendiri saat dibuka di Word), jadi
    // kalau kita tambah nomor lagi di sini hasilnya jadi dobel.
    let html;
    if (items.length === 0) {
        html = '<p>…</p>';
    } else {
        html = items
            .map(value => `<p>${escapeHtml(value)}</p>`)
            .join('');
    }

    document.getElementById('prev_tembusan').innerHTML = html;
    document.getElementById('tembusan_raw').value = html;
}

/* =========================================================================
   Live Preview — dipanggil dari oninput (field biasa) & change:data (CKEditor)
   ========================================================================= */
function updatePreview() {
    // Field teks biasa -> textContent (aman dari XSS, tidak perlu formatting)
    setPlainText('prev_nomor', document.getElementById('nomor').value);
    setPlainText('prev_tempat', document.getElementById('tempat').value);
    setPlainText('prev_tempat_yth', document.getElementById('tempat').value);
    setPlainText('prev_tanggal', document.getElementById('tanggal').value);
    setPlainText('prev_klasifikasi', document.getElementById('klasifikasi').value);
    setPlainText('prev_lampiran', document.getElementById('lampiran').value);
    setPlainText('prev_hal', document.getElementById('hal').value);
    setPlainText('prev_kepada', document.getElementById('kepada').value);
    setPlainText('prev_jabatan', document.getElementById('jabatan').value);
    setPlainText('prev_nama_ttd', document.getElementById('nama_ttd').value);
    setPlainText('prev_pangkat_nrp', document.getElementById('pangkat_nrp').value);

    // Field daftar tambah/hapus
    renderRujukanField();
    renderTembusanField();

    // Field rich text -> innerHTML dari CKEditor (agar bold/italic/list ikut tampil)
    setRichHtml('prev_isi', 'isi');
    setRichHtml('prev_koordinasi', 'koordinasi');
}

function setPlainText(previewId, value) {
    const el = document.getElementById(previewId);
    el.textContent = value.trim() !== '' ? value : '…';
}

document.addEventListener('DOMContentLoaded', function () {
    if (document.querySelectorAll('#rujukan_list .dasar-item').length === 0) {
        addRujukanItem();
    }
    if (document.querySelectorAll('#tembusan_list .dasar-item').length === 0) {
        addTembusanItem();
    }
    updatePreview();
});

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