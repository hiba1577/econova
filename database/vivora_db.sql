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
    montant DECIMAL(10,2) NOT NULL,
    date_don DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    moyen_paiement ENUM('carte','virement','especes') NOT NULL,
    message TEXT,
    statut ENUM('en_attente','valide','refuse') NOT NULL DEFAULT 'en_attente',
    CONSTRAINT fk_don_utilisateur FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id) ON DELETE SET NULL,
    CONSTRAINT fk_don_partenaire FOREIGN KEY (partenaire_id) REFERENCES partenaire(id) ON DELETE CASCADE
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
