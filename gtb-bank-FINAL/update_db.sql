-- ════════════════════════════════════════════════════════════════
--  GTB BANK — Mise à jour base existante (sans perte de données)
--  À importer via phpMyAdmin sur la base en production
--  Toutes les opérations sont idempotentes (IF NOT EXISTS)
-- ════════════════════════════════════════════════════════════════

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ── 1. Colonnes manquantes sur USERS ────────────────────────────
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS region             VARCHAR(30)  NOT NULL DEFAULT 'europe' AFTER pays,
  ADD COLUMN IF NOT EXISTS langue             VARCHAR(10)  NOT NULL DEFAULT 'fr'     AFTER region,
  ADD COLUMN IF NOT EXISTS devise             VARCHAR(10)  NOT NULL DEFAULT 'EUR'    AFTER langue,
  ADD COLUMN IF NOT EXISTS interface_color    VARCHAR(30)  NOT NULL DEFAULT 'default' AFTER devise,
  ADD COLUMN IF NOT EXISTS access_blocked     TINYINT(1)   NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS access_block_reason TEXT        NULL,
  ADD COLUMN IF NOT EXISTS access_block_type  ENUM('temporary','permanent') NULL DEFAULT 'permanent',
  ADD COLUMN IF NOT EXISTS access_block_until DATETIME    NULL,
  ADD COLUMN IF NOT EXISTS transfer_stop_pct  INT         NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS client_number      VARCHAR(20) NULL,
  ADD COLUMN IF NOT EXISTS civility           ENUM('M.','Mme') NULL,
  ADD COLUMN IF NOT EXISTS first_name         VARCHAR(80) NULL,
  ADD COLUMN IF NOT EXISTS last_name          VARCHAR(80) NULL;

-- Fix ENUM kyc_document_type
ALTER TABLE users
  MODIFY COLUMN kyc_document_type ENUM('cni_ue','passport','titre_sejour') NULL;

-- ── 2. Colonnes manquantes sur COMPTES ──────────────────────────
ALTER TABLE comptes
  ADD COLUMN IF NOT EXISTS iban               VARCHAR(34)   NULL    AFTER numero,
  ADD COLUMN IF NOT EXISTS bic                VARCHAR(11)   NULL    AFTER iban,
  ADD COLUMN IF NOT EXISTS plafond_retrait    DECIMAL(15,2) NOT NULL DEFAULT 10000.00,
  ADD COLUMN IF NOT EXISTS plafond_virement   DECIMAL(15,2) NOT NULL DEFAULT 50000.00,
  ADD COLUMN IF NOT EXISTS plafond_paiement   DECIMAL(15,2) NOT NULL DEFAULT 5000.00,
  ADD COLUMN IF NOT EXISTS decouvert_autorise DECIMAL(15,2) NOT NULL DEFAULT 500.00;

-- ── 3. Colonnes manquantes sur TRANSACTIONS ─────────────────────
ALTER TABLE transactions
  ADD COLUMN IF NOT EXISTS certification_pct     INT  NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS certification_status  ENUM('idle','running','frozen','blocked','validated','rejected') NOT NULL DEFAULT 'idle',
  ADD COLUMN IF NOT EXISTS certification_speed   ENUM('slow','normal','fast') NOT NULL DEFAULT 'normal',
  ADD COLUMN IF NOT EXISTS certification_message TEXT NULL,
  ADD COLUMN IF NOT EXISTS admin_alerted         TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS backdated_at          DATETIME NULL,
  ADD COLUMN IF NOT EXISTS compte_associe_id     INT UNSIGNED NULL;

-- ── 4. Colonnes manquantes sur CARTES ───────────────────────────
ALTER TABLE cartes
  ADD COLUMN IF NOT EXISTS paiement_en_ligne TINYINT(1)   NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS paiement_etranger TINYINT(1)   NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS plafond           DECIMAL(15,2) NULL DEFAULT 3000.00,
  ADD COLUMN IF NOT EXISTS cvv               VARCHAR(10)   NULL;

-- ── 5. Table admin_actions ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS admin_actions (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id     INT UNSIGNED NOT NULL,
    user_id      INT UNSIGNED NOT NULL,
    action_type  VARCHAR(100) NOT NULL,
    action_data  JSON         NULL,
    note         TEXT         NULL,
    notif_sent   TINYINT(1)   NOT NULL DEFAULT 0,
    email_sent   TINYINT(1)   NOT NULL DEFAULT 0,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user    (user_id),
    INDEX idx_admin   (admin_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 6. Table sms_banking ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS sms_banking (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNSIGNED NOT NULL UNIQUE,
    telephone    VARCHAR(30)  NOT NULL,
    pin          VARCHAR(20)  NOT NULL,
    is_active    TINYINT(1)   NOT NULL DEFAULT 1,
    alert_debit  TINYINT(1)   NOT NULL DEFAULT 1,
    alert_credit TINYINT(1)   NOT NULL DEFAULT 1,
    alert_min    DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_tel (telephone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 7. Table sms_banking_logs ───────────────────────────────────
CREATE TABLE IF NOT EXISTS sms_banking_logs (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    direction  ENUM('IN','OUT') NOT NULL,
    telephone  VARCHAR(30)  NOT NULL,
    contenu    VARCHAR(500) NOT NULL,
    commande   VARCHAR(30)  NOT NULL DEFAULT '',
    statut     ENUM('pending','success','error') NOT NULL DEFAULT 'pending',
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 8. Table user_settings ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS user_settings (
    user_id          INT UNSIGNED  PRIMARY KEY,
    plafond_virement DECIMAL(12,2) NOT NULL DEFAULT 5000.00,
    plafond_paiement DECIMAL(12,2) NOT NULL DEFAULT 2000.00,
    notif_email      TINYINT(1)    NOT NULL DEFAULT 1,
    notif_sms        TINYINT(1)    NOT NULL DEFAULT 1,
    notif_push       TINYINT(1)    NOT NULL DEFAULT 0,
    langue           VARCHAR(10)   NOT NULL DEFAULT 'fr',
    devise           VARCHAR(10)   NOT NULL DEFAULT 'EUR',
    theme            VARCHAR(20)   NOT NULL DEFAULT 'dark',
    updated_at       DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 9. Table investissements ────────────────────────────────────
CREATE TABLE IF NOT EXISTS investissements (
    id              INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED  NOT NULL,
    produit         VARCHAR(80)   NOT NULL,
    type            VARCHAR(60)   NOT NULL DEFAULT 'performance',
    montant_initial DECIMAL(12,2) NOT NULL,
    montant_actuel  DECIMAL(12,2) NOT NULL,
    rendement_pct   DECIMAL(5,2)  NOT NULL DEFAULT 0.00,
    statut          ENUM('actif','cloture','suspendu') NOT NULL DEFAULT 'actif',
    date_debut      DATE          NOT NULL,
    date_fin        DATE          NULL,
    created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ════════════════════════════════════════════════════════════════
--  FIN — Mise à jour terminée, aucune donnée modifiée
-- ════════════════════════════════════════════════════════════════
