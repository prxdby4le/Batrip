<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre a Batrip</title>
    <link rel="icon" href="../materials/batrip symbol.png" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <?php require_once __DIR__ . '/../includes/header.php'; ?>

    <div class="navbar-space"></div>
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
                    <img src="../img/pradasoueu.jpg" alt="Sobre a Batrip" class="img-fluid rounded shadow" style="max-height:340px; object-fit:cover; width:100%; max-width:400px;">
                </div>
            </div>
        </div>
    </section>
    <section class="section pt-0 pb-5">
        <div class="container">
            <h2 class="section-title mb-4">Referências</h2>
            <div class="row g-3 gallery-batrip">
                <div class="col-6 col-md-3">
                    <div class="gallery-img-wrap"><img src="../materials/forma um morcego, um coração e uma folha de diamba, fazendo referência aos trocadilhos do nome/3.png" class="img-fluid rounded gallery-img" alt="Trocadilho do nome"></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="gallery-img-wrap"><img src="../materials/forma um morcego, um coração e uma folha de diamba, fazendo referência aos trocadilhos do nome/1.png" class="img-fluid rounded gallery-img" alt="Ref da logo icon da batrip"></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="gallery-img-wrap"><img src="../materials/forma um morcego, um coração e uma folha de diamba, fazendo referência aos trocadilhos do nome/2.png" class="img-fluid rounded gallery-img" alt="Ref da logo oficial da batrip"></div>
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
                            <img src="../img/pradasoueu.jpg" alt="Prada" class="img-fluid rounded-circle" style="width:140px; height:140px; object-fit:cover; border:4px solid var(--accent-red);">
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
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h3 class="footer-title">Batrip</h3>
                    <p style="color: var(--text-gray);">
                        Exclusividade • Sonoridade • Autenticidade<br>
                        A marca favorita do seu artista favorito.
                    </p>
                </div>
                <div class="col-md-4">
                    <h4 class="footer-title">Contato</h4>
                    <a href="mailto:contato@batrip.com" class="footer-link">
                        <i class="fas fa-envelope"></i> contato@batrip.com
                    </a>
                    <a href="tel:+5511123456789" class="footer-link">
                        <i class="fas fa-phone"></i> (11) 123456789
                    </a>
                    <p class="footer-link">
                        <i class="fas fa-map-marker-alt"></i> São Paulo, SP
                    </p>
                </div>
                <div class="col-md-4">
                    <h4 class="footer-title">Redes Sociais</h4>
                    <div>
                        <a href="https://www.instagram.com/batrip___/" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="https://x.com/pradasoueu" class="social-icon"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
            <hr style="border-color: var(--border-gray); margin: 2rem 0 1rem;">
            <div class="text-center">
                <p style="color: var(--text-gray); margin: 0;">
                    © 2025 BATRIP. Todos os direitos reservados.
                </p>
            </div>
        </div>
    </footer>
    <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="/Batrip/scripts/script.js"></script>
</body>
</html>
