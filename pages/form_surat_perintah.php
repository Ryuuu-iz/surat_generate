<?php
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Pembuatan Surat Perintah</title>

<!-- CKEditor 5 (Classic Build) via CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<link rel="stylesheet" href="../assets/css/form_surat_perintah.css">

</head>
<body>

<div class="app-wrapper">

    <!-- ======================= KOLOM KIRI : FORM ======================= -->
    <div class="form-column">
        <h2>Buat Surat Perintah</h2>
        <p class="subtitle">Isi form di bawah — hasil akan tampil langsung pada pratinjau di sebelah kanan.</p>

        <form id="formSurat" action="../proses/proses_surat_perintah.php" method="POST" target="_blank">

            <div class="field-group">
                <label for="nomor_surat">Nomor Surat</label>
                <input type="text" id="nomor_surat" name="nomor_surat"
                       placeholder="Contoh: SPRIN/123/VII/2026" oninput="updatePreview()">
            </div>

            <div class="field-group">
                <label for="pertimbangan">Pertimbangan</label>
                <div id="pertimbangan"></div>
                <textarea name="pertimbangan" id="pertimbangan_raw" style="display:none;"></textarea>
            </div>

            <div class="field-group">
                <label for="dasar">Dasar</label>
                <div class="list-field" id="dasar_list"></div>
                <textarea name="dasar" id="dasar_raw" style="display:none;"></textarea>
                <button type="button" class="btn-download" style="width:auto; padding:10px 12px; margin:0;" onclick="addDasarItem()">+ Tambah Dasar</button>
            </div>

            <div class="field-group">
                <label for="kepada">Kepada</label>
                <div class="personnel-list" id="kepada_personnel_list"></div>
                <textarea name="kepada" id="kepada_raw" style="display:none;"></textarea>
            </div>

            <div class="field-group">
                <label><input type="checkbox" id="underline_kepada" name="underline_kepada" value="1" onchange="updatePreview()"> Garis bawah nama</label>
            </div>

            <div class="field-group">
                <button type="button" class="btn-download" style="width:auto; padding:10px 12px; margin:0;" onclick="addPersonnelItem()">+ Tambah Personel</button>
            </div>

            <div class="field-group">
                <label for="untuk">Untuk</label>
                <div class="list-field" id="untuk_list"></div>
                <textarea name="untuk" id="untuk_raw" style="display:none;"></textarea>
                <button type="button" class="btn-download" style="width:auto; padding:10px 12px; margin:0;" onclick="addUntukItem()">+ Tambah Poin</button>
            </div>

            <div class="field-group">
                <label for="bulan_tahun">Pada Tanggal (Dikeluarkan di Makassar)</label>
                <input type="text" id="bulan_tahun" name="bulan_tahun"
                       placeholder="Contoh: 8 Juli 2026" oninput="updatePreview()">
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
                <div class="title">SURAT PERINTAH</div>
                <div class="nomor">Nomor: <span id="prev_nomor_surat">…</span></div>
            </div>

            <!-- Pertimbangan -->
            <div class="row-field">
                <div class="label">Pertimbangan</div>
                <div class="titik-dua">:</div>
                <div class="isi" id="prev_pertimbangan"><p>…</p></div>
            </div>

            <!-- Dasar -->
            <div class="row-field">
                <div class="label">Dasar</div>
                <div class="titik-dua">:</div>
                <div class="isi" id="prev_dasar"><p>…</p></div>
            </div>

            <div class="diperintahkan">DIPERINTAHKAN</div>

            <!-- Kepada -->
            <div class="row-field">
                <div class="label">Kepada</div>
                <div class="titik-dua">:</div>
                <div class="isi" id="prev_kepada"><p>…</p></div>
            </div>

            <!-- Untuk -->
            <div class="row-field">
                <div class="label">Untuk</div>
                <div class="titik-dua">:</div>
                <div class="isi" id="prev_untuk"><p>…</p></div>
            </div>

            <div class="selesai">Selesai.</div>

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

                    <div class="jabatan-ttd" id="prev_jabatan">…</div>
                    <div class="ruang-ttd"></div>
                    <div class="nama-ttd" id="prev_nama">…</div>
                    <div class="pangkat-ttd" id="prev_pangkat_nrp">…</div>
                </div>
            </div>

            <!-- Tembusan -->
            <div class="tembusan-box">
                <div class="judul-tembusan">Tembusan:</div>
                <div class="isi-tembusan" id="prev_tembusan"><p>…</p></div>
            </div>

        </div>
    </div>

</div>

<script>
/* =========================================================================
   Inisialisasi CKEditor 5 untuk semua field paragraf/rich-text
   ========================================================================= */
const richFields = ['pertimbangan', 'tembusan'];
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

