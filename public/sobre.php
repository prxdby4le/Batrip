<?php
$pageTitle = 'Sobre a Batrip';
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/legacy-redirect.php';
// Redireciona para rota limpa (opcional via LEGACY_REDIRECTS=1)
legacy_redirect_if_enabled('sobre');
// Base href disponível via head.php
$baseHref = $baseHref ?? '/';
?>
<body>
    <?php require_once __DIR__ . '/../includes/nav.php'; ?>
    <div class="navbar-space"></div>
    <?php require_once __DIR__ . '/../includes/cart-sidebar.php'; ?>
    <section class="section pt-5 pb-5">
        <div class="container">
            <div class="row align-items-center flex-column-reverse flex-md-row">
                <div class="col-md-6 mb-4 mb-md-0">
                    <h1 class="section-title mb-4">Sobre a Batrip</h1>
                    <p class="lead" style="color:var(--text-gray); font-size:1.2rem;">
                        "A batrip surgiu da minha necessidade de vestir algo diferente. Eu procurava  roupas que me agradassem e nunca tava satisfeito, entao chegou uma hora que eu cansei de esperar alguem ler minha mente e fui <strong>eu mesmo</strong> fazer as roupas que eu queria usar!
                        Quando eu mostrei pra uns amigos, inclusive o Lucca, todo mundo disse que queria uma também, entao eu decidi juntar o util ao agradavel e <strong>criei a marca</strong>. Bglh deu <strong>sold out</strong> no primeiro drop e eu fiquei muito animado.
                        Meus planos pro futuro são simples, nem penso no dinheiro: eu quero dar vida aos designs de conjunto que eu tenho, to com a máquina de costura e trampando pra tornar isso realidade o quanto antes, pq é outra fita que a rapazeada que viu enche o saco pra eu soltar, e com razao, pq modestia a parte, os design tão mto lindo! <strong>Meu foco na marca é unir moda e musica.</strong>"
                    </p>
                </div>
                <div class="col-md-6 d-flex justify-content-center">
                    <img src="<?= htmlspecialchars($baseHref) ?>assets/img/pradasoueu.jpg" alt="Sobre a Batrip" class="img-fluid rounded shadow" style="max-height:340px; object-fit:cover; width:100%; max-width:400px;">
                </div>
            </div>
        </div>
    </section>
    <section class="section pt-0 pb-5">
        <div class="container">
            <h2 class="section-title mb-4">Referências</h2>
            <div class="row g-3 gallery-batrip">
                <div class="col-6 col-md-3">
                    <div class="gallery-img-wrap"><img src="<?= htmlspecialchars($baseHref) ?>assets/materials/forma um morcego, um coração e uma folha de diamba, fazendo referência aos trocadilhos do nome/3.png" class="img-fluid rounded gallery-img" alt="Trocadilho do nome"></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="gallery-img-wrap"><img src="<?= htmlspecialchars($baseHref) ?>assets/materials/forma um morcego, um coração e uma folha de diamba, fazendo referência aos trocadilhos do nome/1.png" class="img-fluid rounded gallery-img" alt="Ref da logo icon da batrip"></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="gallery-img-wrap"><img src="<?= htmlspecialchars($baseHref) ?>assets/materials/forma um morcego, um coração e uma folha de diamba, fazendo referência aos trocadilhos do nome/2.png" class="img-fluid rounded gallery-img" alt="Ref da logo oficial da batrip"></div>
                </div>
            </div>
        </div>
    </section>
    <section class="section pt-0 pb-5">
        <div class="container">
            <h2 class="section-title mb-4">Idealizador</h2>
            <div class="row align-items-center justify-content-center">
                <div class="col-md-4 d-flex justify-content-center mb-4 mb-md-0">
                    <div class="prada-card text-center p-4 rounded shadow">
                        <div class="prada-avatar mx-auto mb-3">
                            <img src="<?= htmlspecialchars($baseHref) ?>assets/img/pradasoueu.jpg" alt="Prada" class="img-fluid rounded-circle" style="width:140px; height:140px; object-fit:cover; border:4px solid var(--accent-red);">
                        </div>
                        <h3 class="artist-name mb-1">Prada</h3>
                        <p class="artist-genre mb-2">Fundador, Artista &amp; Diretor Criativo</p>
                        <p style="color:var(--text-gray); font-size:1.05rem;">
                            "A Batrip é mais do que roupa, é sobre criar pontes entre arte, música e atitude. Cada peça carrega um pouco da nossa história e da energia de quem faz parte desse movimento."
                        </p>
                        <div class="mt-2">
                            <a href="https://x.com/pradasoueu" class="social-icon" target="_blank"><i class="fab fa-twitter"></i></a>
                            <a href="https://www.instagram.com/batrip___/" class="social-icon" target="_blank"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php include '../includes/footer.php'; ?>
    <?php include '../includes/scripts.php'; ?>
</body>
</html>

