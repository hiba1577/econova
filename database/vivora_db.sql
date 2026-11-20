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

INSERT INTO utilisateur (id, nom, prenom, email, mot_de_passe, telephone, role, statut, date_inscription) VALUES
(1, 'Vivora', 'Admin', 'admin@vivora.tn', '$2y$10$ZvGRbDH504zs4fj.mCfk7.lq4qpzmNbG5o5ZfciQRJf9R2qqmDfNe', '71000000', 'admin', 'actif', '2026-09-01 09:00:00'),
(2, 'Ben Ali', 'Sami', 'sami.benali@vivora.tn', '$2y$10$9tyVviWMwH2oEoUia.BMCeMDIWbxf5C9Fl5ReP/GUlpSGnMihMrjK', '22123456', 'utilisateur', 'actif', '2026-09-15 10:30:00'),
(3, 'Trabelsi', 'Amira', 'amira.trabelsi@vivora.tn', '$2y$10$9tyVviWMwH2oEoUia.BMCeMDIWbxf5C9Fl5ReP/GUlpSGnMihMrjK', '55234567', 'utilisateur', 'actif', '2026-10-02 14:00:00'),
(4, 'Gharbi', 'Youssef', 'youssef.gharbi@vivora.tn', '$2y$10$9tyVviWMwH2oEoUia.BMCeMDIWbxf5C9Fl5ReP/GUlpSGnMihMrjK', '98345678', 'utilisateur', 'bloque', '2026-10-10 16:45:00'),
(5, 'Mansouri', 'Nour', 'nour.mansouri@vivora.tn', '$2y$10$9tyVviWMwH2oEoUia.BMCeMDIWbxf5C9Fl5ReP/GUlpSGnMihMrjK', '29456789', 'utilisateur', 'actif', '2026-11-05 11:15:00');
