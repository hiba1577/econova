DROP DATABASE IF EXISTS vivora_db;
CREATE DATABASE vivora_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vivora_db;

CREATE TABLE utilisateur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    telephone VARCHAR(8) NOT NULL,
    role ENUM('utilisateur','admin') NOT NULL DEFAULT 'utilisateur',
    statut ENUM('actif','bloque') NOT NULL DEFAULT 'actif',
    date_inscription DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE profil_accessibilite (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    besoin_visuel TINYINT(1) NOT NULL DEFAULT 0,
    besoin_moteur TINYINT(1) NOT NULL DEFAULT 0,
    besoin_auditif TINYINT(1) NOT NULL DEFAULT 0,
    taille_texte ENUM('normal','grand','tres-grand') NOT NULL DEFAULT 'normal',
    contraste_fort TINYINT(1) NOT NULL DEFAULT 0,
    lecture_facile TINYINT(1) NOT NULL DEFAULT 0,
    CONSTRAINT fk_profil_utilisateur FOREIGN KEY (user_id) REFERENCES utilisateur(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE partenaire (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    type ENUM('association','clinique','pharmacie','sponsor') NOT NULL,
    email VARCHAR(150) NOT NULL,
    telephone VARCHAR(8) NOT NULL,
    adresse VARCHAR(255) NOT NULL,
    description TEXT,
    logo VARCHAR(255) DEFAULT NULL,
    statut ENUM('actif','inactif') NOT NULL DEFAULT 'actif',
    date_debut DATE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE publicite (
    id INT AUTO_INCREMENT PRIMARY KEY,
    partenaire_id INT NOT NULL,
    titre VARCHAR(150) NOT NULL,
    description TEXT,
    image VARCHAR(255) DEFAULT NULL,
    lien VARCHAR(255) NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    statut ENUM('active','inactive') NOT NULL DEFAULT 'active',
    CONSTRAINT fk_publicite_partenaire FOREIGN KEY (partenaire_id) REFERENCES partenaire(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE don (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT DEFAULT NULL,
    partenaire_id INT NOT NULL,
    type_don ENUM('financier','physique') NOT NULL DEFAULT 'financier',
    montant DECIMAL(10,2) DEFAULT NULL,
    date_don DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    moyen_paiement ENUM('carte','virement','especes') DEFAULT NULL,
    message TEXT,
    statut ENUM('en_attente','valide','refuse','recu') NOT NULL DEFAULT 'en_attente',
    CONSTRAINT fk_don_utilisateur FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id) ON DELETE SET NULL,
    CONSTRAINT fk_don_partenaire FOREIGN KEY (partenaire_id) REFERENCES partenaire(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE don_physique (
    id INT AUTO_INCREMENT PRIMARY KEY,
    don_id INT NOT NULL UNIQUE,
    categorie ENUM('sang','equipement','medicament','service','autre') NOT NULL,
    groupe_sanguin ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-') DEFAULT NULL,
    date_disponibilite DATE DEFAULT NULL,
    type_equipement ENUM('fauteuil_roulant','bequilles','deambulateur','lit_medicalise','appareil_auditif','lunettes','autre') DEFAULT NULL,
    etat ENUM('neuf','bon','usage') DEFAULT NULL,
    quantite INT DEFAULT NULL,
    nom_medicament VARCHAR(100) DEFAULT NULL,
    date_expiration DATE DEFAULT NULL,
    emballage_ferme TINYINT(1) DEFAULT NULL,
    domaine_service ENUM('medecin','infirmier','kinesitherapeute','psychologue','aide_domicile','accompagnement','transport','autre') DEFAULT NULL,
    disponibilites VARCHAR(100) DEFAULT NULL,
    heures_par_mois INT DEFAULT NULL,
    mode_remise ENUM('depot','recuperation') DEFAULT NULL,
    adresse_recuperation VARCHAR(200) DEFAULT NULL,
    description TEXT,
    autre_article TEXT,
    CONSTRAINT fk_don_physique_don FOREIGN KEY (don_id) REFERENCES don(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO utilisateur (id, nom, prenom, email, mot_de_passe, telephone, role, statut, date_inscription) VALUES
(1, 'Vivora', 'Admin', 'admin@vivora.tn', '$2y$10$ZvGRbDH504zs4fj.mCfk7.lq4qpzmNbG5o5ZfciQRJf9R2qqmDfNe', '71000000', 'admin', 'actif', '2026-09-01 09:00:00'),
(2, 'Ben Ali', 'Sami', 'sami.benali@vivora.tn', '$2y$10$9tyVviWMwH2oEoUia.BMCeMDIWbxf5C9Fl5ReP/GUlpSGnMihMrjK', '22123456', 'utilisateur', 'actif', '2026-09-15 10:30:00'),
(3, 'Trabelsi', 'Amira', 'amira.trabelsi@vivora.tn', '$2y$10$9tyVviWMwH2oEoUia.BMCeMDIWbxf5C9Fl5ReP/GUlpSGnMihMrjK', '55234567', 'utilisateur', 'actif', '2026-10-02 14:00:00'),
(4, 'Gharbi', 'Youssef', 'youssef.gharbi@vivora.tn', '$2y$10$9tyVviWMwH2oEoUia.BMCeMDIWbxf5C9Fl5ReP/GUlpSGnMihMrjK', '98345678', 'utilisateur', 'bloque', '2026-10-10 16:45:00'),
(5, 'Mansouri', 'Nour', 'nour.mansouri@vivora.tn', '$2y$10$9tyVviWMwH2oEoUia.BMCeMDIWbxf5C9Fl5ReP/GUlpSGnMihMrjK', '29456789', 'utilisateur', 'actif', '2026-11-05 11:15:00');

INSERT INTO profil_accessibilite (user_id, besoin_visuel, besoin_moteur, besoin_auditif, taille_texte, contraste_fort, lecture_facile) VALUES
(1, 0, 0, 0, 'normal', 0, 0),
(2, 1, 0, 0, 'grand', 1, 0),
(3, 0, 1, 0, 'normal', 0, 0),
(4, 0, 0, 1, 'normal', 0, 1),
(5, 1, 0, 1, 'tres-grand', 0, 1);

INSERT INTO partenaire (id, nom, type, email, telephone, adresse, description, logo, statut, date_debut) VALUES
(1, 'Association Pas à Pas', 'association', 'contact@pasapas.tn', '71111222', '12 rue de la Liberté, Tunis', 'Association qui accompagne les personnes à mobilité réduite dans leurs activités sportives.', NULL, 'actif', '2026-09-01'),
(2, 'Clinique Horizon Santé', 'clinique', 'accueil@horizon-sante.tn', '71333444', 'Avenue Habib Bourguiba, Ariana', 'Clinique avec des cabinets accessibles et un accueil en langue des signes.', NULL, 'actif', '2026-09-10'),
(3, 'Pharmacie du Lac Vert', 'pharmacie', 'pharmacie@lacvert.tn', '71555666', '5 rue du Lac, Les Berges du Lac', 'Pharmacie de garde avec livraison à domicile pour les personnes âgées.', NULL, 'actif', '2026-10-01'),
(4, 'BienEtre Sport', 'sponsor', 'sponsor@bienetresport.tn', '71777888', 'Zone industrielle, La Marsa', 'Marque de matériel de sport adapté, sponsor des événements Vivora.', NULL, 'inactif', '2026-10-15');

INSERT INTO publicite (partenaire_id, titre, description, image, lien, date_debut, date_fin, statut) VALUES
(1, 'Marche solidaire du dimanche', 'Une marche adaptée de 3 km ouverte à tous, avec accompagnateurs.', 'pub1.jpg', 'https://www.example.com/marche', DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'active'),
(2, 'Bilan de santé à prix réduit', 'Un bilan complet dans un cabinet accessible, sur rendez-vous.', 'pub2.jpg', 'https://www.example.com/bilan', DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 60 DAY), 'active'),
(3, 'Livraison gratuite de médicaments', 'Livraison gratuite pour les personnes de plus de 65 ans.', 'pub3.jpg', 'https://www.example.com/livraison', DATE_SUB(CURDATE(), INTERVAL 2 DAY), DATE_ADD(CURDATE(), INTERVAL 20 DAY), 'active'),
(4, 'Nouveau fauteuil de sport', 'Découvrez le fauteuil de sport léger de BienEtre Sport.', 'pub4.jpg', 'https://www.example.com/fauteuil', DATE_SUB(CURDATE(), INTERVAL 40 DAY), DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'inactive');

INSERT INTO don (utilisateur_id, partenaire_id, montant, date_don, moyen_paiement, message, statut) VALUES
(2, 1, 50.00, '2026-10-05 10:00:00', 'carte', 'Bon courage pour la marche !', 'valide'),
(3, 1, 20.00, '2026-10-12 15:30:00', 'especes', NULL, 'valide'),
(2, 2, 100.00, '2026-10-20 09:15:00', 'virement', 'Pour le matériel accessible.', 'en_attente'),
(5, 3, 30.00, '2026-11-01 18:00:00', 'carte', 'Merci pour la livraison.', 'valide'),
(3, 2, 15.00, '2026-11-08 12:45:00', 'carte', NULL, 'refuse'),
(5, 1, 75.00, '2026-11-15 17:20:00', 'virement', 'Continuez comme ça.', 'en_attente');

INSERT INTO don (id, utilisateur_id, partenaire_id, type_don, montant, date_don, moyen_paiement, message, statut) VALUES
(7, 2, 2, 'physique', NULL, '2026-11-10 09:30:00', NULL, NULL, 'en_attente'),
(8, 5, 2, 'physique', NULL, '2026-10-25 08:45:00', NULL, NULL, 'recu'),
(9, 3, 1, 'physique', NULL, '2026-11-03 14:10:00', NULL, NULL, 'valide'),
(10, 2, 1, 'physique', NULL, '2026-11-12 16:00:00', NULL, NULL, 'en_attente'),
(11, 5, 3, 'physique', NULL, '2026-11-06 11:20:00', NULL, NULL, 'recu'),
(12, 3, 2, 'physique', NULL, '2026-11-14 10:00:00', NULL, NULL, 'valide'),
(13, 2, 1, 'physique', NULL, '2026-11-16 18:30:00', NULL, NULL, 'en_attente');

INSERT INTO don_physique (don_id, categorie, groupe_sanguin, date_disponibilite, type_equipement, etat, quantite, nom_medicament, date_expiration, emballage_ferme, domaine_service, disponibilites, heures_par_mois, mode_remise, adresse_recuperation, description, autre_article) VALUES
(7, 'sang', 'O+', DATE_ADD(CURDATE(), INTERVAL 5 DAY), NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'sang', 'A-', '2026-10-28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 'equipement', NULL, NULL, 'fauteuil_roulant', 'bon', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'recuperation', '8 rue des Roses, Ariana', NULL, 'Un coussin anti-escarres avec le fauteuil.'),
(10, 'equipement', NULL, NULL, 'bequilles', 'neuf', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'depot', NULL, NULL, NULL),
(11, 'medicament', NULL, NULL, NULL, NULL, 3, 'Doliprane 1000 mg', DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 'service', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'infirmier', 'lundi,mercredi,samedi', 10, NULL, NULL, 'Infirmière diplômée, 5 ans en soins à domicile.', NULL),
(13, 'autre', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Un lot de livres en braille et de jeux de société adaptés.', 'Une loupe électronique de lecture.');