function createPersonnelItem(name = '', jabatan = '') {
    const item = document.createElement('div');
    item.className = 'personnel-item';
    item.innerHTML = `
        <div class="personnel-row">
            <label>Nama, Pangkat/Gol, NIP/NRP</label>
            <input type="text" name="kepada_name[]" value="${name}" placeholder="Contoh: PENATA TK I RUSLAN, S.KOM NIP 197907202006041004" oninput="updatePreview()">
        </div>
        <div class="personnel-row">
            <label>Jabatan / Kesatuan</label>
            <input type="text" name="kepada_jabatan[]" value="${jabatan}" placeholder="Contoh: KAUR INTI SUBBID TEKINFO BID TIK POLDA SULSEL" oninput="updatePreview()">
        </div>
        <div class="personnel-actions">
            <button type="button" class="remove-personnel" onclick="removePersonnelItem(this)">Hapus</button>
        </div>
    `;
    return item;
}

function addPersonnelItem() {
    const list = document.getElementById('kepada_personnel_list');
    const item = createPersonnelItem();
    list.appendChild(item);
    updatePersonnelButtons();
    updatePreview();
}

function createUntukItem(text = '') {
    const item = document.createElement('div');
    item.className = 'dasar-item';
    item.innerHTML = `
        <div class="personnel-row">
            <label>Poin</label>
            <input type="text" name="untuk_item[]" value="${escapeHtml(text)}" placeholder="Contoh: melaporkan hasil pelaksanaannya kepada ..." oninput="updatePreview()">
        </div>
        <div class="dasar-actions">
            <button type="button" class="remove-dasar" onclick="removeUntukItem(this)">Hapus</button>
        </div>
    `;
    return item;
}

function addUntukItem(text = '') {
    const list = document.getElementById('untuk_list');
    const item = createUntukItem(text);
    list.appendChild(item);
    updateUntukButtons();
    updatePreview();
}

function removePersonnelItem(button) {
    const item = button.closest('.personnel-item');
    if (item) {
        item.remove();
        updatePersonnelButtons();
        updatePreview();
    }
}

function removeUntukItem(button) {
    const item = button.closest('.dasar-item');
    if (item) {
        item.remove();
        updateUntukButtons();
        updatePreview();
    }
}

function updatePersonnelButtons() {
    const items = document.querySelectorAll('.personnel-item');
    items.forEach((item, index) => {
        const btn = item.querySelector('.remove-personnel');
        if (btn) {
            btn.style.display = index === 0 && items.length === 1 ? 'none' : 'inline-flex';
        }
    });
}

function updateUntukButtons() {
    const items = document.querySelectorAll('#untuk_list .dasar-item');
    items.forEach((item, index) => {
        const btn = item.querySelector('.remove-dasar');
        if (btn) {
            btn.style.display = index === 0 && items.length === 1 ? 'none' : 'inline-flex';
        }
    });
}

function createDasarItem(text = '') {
    const item = document.createElement('div');
    item.className = 'dasar-item';
    item.innerHTML = `
        <div class="personnel-row">
            <label>Dasar</label>
            <input type="text" name="dasar_item[]" value="${escapeHtml(text)}" placeholder="Contoh: Undang-Undang Nomor 2 Tahun 2002 tentang Kepolisian Negara Republik Indonesia;" oninput="updatePreview()">
        </div>
        <div class="dasar-actions">
            <button type="button" class="remove-dasar" onclick="removeDasarItem(this)">Hapus</button>
        </div>
    `;
    return item;
}

