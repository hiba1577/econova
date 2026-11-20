<?php
$titre = $titre ?? 'Vivora';
$pageActive = $pageActive ?? '';
$connecte = estConnecte();
?>
<!DOCTYPE html>
<html lang="fr" class="<?= htmlspecialchars(classesAccessibilite()) ?>">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title><?= htmlspecialchars($titre) ?> - Vivora</title>
  <meta name="description" content="Vivora : la santé et le bien-être accessibles à tous">
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@300;400;500;600;700&family=Raleway:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.min.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
  <link href="assets/css/vivora.css" rel="stylesheet">
</head>

<body class="<?= $pageActive === 'accueil' ? 'index-page' : 'starter-page-page' ?>">

  <header id="header" class="header sticky-top">

    <div class="topbar d-flex align-items-center">
      <div class="container d-flex justify-content-center justify-content-md-between">
        <div class="contact-info d-flex align-items-center">
          <i class="bi bi-envelope d-flex align-items-center"><a href="mailto:contact@vivora.tn">contact@vivora.tn</a></i>
          <i class="bi bi-phone d-flex align-items-center ms-4"><span>+216 71 000 000</span></i>
        </div>
        <div class="social-links d-none d-md-flex align-items-center">
          <?php if ($connecte): ?>
            <span class="text-white me-2"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['user']['prenom'] . ' ' . $_SESSION['user']['nom']) ?></span>
          <?php else: ?>
            <span class="text-white me-2"><i class="bi bi-universal-access"></i> Santé et bien-être inclusif</span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="branding d-flex align-items-center">
      <div class="container position-relative d-flex align-items-center justify-content-end">
        <a href="index.php" class="logo d-flex align-items-center me-auto">
          <h1 class="sitename">Vivora</h1>
        </a>

        <nav id="navmenu" class="navmenu">
          <ul>
            <li><a href="index.php" class="<?= $pageActive === 'accueil' ? 'active' : '' ?>">Accueil</a></li>
            <li><a href="#">Autres modules</a></li>
            <?php if ($connecte): ?>
              <li><a href="mon-profil.php" class="<?= $pageActive === 'profil' ? 'active' : '' ?>">Mon profil</a></li>
              <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                <li><a href="../BackOffice/dashboard.php">Back office</a></li>
              <?php endif; ?>
              <li><a href="deconnexion.php?jeton=<?= htmlspecialchars(getCsrfToken()) ?>">Déconnexion</a></li>
            <?php else: ?>
              <li><a href="connexion.php" class="<?= $pageActive === 'connexion' ? 'active' : '' ?>">Connexion</a></li>
              <li><a href="inscription.php" class="<?= $pageActive === 'inscription' ? 'active' : '' ?>">Inscription</a></li>
            <?php endif; ?>
          </ul>
          <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>

        <?php if ($connecte): ?>
          <a class="cta-btn d-none d-sm-block" href="mon-profil.php">Mon profil</a>
        <?php else: ?>
          <a class="cta-btn d-none d-sm-block" href="inscription.php">Créer un compte</a>
        <?php endif; ?>
      </div>
    </div>

  </header>

  <main class="main">

    <?php if (isset($sousTitre)): ?>
      <div class="page-title" data-aos="fade">
        <div class="heading">
          <div class="container">
            <div class="row d-flex justify-content-center text-center">
              <div class="col-lg-8">
                <h1><?= htmlspecialchars($titre) ?></h1>
                <p class="mb-0"><?= htmlspecialchars($sousTitre) ?></p>
              </div>
            </div>
          </div>
        </div>
        <nav class="breadcrumbs">
          <div class="container">
            <ol>
              <li><a href="index.php">Accueil</a></li>
              <li class="current"><?= htmlspecialchars($titre) ?></li>
            </ol>
          </div>
        </nav>
      </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['message'])): ?>
      <div class="container mt-4">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <?= htmlspecialchars($_SESSION['message']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
      </div>
      <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['erreur'])): ?>
      <div class="container mt-4">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?= htmlspecialchars($_SESSION['erreur']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
      </div>
      <?php unset($_SESSION['erreur']); ?>
    <?php endif; ?>
