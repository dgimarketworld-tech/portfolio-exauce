-- ══════════════════════════════════════════════════════
--  GTB BANK — Migration v2.0
--  À exécuter une seule fois via phpMyAdmin ou CLI MySQL
-- ══════════════════════════════════════════════════════

-- 1. Users : région, langue, devise, couleur interface, blocage accès
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS region VARCHAR(30) DEFAULT 'europe' AFTER pays,
  ADD COLUMN IF NOT EXISTS langue VARCHAR(10) DEFAULT 'fr' AFTER region,
  ADD COLUMN IF NOT EXISTS devise VARCHAR(10) DEFAULT 'EUR' AFTER langue,
  ADD COLUMN IF NOT EXISTS interface_color VARCHAR(30) DEFAULT 'default' AFTER devise,
  ADD COLUMN IF NOT EXISTS access_blocked TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS access_block_reason TEXT DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS access_block_type ENUM('temporary','permanent') DEFAULT 'permanent',
  ADD COLUMN IF NOT EXISTS access_block_until DATETIME DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS transfer_stop_pct INT DEFAULT 0 COMMENT 'Pourcentage arrêt virement 0-100';

-- 2. Comptes : IBAN et BIC stockés
ALTER TABLE comptes
  ADD COLUMN IF NOT EXISTS iban VARCHAR(34) DEFAULT NULL AFTER numero,
  ADD COLUMN IF NOT EXISTS bic VARCHAR(11) DEFAULT NULL AFTER iban,
  ADD COLUMN IF NOT EXISTS plafond_retrait DECIMAL(15,2) DEFAULT 10000.00,
  ADD COLUMN IF NOT EXISTS plafond_virement DECIMAL(15,2) DEFAULT 50000.00,
  ADD COLUMN IF NOT EXISTS plafond_paiement DECIMAL(15,2) DEFAULT 5000.00,
  ADD COLUMN IF NOT EXISTS decouvert_autorise DECIMAL(15,2) DEFAULT 500.00;

-- 3. Transactions : barre de certification
ALTER TABLE transactions
  ADD COLUMN IF NOT EXISTS certification_pct INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS certification_status ENUM('idle','running','frozen','blocked','validated','rejected') DEFAULT 'idle',
  ADD COLUMN IF NOT EXISTS certification_speed ENUM('slow','normal','fast') DEFAULT 'normal',
  ADD COLUMN IF NOT EXISTS certification_message TEXT DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS admin_alerted TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS backdated_at DATETIME DEFAULT NULL;

-- 4. Table journal des actions admin
CREATE TABLE IF NOT EXISTS admin_actions (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id      INT UNSIGNED NOT NULL,
  user_id       INT UNSIGNED NOT NULL,
  action_type   VARCHAR(100) NOT NULL,
  action_data   JSON DEFAULT NULL,
  note          TEXT DEFAULT NULL,
  notif_sent    TINYINT(1) DEFAULT 0,
  email_sent    TINYINT(1) DEFAULT 0,
  created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user (user_id),
  INDEX idx_admin (admin_id),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Fix kyc_document_type ENUM
ALTER TABLE users
  MODIFY COLUMN kyc_document_type ENUM('cni_ue','passport','titre_sejour') NULL;

-- 6. Cartes : colonnes manquantes
ALTER TABLE cartes
  ADD COLUMN IF NOT EXISTS paiement_en_ligne TINYINT(1) DEFAULT 1,
  ADD COLUMN IF NOT EXISTS paiement_etranger TINYINT(1) DEFAULT 1,
  ADD COLUMN IF NOT EXISTS plafond DECIMAL(15,2) DEFAULT 3000.00,
  ADD COLUMN IF NOT EXISTS cvv VARCHAR(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS expire_le DATE DEFAULT NULL;
