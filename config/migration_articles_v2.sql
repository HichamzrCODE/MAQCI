-- Migration Articles v2 - Système de gestion de stock complet
-- Exécuter ce script une seule fois pour migrer la base de données

-- ============================================================
-- 1. TABLE catégories
-- ============================================================
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    parent_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id),
    INDEX idx_nom (nom)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 2. TABLE dépôts
-- ============================================================
CREATE TABLE IF NOT EXISTS depots (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL UNIQUE,
    adresse VARCHAR(255) NULL,
    ville VARCHAR(100) NULL,
    responsable_id INT NULL,
    telephone VARCHAR(20) NULL,
    email VARCHAR(100) NULL,
    statut ENUM('actif','inactif') DEFAULT 'actif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (responsable_id) REFERENCES users(id_users) ON DELETE SET NULL,
    INDEX idx_nom (nom),
    INDEX idx_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 3. MISE À JOUR TABLE articles (ajout nouvelles colonnes)
-- ============================================================

-- Ajout colonne sku (si elle n'existe pas déjà)
ALTER TABLE articles
    ADD COLUMN IF NOT EXISTS sku VARCHAR(50) NULL UNIQUE COMMENT 'Numéro de série/SKU',
    ADD COLUMN IF NOT EXISTS prix_revient DECIMAL(10,2) NULL COMMENT 'Prix d''achat au fournisseur',
    ADD COLUMN IF NOT EXISTS prix_vente DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT 'Prix de vente standard',
    ADD COLUMN IF NOT EXISTS fournisseur_alternatif_id INT NULL,
    ADD COLUMN IF NOT EXISTS poids_kg DECIMAL(10,3) NULL,
    ADD COLUMN IF NOT EXISTS longueur_cm DECIMAL(10,2) NULL,
    ADD COLUMN IF NOT EXISTS largeur_cm DECIMAL(10,2) NULL,
    ADD COLUMN IF NOT EXISTS hauteur_cm DECIMAL(10,2) NULL,
    ADD COLUMN IF NOT EXISTS couleur VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS unite_mesure VARCHAR(20) DEFAULT 'Piece' COMMENT 'Piece, Kg, Litre, Mètre...',
    ADD COLUMN IF NOT EXISTS stock_minimal INT DEFAULT 0,
    ADD COLUMN IF NOT EXISTS stock_maximal INT DEFAULT 0,
    ADD COLUMN IF NOT EXISTS quantite_totale INT DEFAULT 0 COMMENT 'Total tous dépôts',
    ADD COLUMN IF NOT EXISTS image_path VARCHAR(255) NULL,
    ADD COLUMN IF NOT EXISTS categorie_id INT NULL,
    ADD COLUMN IF NOT EXISTS statut ENUM('actif','inactif','discontinued') DEFAULT 'actif',
    ADD COLUMN IF NOT EXISTS updated_by INT NULL,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL COMMENT 'Soft delete',
    ADD COLUMN IF NOT EXISTS notes_internes TEXT NULL;

-- Copier pr vers prix_revient si prix_revient est NULL
UPDATE articles SET prix_revient = pr WHERE prix_revient IS NULL AND pr IS NOT NULL;

-- Ajouter les clés étrangères (si pas déjà présentes)
ALTER TABLE articles
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;

-- Ajout des index pour la performance
ALTER TABLE articles
    ADD INDEX IF NOT EXISTS idx_sku (sku),
    ADD INDEX IF NOT EXISTS idx_fournisseur (fournisseur_id),
    ADD INDEX IF NOT EXISTS idx_statut (statut),
    ADD INDEX IF NOT EXISTS idx_nom (nom_art),
    ADD INDEX IF NOT EXISTS idx_deleted (deleted_at);

-- ============================================================
-- 4. TABLE stock_par_depot
-- ============================================================
CREATE TABLE IF NOT EXISTS stock_par_depot (
    id INT PRIMARY KEY AUTO_INCREMENT,
    article_id INT NOT NULL,
    depot_id INT NOT NULL,
    quantite INT DEFAULT 0 COMMENT 'Quantité physique en dépôt',
    quantite_en_transit INT DEFAULT 0 COMMENT 'Quantité commandée non reçue',
    quantite_bloquee INT DEFAULT 0 COMMENT 'Quantité indisponible',
    emplacement VARCHAR(50) NULL COMMENT 'Position dans le dépôt (A-12-3)',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_article_depot (article_id, depot_id),
    FOREIGN KEY (article_id) REFERENCES articles(id_articles) ON DELETE CASCADE,
    FOREIGN KEY (depot_id) REFERENCES depots(id),
    INDEX idx_depot (depot_id),
    INDEX idx_quantite (quantite)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 5. TABLE historique des prix
-- ============================================================
CREATE TABLE IF NOT EXISTS articles_prix_historique (
    id INT PRIMARY KEY AUTO_INCREMENT,
    article_id INT NOT NULL,
    prix_revient_ancien DECIMAL(10,2) NULL,
    prix_revient_nouveau DECIMAL(10,2) NULL,
    prix_vente_ancien DECIMAL(10,2) NULL,
    prix_vente_nouveau DECIMAL(10,2) NULL,
    changed_by INT NOT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    raison VARCHAR(255) NULL,
    FOREIGN KEY (article_id) REFERENCES articles(id_articles) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id_users) ON DELETE SET NULL,
    INDEX idx_article (article_id),
    INDEX idx_date (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 6. TABLE audit_log
-- ============================================================
CREATE TABLE IF NOT EXISTS audit_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    entity_type VARCHAR(50) NOT NULL COMMENT 'articles, clients, devis...',
    entity_id INT NOT NULL,
    action VARCHAR(20) NOT NULL COMMENT 'CREATE, UPDATE, DELETE, VIEW',
    user_id INT NOT NULL,
    old_values JSON NULL COMMENT 'Valeurs avant modification',
    new_values JSON NULL COMMENT 'Valeurs après modification',
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id_users) ON DELETE SET NULL,
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_user (user_id),
    INDEX idx_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
