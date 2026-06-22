-- ================================================================
--  GTB BANK — Schéma de base de données (v2)
--
--  Import :
--    mysql -u root -p < sql/schema.sql
--  Ou via phpMyAdmin : importer ce fichier.
-- ================================================================

CREATE DATABASE IF NOT EXISTS gtb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gtb;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS support_messages;
DROP TABLE IF EXISTS tickets;
DROP TABLE IF EXISTS sessions_actives;
DROP TABLE IF EXISTS virements_recurrents;
DROP TABLE IF EXISTS beneficiaires;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS credits;
DROP TABLE IF EXISTS audit_log;
DROP TABLE IF EXISTS otp_codes;
DROP TABLE IF EXISTS login_attempts;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS cartes;
DROP TABLE IF EXISTS comptes;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS admins;
SET FOREIGN_KEY_CHECKS = 1;

-- ── USERS ────────────────────────────────────────────────────────
CREATE TABLE users (
    id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_number        VARCHAR(20)  NULL UNIQUE,
    email                VARCHAR(190) NOT NULL UNIQUE,
    password_hash        VARCHAR(255) NOT NULL,
    civility             ENUM('M.','Mme') NULL,
    prenom               VARCHAR(80)  NOT NULL,
    nom                  VARCHAR(80)  NOT NULL,
    first_name           VARCHAR(80)  NULL,
    last_name            VARCHAR(80)  NULL,
    telephone            VARCHAR(30)  NULL,
    birthday             DATE         NULL,
    pays                 CHAR(2)      NULL COMMENT 'Code ISO : SN, CI, BJ, FR...',
    plan                 ENUM('standard','premium','business') NOT NULL DEFAULT 'standard',
    role                 ENUM('user','admin') NOT NULL DEFAULT 'user',
    is_active            TINYINT(1)   NOT NULL DEFAULT 1,
    status               ENUM('active','suspended','closed') NOT NULL DEFAULT 'active',
    kyc_status           ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
    kyc_document_type    ENUM('cni_ue','passport','titre_sejour') NULL,
    kyc_issuing_country  CHAR(2) NULL,
    two_fa_enabled       TINYINT(1)   NOT NULL DEFAULT 0,
    avatar_url           VARCHAR(255) NULL,
    language             CHAR(5)      NOT NULL DEFAULT 'fr',
    email_verified       TINYINT(1)   NOT NULL DEFAULT 0,
    last_login_at        DATETIME     NULL,
    last_login_ip        VARCHAR(45)  NULL,
    failed_logins        TINYINT      NOT NULL DEFAULT 0,
    created_at           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at           DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email  (email),
    INDEX idx_status (status),
    INDEX idx_client (client_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── ADMINS ───────────────────────────────────────────────────────
CREATE TABLE admins (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email           VARCHAR(190) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    first_name      VARCHAR(80)  NOT NULL,
    last_name       VARCHAR(80)  NOT NULL,
    role            ENUM('superadmin','admin','support','compliance') NOT NULL DEFAULT 'admin',
    status          ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
    two_fa_enabled  TINYINT(1)   NOT NULL DEFAULT 0,
    permissions     JSON         NULL,
    last_login_at   DATETIME     NULL,
    last_login_ip   VARCHAR(45)  NULL,
    failed_logins   TINYINT      NOT NULL DEFAULT 0,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── COMPTES BANCAIRES ────────────────────────────────────────────
CREATE TABLE comptes (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id   INT UNSIGNED NOT NULL,
    numero    VARCHAR(34)  NOT NULL UNIQUE,
    type      ENUM('courant','epargne','business') NOT NULL DEFAULT 'courant',
    solde     DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    devise    CHAR(3)       NOT NULL DEFAULT 'EUR',
    statut    ENUM('actif','gele','cloture') NOT NULL DEFAULT 'actif',
    ouvert_le DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── CARTES BANCAIRES ─────────────────────────────────────────────
CREATE TABLE cartes (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    compte_id     INT UNSIGNED NOT NULL,
    numero_masque VARCHAR(20)  NOT NULL,
    type          ENUM('standard','gold','infinite','business') NOT NULL,
    reseau        ENUM('visa','mastercard') NOT NULL DEFAULT 'visa',
    plafond       DECIMAL(15,2) NULL,
    expire_le     DATE         NOT NULL,
    statut        ENUM('active','bloquee','verification') NOT NULL DEFAULT 'active',
    cree_le       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (compte_id) REFERENCES comptes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── TRANSACTIONS ─────────────────────────────────────────────────
CREATE TABLE transactions (
    id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    compte_id         INT UNSIGNED NOT NULL,
    type              ENUM('depot','retrait','virement_in','virement_out','frais') NOT NULL,
    montant           DECIMAL(15,2) NOT NULL,
    solde_apres       DECIMAL(15,2) NOT NULL,
    compte_associe_id INT UNSIGNED NULL,
    description       VARCHAR(255) NULL,
    reference         VARCHAR(50)  NOT NULL UNIQUE,
    statut            ENUM('en_cours','terminee','echouee','annulee') NOT NULL DEFAULT 'en_cours',
    cree_le           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (compte_id)         REFERENCES comptes(id),
    FOREIGN KEY (compte_associe_id) REFERENCES comptes(id),
    INDEX idx_compte_date (compte_id, cree_le)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── OTP CODES ────────────────────────────────────────────────────
CREATE TABLE otp_codes (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id        INT UNSIGNED NULL,
    admin_id       INT UNSIGNED NULL,
    purpose        VARCHAR(40)  NOT NULL DEFAULT 'login',
    channel        ENUM('email','sms') NOT NULL DEFAULT 'email',
    recipient      VARCHAR(190) NULL,
    code_hash      VARCHAR(255) NOT NULL,
    attempts       TINYINT      NOT NULL DEFAULT 0,
    max_attempts   TINYINT      NOT NULL DEFAULT 5,
    ref_object     VARCHAR(40)  NULL,
    ref_object_id  INT UNSIGNED NULL,
    used           TINYINT(1)   NOT NULL DEFAULT 0,
    expires_at     DATETIME     NOT NULL,
    created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
    INDEX idx_user_active (user_id, used, expires_at),
    INDEX idx_purpose     (purpose, used, expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── LOGIN ATTEMPTS ───────────────────────────────────────────────
CREATE TABLE login_attempts (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email        VARCHAR(190) NULL,
    ip_address   VARCHAR(45)  NOT NULL,
    success      TINYINT(1)   NOT NULL DEFAULT 0,
    attempted_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_date    (ip_address, attempted_at),
    INDEX idx_email_date (email, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── AUDIT LOG ────────────────────────────────────────────────────
CREATE TABLE audit_log (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    actor_type   ENUM('user','admin','system') NOT NULL DEFAULT 'system',
    actor_id     INT UNSIGNED NULL,
    actor_email  VARCHAR(190) NULL,
    action       VARCHAR(100) NOT NULL,
    entity_type  VARCHAR(40)  NULL,
    entity_id    INT UNSIGNED NULL,
    details      JSON         NULL,
    ip_address   VARCHAR(45)  NULL,
    user_agent   VARCHAR(255) NULL,
    severity     ENUM('info','warning','critical') NOT NULL DEFAULT 'info',
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_actor  (actor_type, actor_id),
    INDEX idx_action (action),
    INDEX idx_date   (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── BÉNÉFICIAIRES ────────────────────────────────────────────────
CREATE TABLE beneficiaires (
    id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id  INT UNSIGNED NOT NULL,
    nom      VARCHAR(120) NOT NULL,
    iban     VARCHAR(34)  NOT NULL,
    bic      VARCHAR(12)  NULL,
    banque   VARCHAR(100) NULL,
    intitule VARCHAR(80)  NULL,
    verifie  TINYINT(1)   NOT NULL DEFAULT 0,
    cree_le  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── VIREMENTS RÉCURRENTS ─────────────────────────────────────────
CREATE TABLE virements_recurrents (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    compte_id       INT UNSIGNED NOT NULL,
    beneficiaire_id INT UNSIGNED NULL,
    iban_dest       VARCHAR(34)  NOT NULL,
    nom_dest        VARCHAR(120) NOT NULL,
    montant         DECIMAL(15,2) NOT NULL,
    motif           VARCHAR(140) NULL,
    frequence       ENUM('hebdomadaire','mensuel','trimestriel') NOT NULL DEFAULT 'mensuel',
    jour_execution  TINYINT      NOT NULL DEFAULT 1,
    prochain_le     DATE         NOT NULL,
    statut          ENUM('actif','suspendu','annule') NOT NULL DEFAULT 'actif',
    cree_le         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (compte_id) REFERENCES comptes(id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── CRÉDITS ──────────────────────────────────────────────────────
CREATE TABLE credits (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id            INT UNSIGNED NOT NULL,
    reference          VARCHAR(30)  NOT NULL UNIQUE,
    type               ENUM('immobilier','auto','travaux','consommation','professionnel') NOT NULL,
    montant            DECIMAL(15,2) NOT NULL,
    mensualite         DECIMAL(15,2) NOT NULL,
    taux               DECIMAL(5,3)  NOT NULL,
    duree_mois         SMALLINT      NOT NULL,
    solde_restant      DECIMAL(15,2) NOT NULL,
    prochaine_echeance DATE          NULL,
    statut             ENUM('en_etude','accepte','refuse','en_cours','solde') NOT NULL DEFAULT 'en_etude',
    motif_refus        TEXT          NULL,
    cree_le            DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── NOTIFICATIONS ────────────────────────────────────────────────
CREATE TABLE notifications (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    type       ENUM('info','success','warning','error') NOT NULL DEFAULT 'info',
    icon       VARCHAR(40)  NULL,
    title      VARCHAR(140) NOT NULL,
    message    TEXT         NOT NULL,
    link_url   VARCHAR(255) NULL,
    link_label VARCHAR(60)  NULL,
    is_read    TINYINT(1)   NOT NULL DEFAULT 0,
    cree_le    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_unread (user_id, is_read, cree_le)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── TICKETS SUPPORT ──────────────────────────────────────────────
CREATE TABLE tickets (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       INT UNSIGNED NOT NULL,
    reference     VARCHAR(20)  NOT NULL UNIQUE,
    categorie     ENUM('transaction','carte','virement','compte','credit','autre') NOT NULL,
    sujet         VARCHAR(180) NOT NULL,
    priorite      ENUM('basse','normale','urgente') NOT NULL DEFAULT 'normale',
    statut        ENUM('ouvert','en_cours','resolu','ferme') NOT NULL DEFAULT 'ouvert',
    conseiller_id INT UNSIGNED NULL,
    cree_le       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    mis_a_jour    DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)       REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (conseiller_id) REFERENCES admins(id) ON DELETE SET NULL,
    INDEX idx_user   (user_id),
    INDEX idx_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── MESSAGES SUPPORT ─────────────────────────────────────────────
CREATE TABLE support_messages (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id   INT UNSIGNED NOT NULL,
    auteur_type ENUM('client','conseiller','system') NOT NULL,
    auteur_id   INT UNSIGNED NULL,
    message     TEXT         NOT NULL,
    lu          TINYINT(1)   NOT NULL DEFAULT 0,
    cree_le     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    INDEX idx_ticket (ticket_id, cree_le)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── SESSIONS ACTIVES ─────────────────────────────────────────────
CREATE TABLE sessions_actives (
    id            VARCHAR(128) PRIMARY KEY,
    user_id       INT UNSIGNED NOT NULL,
    user_type     ENUM('user','admin') NOT NULL DEFAULT 'user',
    ip_address    VARCHAR(45)  NULL,
    user_agent    VARCHAR(255) NULL,
    device_name   VARCHAR(100) NULL,
    last_activity DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    cree_le       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id, user_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================================================================
--  Compte admin : créer via CLI
--    php bin/create-admin.php email password "Prénom" "Nom"
-- ================================================================
