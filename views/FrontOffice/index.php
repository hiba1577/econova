<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/PubliciteController.php';

$publiciteController = new PubliciteController();
$publicites = $publiciteController->readPublicitesActives();

$titre = 'Accueil';
$pageActive = 'accueil';
include __DIR__ . '/includes/header.php';
?>

    <section id="hero" class="hero section light-background">
      <img src="assets/img/hero-bg.jpg" alt="" data-aos="fade-in">
      <div class="container position-relative">
        <div class="welcome position-relative" data-aos="fade-down" data-aos-delay="100">
          <h2>BIENVENUE SUR VIVORA</h2>
          <p>Vie + vitalité : la santé et le bien-être accessibles à tous</p>
        </div>

        <div class="content row gy-4">
          <div class="col-lg-4 d-flex align-items-stretch">
            <div class="why-box" data-aos="zoom-out" data-aos-delay="200">
              <h3>Pourquoi Vivora ?</h3>
              <p>Un seul compte et un seul profil d'accessibilité. La taille du texte, le contraste et le mode lecture facile s'adaptent à vos besoins sur toute la plateforme.</p>
              <div class="text-center">
                <?php if (estConnecte()): ?>
                  <a href="mon-profil.php" class="more-btn"><span>Mon profil</span> <i class="bi bi-chevron-right"></i></a>
                <?php else: ?>
                  <a href="inscription.php" class="more-btn"><span>Créer mon compte</span> <i class="bi bi-chevron-right"></i></a>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="col-lg-8 d-flex align-items-stretch">
            <div class="d-flex flex-column justify-content-center">
              <div class="row gy-4">
                <div class="col-xl-4 d-flex align-items-stretch">
                  <div class="icon-box" data-aos="zoom-out" data-aos-delay="300">
                    <i class="bi bi-eye"></i>
                    <h4>Besoin visuel</h4>
                    <p>Texte agrandi et contraste fort pour lire sans effort.</p>
                  </div>
                </div>
                <div class="col-xl-4 d-flex align-items-stretch">
                  <div class="icon-box" data-aos="zoom-out" data-aos-delay="400">
                    <i class="bi bi-person-wheelchair"></i>
                    <h4>Besoin moteur</h4>
                    <p>Une navigation simple, au clavier, sans gestes compliqués.</p>
                  </div>
                </div>
                <div class="col-xl-4 d-flex align-items-stretch">
                  <div class="icon-box" data-aos="zoom-out" data-aos-delay="500">
                    <i class="bi bi-ear"></i>
                    <h4>Besoin auditif</h4>
                    <p>Des informations écrites et claires, sans dépendre du son.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="publicites" class="services section">
      <div class="container section-title" data-aos="fade-up">
        <h2>Offres de nos partenaires</h2>
        <p>Les annonces en cours des associations, cliniques, pharmacies et sponsors de Vivora</p>
      </div>

      <div class="container">
        <div class="row gy-4">
          <?php if (empty($publicites)): ?>
            <p class="text-center">Aucune offre en cours pour le moment.</p>
          <?php endif; ?>
          <?php foreach ($publicites as $pub): ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
              <div class="carte-publicite h-100">
                <?php if (!empty($pub['image'])): ?>
                  <img src="../../uploads/<?= htmlspecialchars($pub['image']) ?>" alt="<?= htmlspecialchars($pub['titre']) ?>" class="img-fluid">
                <?php endif; ?>
                <div class="p-4">
                  <span class="badge badge-vivora mb-2"><?= htmlspecialchars($pub['partenaire_nom']) ?></span>
                  <h3><?= htmlspecialchars($pub['titre']) ?></h3>
                  <p><?= htmlspecialchars($pub['description'] ?? '') ?></p>
                  <p class="small text-muted">Jusqu'au <?= htmlspecialchars(date('d/m/Y', strtotime($pub['date_fin']))) ?></p>
                  <a href="<?= htmlspecialchars($pub['lien']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-vivora btn-sm">En savoir plus</a>
                  <a href="partenaire-detail.php?id=<?= (int) $pub['partenaire_id'] ?>" class="btn btn-outline-vivora btn-sm">Voir le partenaire</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <section id="services" class="services section light-background">
      <div class="container section-title" data-aos="fade-up">
        <h2>Nos services</h2>
        <p>Cinq modules, un même compte utilisateur</p>
      </div>

      <div class="container">
        <div class="row gy-4">
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
            <div class="service-item position-relative">
              <div class="icon"><i class="fas fa-universal-access"></i></div>
              <a href="inscription.php" class="stretched-link"><h3>Profils et accessibilité</h3></a>
              <p>Créez votre compte et réglez vos préférences d'affichage.</p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
            <div class="service-item position-relative">
              <div class="icon"><i class="fas fa-hand-holding-heart"></i></div>
              <a href="partenaires.php" class="stretched-link"><h3>Partenaires et dons</h3></a>
              <p>Soutenez les associations, cliniques et pharmacies partenaires.</p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
            <div class="service-item position-relative">
              <div class="icon"><i class="fas fa-running"></i></div>
              <a href="#services" class="stretched-link"><h3>Activités adaptées</h3></a>
              <p>Sport et bien-être classés par catégorie et par besoin.</p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
            <div class="service-item position-relative">
              <div class="icon"><i class="fas fa-user-md"></i></div>
              <a href="#services" class="stretched-link"><h3>Rendez-vous</h3></a>
              <p>Des professionnels de santé dans des cabinets accessibles.</p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
            <div class="service-item position-relative">
              <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
              <a href="#services" class="stretched-link"><h3>Lieux et événements</h3></a>
              <p>Rampe, ascenseur, parking : l'accessibilité de chaque lieu.</p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="600">
            <div class="service-item position-relative">
              <div class="icon"><i class="fas fa-comments"></i></div>
              <a href="#services" class="stretched-link"><h3>Conseils et communauté</h3></a>
              <p>Des articles de bien-être et un espace d'échange.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
