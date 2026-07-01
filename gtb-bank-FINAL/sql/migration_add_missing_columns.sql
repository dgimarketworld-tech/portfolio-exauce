-- ================================================================
--  GTB BANK — Migration : ajout des colonnes manquantes
--  À exécuter UNE SEULE FOIS dans phpMyAdmin > Onglet SQL
--  Toutes les colonnes utilisent IF NOT EXISTS : safe à re-exécuter
-- ================================================================

-- ────────────────────────────────────────────────────────────────
--  TABLE users — toutes les colonnes manquantes
-- ────────────────────────────────────────────────────────────────

ALTER TABLE users
  -- Identité
  ADD COLUMN IF NOT EXISTS first_name           VARCHAR(80)   NULL                                          AFTER nom,
  ADD COLUMN IF NOT EXISTS last_name            VARCHAR(80)   NULL                                          AFTER first_name,
  ADD COLUMN IF NOT EXISTS client_number        VARCHAR(20)   NULL                                          AFTER last_name,
  ADD COLUMN IF NOT EXISTS civility             ENUM('M.','Mme') NULL                                      AFTER client_number,
  -- Statut compte
  ADD COLUMN IF NOT EXISTS status               ENUM('active','suspended','closed') NOT NULL DEFAULT 'active' AFTER is_active,
  ADD COLUMN IF NOT EXISTS kyc_status           ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending' AFTER status,
  ADD COLUMN IF NOT EXISTS kyc_document_type    ENUM('cni_ue','passport','titre_sejour') NULL              AFTER kyc_status,
  -- Auth & sécurité
  ADD COLUMN IF NOT EXISTS two_fa_enabled       TINYINT(1)    NOT NULL DEFAULT 0                           AFTER kyc_document_type,
  ADD COLUMN IF NOT EXISTS must_change_password TINYINT(1)    NOT NULL DEFAULT 0                           AFTER two_fa_enabled,
  ADD COLUMN IF NOT EXISTS security_level       TINYINT       NOT NULL DEFAULT 1                           AFTER must_change_password,
  ADD COLUMN IF NOT EXISTS session_token        VARCHAR(64)   NULL                                          AFTER security_level,
  ADD COLUMN IF NOT EXISTS last_login_ip        VARCHAR(45)   NULL                                          AFTER last_login_at,
  ADD COLUMN IF NOT EXISTS failed_logins        TINYINT       NOT NULL DEFAULT 0                           AFTER last_login_ip,
  -- Avatar & préférences visuelles
  ADD COLUMN IF NOT EXISTS avatar_url           VARCHAR(255)  NULL                                          AFTER failed_logins,
  ADD COLUMN IF NOT EXISTS interface_color      VARCHAR(20)   NOT NULL DEFAULT 'gold'                      AFTER avatar_url,
  ADD COLUMN IF NOT EXISTS pref_theme           VARCHAR(20)   NOT NULL DEFAULT 'light'                     AFTER interface_color,
  -- Langue & région
  ADD COLUMN IF NOT EXISTS language             CHAR(5)       NOT NULL DEFAULT 'fr'                        AFTER pref_theme,
  ADD COLUMN IF NOT EXISTS langue               CHAR(5)       NOT NULL DEFAULT 'fr'                        AFTER language,
  -- NOTE : region existe déjà dans votre DB (europe|northam|latam) — pas besoin de l'ajouter
  -- Devise préférée
  ADD COLUMN IF NOT EXISTS devise               CHAR(3)       NOT NULL DEFAULT 'EUR'                       AFTER langue,
  -- Alertes
  ADD COLUMN IF NOT EXISTS email_alerts         TINYINT(1)    NOT NULL DEFAULT 1                           AFTER devise,
  ADD COLUMN IF NOT EXISTS pref_email_alerts    TINYINT(1)    NOT NULL DEFAULT 1                           AFTER email_alerts,
  ADD COLUMN IF NOT EXISTS pref_sms_alerts      TINYINT(1)    NOT NULL DEFAULT 0                           AFTER pref_email_alerts,
  -- Blocage admin
  ADD COLUMN IF NOT EXISTS access_blocked       TINYINT(1)    NOT NULL DEFAULT 0                           AFTER pref_sms_alerts,
  ADD COLUMN IF NOT EXISTS access_block_reason  VARCHAR(255)  NULL                                          AFTER access_blocked,
  ADD COLUMN IF NOT EXISTS access_block_type    ENUM('permanent','temporary') NULL                         AFTER access_block_reason,
  ADD COLUMN IF NOT EXISTS access_block_until   DATETIME      NULL                                          AFTER access_block_type,
  ADD COLUMN IF NOT EXISTS transfer_stop_pct    TINYINT(1)    NOT NULL DEFAULT 0                           AFTER access_block_until;

