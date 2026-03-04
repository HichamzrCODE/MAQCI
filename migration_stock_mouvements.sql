-- Migration: Systèmes de mouvements de stock
-- Date: 2026-03-04
-- Tables: mouvements_stock, transferts_stock, transferts_stock_lignes, receptions_fournisseur, receptions_fournisseur_lignes

-- Mouvements de stock (journal d'audit des entrées/sorties)
CREATE TABLE IF NOT EXISTS `mouvements_stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) NOT NULL,
  `depot_id` int(11) NOT NULL,
  `type` enum('ENTREE','SORTIE') NOT NULL,
  `quantite` int(11) NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_article` (`article_id`),
  KEY `idx_depot` (`depot_id`),
  KEY `idx_reference` (`reference_type`,`reference_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Transferts entre dépôts
CREATE TABLE IF NOT EXISTS `transferts_stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) NOT NULL,
  `depot_source_id` int(11) NOT NULL,
  `depot_destination_id` int(11) NOT NULL,
  `date_transfert` date NOT NULL,
  `user_id` int(11) NOT NULL,
  `statut` enum('brouillon','en_cours','valide') NOT NULL DEFAULT 'brouillon',
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero` (`numero`),
  KEY `idx_depot_source` (`depot_source_id`),
  KEY `idx_depot_dest` (`depot_destination_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Lignes de transfert
CREATE TABLE IF NOT EXISTS `transferts_stock_lignes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transfert_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `quantite` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_transfert` (`transfert_id`),
  KEY `idx_article` (`article_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Réceptions fournisseur
CREATE TABLE IF NOT EXISTS `receptions_fournisseur` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) NOT NULL,
  `fournisseur_id` int(11) NOT NULL,
  `depot_id` int(11) NOT NULL,
  `date_reception` date NOT NULL,
  `user_id` int(11) NOT NULL,
  `statut` enum('brouillon','recue','validee') NOT NULL DEFAULT 'brouillon',
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero` (`numero`),
  KEY `idx_fournisseur` (`fournisseur_id`),
  KEY `idx_depot` (`depot_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Lignes de réception
CREATE TABLE IF NOT EXISTS `receptions_fournisseur_lignes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reception_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `quantite_commandee` int(11) NOT NULL,
  `quantite_recue` int(11) NOT NULL DEFAULT 0,
  `prix_unitaire` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_reception` (`reception_id`),
  KEY `idx_article` (`article_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
