<?php
$BASE_URL = '../';
$defaultIcon = '<path d="M0 0h14v14H0z" fill="none"/><path fill="currentColor" fill-rule="evenodd" clip-rule="evenodd" d="M.658.44A1.5 1.5 0 0 1 1.718 0h5.587a1.5 1.5 0 0 1 1.06.44l3.414 3.414a1.5 1.5 0 0 1 .44 1.06V12.5a1.5 1.5 0 0 1-1.5 1.5h-9a1.5 1.5 0 0 1-1.5-1.5v-11c0-.398.158-.78.44-1.06ZM5.33 4.527a.75.75 0 0 1 .175 1.047L4.108 7.53a.75.75 0 0 1-1.14.094l-.838-.838a.75.75 0 0 1 1.06-1.06l.212.211l.882-1.234a.75.75 0 0 1 1.046-.175Zm.95 1.847a.75.75 0 0 1 .75-.75h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1-.75-.75m0 3.969a.75.75 0 0 1 .75-.75h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1-.75-.75m-.775-.738a.75.75 0 1 0-1.22-.872l-.883 1.235l-.212-.212a.75.75 0 0 0-1.06 1.06l.838.838a.75.75 0 0 0 1.14-.094z"/>';

$jenisSurat = [
    ['label' => 'Surat Perintah', 'href' => 'form_surat_perintah.php', 'ready' => true],
    ['label' => 'Surat Izin', 'href' => 'form_surat_ijin.php', 'ready' => true],
    ['label' => 'Nota Dinas', 'href' => 'form_nota_dinas.php', 'ready' => true],
    ['label' => 'Surat Keluar', 'href' => 'form_surat_keluar.php', 'ready' => true],
    ['label' => 'Surat Telegram', 'href' => 'form_surat_telegram.php', 'ready' => false],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pilih Jenis Surat — Sistem Pembuatan Surat Polda Sulsel</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo $BASE_URL; ?>assets/css/style.css">
</head>
<body>

<?php include __DIR__ . '/../includes/header.php'; ?>

<section class="pilih-surat-page">
    <div class="pilih-surat-page__inner">
        <div class="pilih-surat-page__header">
            <h1 class="pilih-surat-page__title">Pilih Jenis Surat</h1>
            <p class="pilih-surat-page__subtitle">Silahkan pilih jenis surat yang akan dibuat</p>
        </div>

        <div class="surat-grid">
            <?php foreach ($jenisSurat as $item): ?>
                <?php $icon = $item['icon'] ?? $defaultIcon; ?>
                <?php if ($item['ready']): ?>
                    <a class="surat-card" href="<?php echo htmlspecialchars($item['href']); ?>">
                        <span class="surat-card__icon">
                            <svg viewBox="0 0 14 14"><?php echo $icon; ?></svg>
                        </span>
                        <h3><?php echo htmlspecialchars($item['label']); ?></h3>
                        <span class="surat-card__btn">
                            Buat Surat
                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                        </span>
                    </a>
                <?php else: ?>
                    <div class="surat-card surat-card--disabled">
                        <span class="surat-card__icon">
                            <svg viewBox="0 0 14 14"><?php echo $icon; ?></svg>
                        </span>
                        <span class="surat-card__soon">Segera Hadir</span>
                        <h3><?php echo htmlspecialchars($item['label']); ?></h3>
                        <span class="surat-card__btn" aria-disabled="true">
                            Buat Surat
                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                        </span>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>