-- Index unique sur client_number (si absent)
ALTER TABLE users
  ADD UNIQUE INDEX IF NOT EXISTS idx_client_number (client_number);

-- Synchroniser les colonnes depuis leurs équivalents existants
UPDATE users SET
  first_name    = COALESCE(first_name,  prenom),
  last_name     = COALESCE(last_name,   nom),
  status        = COALESCE(status,      IF(is_active = 1, 'active', 'suspended')),
  language      = COALESCE(language, langue, 'fr'),
  langue        = COALESCE(langue, language, 'fr'),
  email_alerts  = COALESCE(email_alerts, pref_email_alerts, 1),
  pref_email_alerts = COALESCE(pref_email_alerts, email_alerts, 1)
WHERE first_name IS NULL OR last_name IS NULL;

-- ────────────────────────────────────────────────────────────────
--  TABLE comptes — colonnes manquantes
-- ────────────────────────────────────────────────────────────────

ALTER TABLE comptes
  ADD COLUMN IF NOT EXISTS iban               VARCHAR(34)   NULL                                AFTER numero,
  ADD COLUMN IF NOT EXISTS bic                VARCHAR(11)   NULL                                AFTER iban,
  ADD COLUMN IF NOT EXISTS taux_interet       DECIMAL(5,2)  NOT NULL DEFAULT 0.00              AFTER devise,
  ADD COLUMN IF NOT EXISTS decouvert_autorise DECIMAL(15,2) NOT NULL DEFAULT 0.00              AFTER taux_interet,
  ADD COLUMN IF NOT EXISTS plafond_virement   DECIMAL(15,2) NULL                               AFTER decouvert_autorise,
  ADD COLUMN IF NOT EXISTS plafond_paiement   DECIMAL(15,2) NULL                               AFTER plafond_virement,
  ADD COLUMN IF NOT EXISTS plafond_retrait    DECIMAL(15,2) NULL                               AFTER plafond_paiement,
  ADD COLUMN IF NOT EXISTS cree_le            DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER ouvert_le;

-- Remplir iban/bic depuis les données existantes
UPDATE comptes SET
  iban    = COALESCE(iban, numero),
  bic     = COALESCE(bic,  'GTBKFRPPXXX'),
  cree_le = COALESCE(cree_le, ouvert_le)
WHERE iban IS NULL OR bic IS NULL OR cree_le IS NULL;

-- ────────────────────────────────────────────────────────────────
--  TABLE admins — colonnes manquantes
-- ────────────────────────────────────────────────────────────────

ALTER TABLE admins
  ADD COLUMN IF NOT EXISTS last_login_ip  VARCHAR(45) NULL       AFTER last_login_at,
  ADD COLUMN IF NOT EXISTS failed_logins  TINYINT     NOT NULL DEFAULT 0 AFTER last_login_ip;

-- ────────────────────────────────────────────────────────────────
--  TABLE otp_codes — colonnes manquantes
-- ────────────────────────────────────────────────────────────────

ALTER TABLE otp_codes
  ADD COLUMN IF NOT EXISTS admin_id      INT UNSIGNED NULL           AFTER user_id,
  ADD COLUMN IF NOT EXISTS purpose       VARCHAR(40)  NOT NULL DEFAULT 'login' AFTER admin_id,
  ADD COLUMN IF NOT EXISTS channel       ENUM('email','sms') NOT NULL DEFAULT 'email' AFTER purpose,
  ADD COLUMN IF NOT EXISTS recipient     VARCHAR(190) NULL           AFTER channel,
  ADD COLUMN IF NOT EXISTS attempts      TINYINT      NOT NULL DEFAULT 0 AFTER recipient,
  ADD COLUMN IF NOT EXISTS max_attempts  TINYINT      NOT NULL DEFAULT 5 AFTER attempts,
  ADD COLUMN IF NOT EXISTS ref_object    VARCHAR(40)  NULL           AFTER max_attempts,
  ADD COLUMN IF NOT EXISTS ref_object_id INT UNSIGNED NULL           AFTER ref_object;

-- ════════════════════════════════════════════════════════════════
--  FIN DE LA MIGRATION
--  Résumé des valeurs region (colonne existante) :
--    europe  → Europe (FR, BE, CH, LU, MC, DE, ES, IT…)
--    northam → Amérique du Nord (US, CA, MX…)
--    latam   → Afrique & Amérique Latine (SN, CI, BJ, CM, MR, GN…)
-- ════════════════════════════════════════════════════════════════
