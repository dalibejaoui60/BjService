-- ============================================================
-- migration_chat.sql — À exécuter APRÈS avoir importé bdmf.sql
-- Corrige les types de colonnes + ajoute regrouper & messages
-- ============================================================
USE bdmf;

-- 1) Corrections de types (bugs du schéma original)
ALTER TABLE service   MODIFY idS INT NOT NULL AUTO_INCREMENT;
ALTER TABLE service   MODIFY nomS VARCHAR(100) NOT NULL;      -- était INT par erreur
ALTER TABLE panier    MODIFY idPa INT NOT NULL AUTO_INCREMENT;
ALTER TABLE commande  MODIFY numCde INT NOT NULL AUTO_INCREMENT;
ALTER TABLE promotion MODIFY idpromo INT NOT NULL AUTO_INCREMENT;
ALTER TABLE publication MODIFY idPub INT NOT NULL AUTO_INCREMENT;
ALTER TABLE publication MODIFY titrePub VARCHAR(150) NOT NULL; -- était INT par erreur

-- 1b) Colonnes pour "Se souvenir de moi"
ALTER TABLE utilisateur ADD COLUMN remember_token VARCHAR(64) NULL;
ALTER TABLE utilisateur ADD COLUMN remember_expires DATETIME NULL;

-- 2) Table Regrouper (association Panier <-> Service, du diagramme de classe)
-- (panier/service étaient en MyISAM à l'origine -> il faut InnoDB pour les clés étrangères)
ALTER TABLE panier  ENGINE=InnoDB;
ALTER TABLE service ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS regrouper (
    idPa      INT NOT NULL,
    idS       INT NOT NULL,
    quantite  INT NOT NULL DEFAULT 1,
    PRIMARY KEY (idPa, idS),
    FOREIGN KEY (idPa) REFERENCES panier(idPa)  ON DELETE CASCADE,
    FOREIGN KEY (idS)  REFERENCES service(idS)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3) Table Messages (chat client <-> admin, lié à un panier/commande)
CREATE TABLE IF NOT EXISTS messages (
    idMsg       INT NOT NULL AUTO_INCREMENT,
    idPa        INT NOT NULL,
    expediteur  ENUM('client','admin') NOT NULL,
    contenu     TEXT NOT NULL,
    dateEnvoi   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (idMsg),
    FOREIGN KEY (idPa) REFERENCES panier(idPa) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3b) Table Réclamations (page contact.php)
CREATE TABLE IF NOT EXISTS reclamation (
    idR         INT NOT NULL AUTO_INCREMENT,
    nomClient   VARCHAR(100) NOT NULL,
    emailClient VARCHAR(100) NOT NULL,
    sujet       VARCHAR(100) NOT NULL,
    message     TEXT NOT NULL,
    statut      ENUM('nouvelle','en cours','traitée') NOT NULL DEFAULT 'nouvelle',
    dateR       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    idU         INT NULL,
    idPa        INT NULL,
    PRIMARY KEY (idR),
    FOREIGN KEY (idU) REFERENCES utilisateur(idU) ON DELETE SET NULL,
    FOREIGN KEY (idPa) REFERENCES panier(idPa) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4) Seed : les produits déjà présents sur le site (front-end) → table service
INSERT INTO service (idS, nomS, prixS, imageS, typeS, descriptionS) VALUES
(1, 'Valorant',        10, 'img/valo.png',        'JEUX',    'Top up Valorant Points'),
(2, 'EA Sports FC',     20, 'img/fc.png',           'JEUX',    'Top up EA Sports FC coins'),
(3, '8 Ball Pool',      8,  'img/8 ball p.png',     'JEUX',    'Top up 8 Ball Pool coins'),
(4, 'Free Fire',        10, 'img/free fire.png',    'JEUX',    'Top up Free Fire diamonds'),
(5, 'Logo Createur',    7,  'img/LOGO CRE.png',     'LOGO',    'Création de logo sur mesure'),
(6, 'Affiche Marketing',4,  'img/AFICHE.webp',      'LOGO',    'Création affiche / visuel pub'),
(7, 'Ads Facebook',     10, 'img/facebook.png',     'PUBLICITES',     'Boost publicitaire Facebook'),
(8, 'Ads Instagram',    10, 'img/instagrame.png',   'PUBLICITES',     'Boost publicitaire Instagram'),
(9, 'Ads Youtube',      20, 'img/youtube.png',      'PUBLICITES',     'Boost publicitaire Youtube'),
(10, 'STEG',             0, 'img/STEG.jpg',         'PAIEMENT', 'Paiement facture STEG'),
(11, 'SONEDE',           0, 'img/SONEDE.webp',      'PAIEMENT', 'Paiement facture SONEDE'),
(12, 'Ecole',            0, 'img/ecole.png',        'PAIEMENT', 'Paiement frais scolarité'),
(13, 'Topnet',           0, 'img/topnet.webp',      'PAIEMENT', 'Paiement facture Topnet'),
(14, 'Tunisie Autoroutes', 0, 'img/tunis.webp',     'PAIEMENT', 'Paiement vignette autoroute')
ON DUPLICATE KEY UPDATE nomS = VALUES(nomS);
