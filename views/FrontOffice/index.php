<?php
require_once __DIR__ . '/../../session.php';

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
              <p>Un seul compte pour accéder à tous les services de la plateforme : activités, rendez-vous, lieux, événements et communauté.</p>
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
                    <i class="bi bi-person-plus"></i>
                    <h4>Inscription</h4>
                    <p>Créez votre compte en quelques secondes.</p>
                  </div>
                </div>
                <div class="col-xl-4 d-flex align-items-stretch">
                  <div class="icon-box" data-aos="zoom-out" data-aos-delay="400">
                    <i class="bi bi-shield-lock"></i>
                    <h4>Sécurité</h4>
                    <p>Mot de passe haché, session protégée et jeton CSRF.</p>
                  </div>
                </div>
                <div class="col-xl-4 d-flex align-items-stretch">
                  <div class="icon-box" data-aos="zoom-out" data-aos-delay="500">
                    <i class="bi bi-person-gear"></i>
                    <h4>Mon profil</h4>
                    <p>Modifiez vos informations et votre mot de passe à tout moment.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="services" class="services section light-background">
      <div class="container section-title" data-aos="fade-up">
        <h2>Nos services</h2>
        <p>Plusieurs modules, un même compte utilisateur</p>
      </div>

      <div class="container">
        <div class="row gy-4">
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
            <div class="service-item position-relative">
              <div class="icon"><i class="fas fa-user"></i></div>
              <a href="inscription.php" class="stretched-link"><h3>Comptes utilisateurs</h3></a>
              <p>Inscription, connexion et gestion de votre profil.</p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
            <div class="service-item position-relative">
              <div class="icon"><i class="fas fa-th-large"></i></div>
              <a href="#" class="stretched-link"><h3>Autres modules</h3></a>
              <p>Les autres services de Vivora, développés par le groupe.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
