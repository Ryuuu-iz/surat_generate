<?php $BASE_URL = ''; ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sistem Pembuatan Surat — Polda Sulawesi Selatan</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo $BASE_URL; ?>assets/css/style.css">
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<section class="hero">
    <canvas id="heroNetwork" class="hero__network"></canvas>
    <div class="hero__content">
        <span class="hero__eyebrow">Bidang Teknologi dan Komunikasi</span>
        <h1 class="hero__title">
            Sistem Pembuatan Surat Administrasi<br>
            Kepolisian Negara Republik Indonesia<br>
            Daerah Sulawesi Selatan
        </h1>
        <p class="hero__subtitle">Bidang Teknologi dan Komunikasi</p>
        <a href="pages/pilih_surat.php" class="btn-gold">Buat Surat</a>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script src="<?php echo $BASE_URL; ?>assets/js/network-bg.js"></script>
</body>
</html>