function createTembusanItem(text = '') {
    const item = document.createElement('div');
    item.className = 'dasar-item';
    item.innerHTML = `
        <div class="personnel-row">
            <label>Tembusan</label>
            <input type="text" name="tembusan_item[]" value="${escapeHtml(text)}" placeholder="Contoh: Kapolda Sulsel" oninput="updatePreview()">
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
    const html = items.length
        ? `<ol class="dasar-list">${items.map(value => `<li>${escapeHtml(value)}</li>`).join('')}</ol>`
        : '<p>…</p>';

    document.getElementById('prev_tembusan').innerHTML = html;
    document.getElementById('tembusan_raw').value = html;
}

function addDasarItem(text = '') {
    const list = document.getElementById('dasar_list');
    const item = createDasarItem(text);
    list.appendChild(item);
    updateDasarButtons();
    updatePreview();
}

function removeDasarItem(button) {
    const item = button.closest('.dasar-item');
    if (item) {
        item.remove();
        updateDasarButtons();
        updatePreview();
    }
}

function updateDasarButtons() {
    const items = document.querySelectorAll('.dasar-item');
    items.forEach((item, index) => {
        const btn = item.querySelector('.remove-dasar');
        if (btn) {
            btn.style.display = index === 0 && items.length === 1 ? 'none' : 'inline-flex';
        }
    });
}

function createUntukItem(text = '') {
    const item = document.createElement('div');
    item.className = 'dasar-item';
    item.innerHTML = `
        <div class="personnel-row">
            <label>Poin</label>
            <input type="text" name="untuk_item[]" value="${escapeHtml(text)}" placeholder="Contoh: melaporkan hasil pelaksanaannya kepada ..." oninput="updatePreview()">
        </div>
        <div class="dasar-actions">
            <button type="button" class="remove-dasar" onclick="removeUntukItem(this)">Hapus</button>
        </div>
    `;
    return item;
}

function addUntukItem(text = '') {
    const list = document.getElementById('untuk_list');
    const item = createUntukItem(text);
    list.appendChild(item);
    updateUntukButtons();
    updatePreview();
}

function removeUntukItem(button) {
    const item = button.closest('.dasar-item');
    if (item) {
        item.remove();
        updateUntukButtons();
        updatePreview();
    }
}

function updateUntukButtons() {
    const items = document.querySelectorAll('#untuk_list .dasar-item');
    items.forEach((item, index) => {
        const btn = item.querySelector('.remove-dasar');
        if (btn) {
            btn.style.display = index === 0 && items.length === 1 ? 'none' : 'inline-flex';
        }
    });
}

function cleanDasarItemText(text) {
    return text.replace(/^\s*\d+[\.)]?\s*/u, '');
}

function renderDasarField() {
    const items = Array.from(document.querySelectorAll('input[name="dasar_item[]"]'))
        .map(el => cleanDasarItemText(el.value.trim()))
        .filter(value => value !== '');
    const html = items.length
        ? `<ol class="dasar-list">${items.map(value => `<li>${escapeHtml(value)}</li>`).join('')}</ol>`
        : '<p>…</p>';

    document.getElementById('prev_dasar').innerHTML = html;
    document.getElementById('dasar_raw').value = html;
}

function renderUntukField() {
    const items = Array.from(document.querySelectorAll('input[name="untuk_item[]"]'))
        .map(el => el.value.trim())
        .filter(value => value !== '');
    const html = items.length
        ? `<ol class="dasar-list">${items.map(value => `<li>${escapeHtml(value)}</li>`).join('')}</ol>`
        : '<p>…</p>';

    document.getElementById('prev_untuk').innerHTML = html;
    document.getElementById('untuk_raw').value = html;
}

function renderKepadaField() {
    const names = Array.from(document.querySelectorAll('input[name="kepada_name[]"]')).map(el => el.value.trim());
    const jabatan = Array.from(document.querySelectorAll('input[name="kepada_jabatan[]"]')).map(el => el.value.trim());
    const underline = document.getElementById('underline_kepada').checked;
    const paragraphs = [];

    for (let i = 0; i < names.length; i++) {
        if (names[i] === '' && jabatan[i] === '') {
            continue;
        }
        const escapedName = names[i] !== '' ? escapeHtml(names[i]) : '…';
        const escapedJabatan = jabatan[i] !== '' ? escapeHtml(jabatan[i]) : '';
        const nameLine = underline
            ? `<span class="kepada-name"><u>${escapedName}</u></span>`
            : `<span class="kepada-name">${escapedName}</span>`;
        const jabatanLine = escapedJabatan ? `<br><span class="kepada-jabatan">${escapedJabatan}</span>` : '';
        paragraphs.push(`<p class="kepada-pair">${nameLine}${jabatanLine}</p>`);
    }

    const html = paragraphs.length ? paragraphs.join('') : '<p>…</p>';
    const preview = document.getElementById('prev_kepada');
    preview.innerHTML = html;
    preview.classList.toggle('underline-kepada', underline);
    document.getElementById('kepada_raw').value = html;
}

function escapeHtml(text) {
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

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
    setPlainText('prev_bulan_tahun', document.getElementById('bulan_tahun').value);
    setPlainText('prev_jabatan', document.getElementById('jabatan').value);
    setPlainText('prev_nama', document.getElementById('nama').value);
    setPlainText('prev_pangkat_nrp', document.getElementById('pangkat_nrp').value);

    // Field rich text -> innerHTML dari CKEditor (agar bold/italic/list ikut tampil)
    setRichHtml('prev_pertimbangan', 'pertimbangan');
    renderDasarField();
    renderKepadaField();
    renderUntukField();
    renderTembusanField();
}

function setPlainText(previewId, value) {
    const el = document.getElementById(previewId);
    el.textContent = value.trim() !== '' ? value : '…';
}

document.addEventListener('DOMContentLoaded', function () {
    if (document.querySelectorAll('#kepada_personnel_list .personnel-item').length === 0) {
        addPersonnelItem();
    }
    if (document.querySelectorAll('#dasar_list .dasar-item').length === 0) {
        addDasarItem();
    }
    if (document.querySelectorAll('#untuk_list .dasar-item').length === 0) {
        addUntukItem();
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