<?php
$titre = $titre ?? 'Back office';
$menu = $menu ?? '';
$groupeUtilisateurs = in_array($menu, ['utilisateurs', 'utilisateur-ajout', 'par-besoin'], true);
$groupePartenariats = in_array($menu, ['partenaires', 'partenaire-ajout', 'publicites', 'publicite-ajout'], true);
$groupeDons = in_array($menu, ['dons', 'dons-partenaire'], true);
?>
<!DOCTYPE html>
<html lang="fr" class="<?= htmlspecialchars(classesAccessibilite()) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($titre) ?> - Vivora Admin</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css">
  <link rel="stylesheet" href="assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="assets/css/adminlte.min.css">
  <link rel="stylesheet" href="assets/css/vivora-admin.css">
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">

  <nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="Menu"><i class="bi bi-list"></i></a>
        </li>
        <li class="nav-item d-none d-md-block"><a href="dashboard.php" class="nav-link">Tableau de bord</a></li>
        <li class="nav-item d-none d-md-block"><a href="../FrontOffice/index.php" class="nav-link">Voir le site</a></li>
      </ul>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item dropdown user-menu">
          <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
            <img src="assets/img/avatar.png" class="user-image rounded-circle shadow" alt="Avatar">
            <span class="d-none d-md-inline"><?= htmlspecialchars($_SESSION['user']['prenom'] . ' ' . $_SESSION['user']['nom']) ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
            <li class="user-header text-bg-vivora">
              <img src="assets/img/avatar.png" class="rounded-circle shadow" alt="Avatar">
              <p>
                <?= htmlspecialchars($_SESSION['user']['prenom'] . ' ' . $_SESSION['user']['nom']) ?>
                <small><?= htmlspecialchars($_SESSION['user']['email']) ?></small>
              </p>
            </li>
            <li class="user-footer">
              <a href="../FrontOffice/mon-profil.php" class="btn btn-default btn-flat">Mon profil</a>
              <a href="../FrontOffice/deconnexion.php?jeton=<?= htmlspecialchars(getCsrfToken()) ?>" class="btn btn-default btn-flat float-end">Déconnexion</a>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </nav>

  <aside class="app-sidebar sidebar-vivora shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
      <a href="dashboard.php" class="brand-link">
        <img src="assets/img/AdminLTELogo.png" alt="Vivora" class="brand-image opacity-75 shadow">
        <span class="brand-text fw-light">Vivora Admin</span>
      </a>
    </div>
    <div class="sidebar-wrapper">
      <nav class="mt-2">
        <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" data-accordion="false">
          <li class="nav-item">
            <a href="dashboard.php" class="nav-link <?= $menu === 'dashboard' ? 'active' : '' ?>">
              <i class="nav-icon bi bi-speedometer"></i>
              <p>Tableau de bord</p>
            </a>
          </li>

          <li class="nav-header">GESTION</li>

          <li class="nav-item <?= $groupeUtilisateurs ? 'menu-open' : '' ?>">
            <a href="#" class="nav-link <?= $groupeUtilisateurs ? 'active' : '' ?>">
              <i class="nav-icon bi bi-people"></i>
              <p>Utilisateurs <i class="nav-arrow bi bi-chevron-right"></i></p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="utilisateurs-liste.php" class="nav-link <?= $menu === 'utilisateurs' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Liste des utilisateurs</p></a>
              </li>
              <li class="nav-item">
                <a href="utilisateur-ajout.php" class="nav-link <?= $menu === 'utilisateur-ajout' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Ajouter un utilisateur</p></a>
              </li>
              <li class="nav-item">
                <a href="utilisateurs-par-besoin.php" class="nav-link <?= $menu === 'par-besoin' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Utilisateurs par besoin</p></a>
              </li>
            </ul>
          </li>

          <li class="nav-item <?= $groupePartenariats ? 'menu-open' : '' ?>">
            <a href="#" class="nav-link <?= $groupePartenariats ? 'active' : '' ?>">
              <i class="nav-icon bi bi-building"></i>
              <p>Partenariats <i class="nav-arrow bi bi-chevron-right"></i></p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="partenaires-liste.php" class="nav-link <?= $menu === 'partenaires' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Partenaires</p></a>
              </li>
              <li class="nav-item">
                <a href="partenaire-ajout.php" class="nav-link <?= $menu === 'partenaire-ajout' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Ajouter un partenaire</p></a>
              </li>
              <li class="nav-item">
                <a href="publicites-liste.php" class="nav-link <?= $menu === 'publicites' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Publicités</p></a>
              </li>
              <li class="nav-item">
                <a href="publicite-ajout.php" class="nav-link <?= $menu === 'publicite-ajout' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Ajouter une publicité</p></a>
              </li>
            </ul>
          </li>

          <li class="nav-item <?= $groupeDons ? 'menu-open' : '' ?>">
            <a href="#" class="nav-link <?= $groupeDons ? 'active' : '' ?>">
              <i class="nav-icon bi bi-heart"></i>
              <p>Dons <i class="nav-arrow bi bi-chevron-right"></i></p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="dons-liste.php" class="nav-link <?= $menu === 'dons' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Liste des dons</p></a>
              </li>
              <li class="nav-item">
                <a href="dons-par-partenaire.php" class="nav-link <?= $menu === 'dons-partenaire' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Dons par partenaire</p></a>
              </li>
            </ul>
          </li>
        </ul>
      </nav>
    </div>
  </aside>

  <main class="app-main">
    <div class="app-content-header">
      <div class="container-fluid">
        <div class="row">
          <div class="col-sm-6"><h3 class="mb-0"><?= htmlspecialchars($titre) ?></h3></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-end">
              <li class="breadcrumb-item"><a href="dashboard.php">Accueil</a></li>
              <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($titre) ?></li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <div class="app-content">
      <div class="container-fluid">
        <?php if (!empty($_SESSION['message'])): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
          </div>
          <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['erreur'])): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['erreur']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
          </div>
          <?php unset($_SESSION['erreur']); ?>
        <?php endif; ?>
