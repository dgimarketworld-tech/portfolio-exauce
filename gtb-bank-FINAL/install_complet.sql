-- ════════════════════════════════════════════════════════════════
--  GTB BANK — Installation complète (schéma v2)
--  Base : globa2821189  |  LWS Mutualisé Premium
--  À importer UNE SEULE FOIS via phpMyAdmin sur la base vierge
--  ⚠ Ce fichier recrée tout depuis zéro.
-- ════════════════════════════════════════════════════════════════

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- ── Nettoyage (au cas où une ancienne install partielle existe) ──
DROP TABLE IF EXISTS admin_actions;
DROP TABLE IF EXISTS sessions_actives;
DROP TABLE IF EXISTS support_messages;
DROP TABLE IF EXISTS tickets;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS virements_recurrents;
DROP TABLE IF EXISTS beneficiaires;
DROP TABLE IF EXISTS credits;
DROP TABLE IF EXISTS otp_codes;
DROP TABLE IF EXISTS login_attempts;
DROP TABLE IF EXISTS audit_log;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS cartes;
DROP TABLE IF EXISTS comptes;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ════════════════════════════════════════════════════════════════
--  USERS — Clients et admins
-- ════════════════════════════════════════════════════════════════
CREATE TABLE users (
    id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_number        VARCHAR(20)  NULL,
    email                VARCHAR(190) NOT NULL UNIQUE,
    civility             ENUM('M.','Mme') NULL,
    password_hash        VARCHAR(255) NOT NULL,
    prenom               VARCHAR(80)  NOT NULL,
    nom                  VARCHAR(80)  NOT NULL,
    first_name           VARCHAR(80)  NULL,
    last_name            VARCHAR(80)  NULL,
    telephone            VARCHAR(30)  NULL,
    birthday             DATE         NULL,
    pays                 CHAR(2)      NULL COMMENT 'Code ISO : FR, US, MX…',
    region               VARCHAR(30)  NOT NULL DEFAULT 'europe' COMMENT 'europe | northam | latam',
    langue               VARCHAR(10)  NOT NULL DEFAULT 'fr',
    devise               VARCHAR(10)  NOT NULL DEFAULT 'EUR',
    interface_color      VARCHAR(30)  NOT NULL DEFAULT 'default',
    plan                 ENUM('standard','premium','business') NOT NULL DEFAULT 'standard',
    role                 ENUM('user','admin')                  NOT NULL DEFAULT 'user',
    is_active            TINYINT(1)   NOT NULL DEFAULT 1,
    status               ENUM('active','suspended','closed')   NOT NULL DEFAULT 'active',
    kyc_status           ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
    kyc_document_type    ENUM('cni_ue','passport','titre_sejour') NULL,
    kyc_issuing_country  CHAR(2)      NULL,
    two_fa_enabled       TINYINT(1)   NOT NULL DEFAULT 0,
    avatar_url           VARCHAR(255) NULL,
    language             CHAR(5)      NOT NULL DEFAULT 'fr',
    email_verified       TINYINT(1)   NOT NULL DEFAULT 0,
    -- Blocage accès
    access_blocked       TINYINT(1)   NOT NULL DEFAULT 0,
    access_block_reason  TEXT         NULL,
    access_block_type    ENUM('temporary','permanent') NULL DEFAULT 'permanent',
    access_block_until   DATETIME     NULL,
    -- Contrôle virement
    transfer_stop_pct    INT          NOT NULL DEFAULT 0 COMMENT 'Pourcentage arrêt 0-100',
    -- Connexion
    last_login_at        DATETIME     NULL,
    last_login_ip        VARCHAR(45)  NULL,
    failed_logins        TINYINT      NOT NULL DEFAULT 0,
    created_at           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at           DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email      (email),
    INDEX idx_role       (role),
    INDEX idx_client_num (client_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ════════════════════════════════════════════════════════════════
--  ADMINS — Comptes administrateurs séparés
-- ════════════════════════════════════════════════════════════════
CREATE TABLE admins (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email          VARCHAR(190) NOT NULL UNIQUE,
    password_hash  VARCHAR(255) NOT NULL,
    first_name     VARCHAR(80)  NOT NULL,
    last_name      VARCHAR(80)  NOT NULL,
    role           ENUM('superadmin','admin','support','compliance') NOT NULL DEFAULT 'admin',
    is_active      TINYINT(1)   NOT NULL DEFAULT 1,
    last_login_at  DATETIME     NULL,
    last_login_ip  VARCHAR(45)  NULL,
    failed_logins  TINYINT      NOT NULL DEFAULT 0,
    permissions    JSON         NULL COMMENT 'Droits par module',
    status         ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
    created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ════════════════════════════════════════════════════════════════
--  COMPTES BANCAIRES
-- ════════════════════════════════════════════════════════════════
CREATE TABLE comptes (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id             INT UNSIGNED NOT NULL,
    numero              VARCHAR(34)   NOT NULL UNIQUE COMMENT 'Référence interne GTB',
    iban                VARCHAR(34)   NULL COMMENT 'IBAN ISO 13616',
    bic                 VARCHAR(11)   NULL COMMENT 'BIC SWIFT',
    type                ENUM('courant','epargne','business') NOT NULL DEFAULT 'courant',
    solde               DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    devise              CHAR(3)       NOT NULL DEFAULT 'EUR',
    statut              ENUM('actif','gele','cloture') NOT NULL DEFAULT 'actif',
    plafond_retrait     DECIMAL(15,2) NOT NULL DEFAULT 10000.00,
    plafond_virement    DECIMAL(15,2) NOT NULL DEFAULT 50000.00,
    plafond_paiement    DECIMAL(15,2) NOT NULL DEFAULT 5000.00,
    decouvert_autorise  DECIMAL(15,2) NOT NULL DEFAULT 500.00,
    ouvert_le           DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_user  (user_id),
    INDEX idx_iban  (iban)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ════════════════════════════════════════════════════════════════
--  CARTES BANCAIRES
-- ════════════════════════════════════════════════════════════════
CREATE TABLE cartes (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    compte_id         INT UNSIGNED NOT NULL,
    numero_masque     VARCHAR(20)  NOT NULL COMMENT 'ex: **** **** **** 1234',
    type              ENUM('standard','gold','infinite','business') NOT NULL,
    reseau            ENUM('visa','mastercard') NOT NULL,
    plafond           DECIMAL(15,2) NULL DEFAULT 3000.00,
    cvv               VARCHAR(10)   NULL,
    expire_le         DATE         NOT NULL,
    statut            ENUM('active','bloquee','verification') NOT NULL DEFAULT 'active',
    paiement_en_ligne TINYINT(1)   NOT NULL DEFAULT 1,
    paiement_etranger TINYINT(1)   NOT NULL DEFAULT 1,
    cree_le           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (compte_id) REFERENCES comptes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ════════════════════════════════════════════════════════════════
--  TRANSACTIONS
-- ════════════════════════════════════════════════════════════════
CREATE TABLE transactions (
    id                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    compte_id             INT UNSIGNED    NOT NULL,
    type                  ENUM('depot','retrait','virement_in','virement_out','frais') NOT NULL,
    montant               DECIMAL(15,2)   NOT NULL,
    solde_apres           DECIMAL(15,2)   NOT NULL,
    compte_associe_id     INT UNSIGNED    NULL,
    description           VARCHAR(255)    NULL,
    reference             VARCHAR(50)     NOT NULL UNIQUE,
    statut                ENUM('en_cours','terminee','echouee','annulee') NOT NULL DEFAULT 'en_cours',
    -- Barre de certification
    certification_pct     INT             NOT NULL DEFAULT 0 COMMENT '0-100',
    certification_status  ENUM('idle','running','frozen','blocked','validated','rejected') NOT NULL DEFAULT 'idle',
    certification_speed   ENUM('slow','normal','fast') NOT NULL DEFAULT 'normal',
    certification_message TEXT            NULL,
    admin_alerted         TINYINT(1)      NOT NULL DEFAULT 0,
    backdated_at          DATETIME        NULL,
    cree_le               DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (compte_id)         REFERENCES comptes(id),
    FOREIGN KEY (compte_associe_id) REFERENCES comptes(id),
    INDEX idx_compte_date (compte_id, cree_le),
    INDEX idx_cert_status (certification_status, admin_alerted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ════════════════════════════════════════════════════════════════
--  ACTIONS ADMIN — Journal des opérations sur les comptes clients
-- ════════════════════════════════════════════════════════════════
CREATE TABLE admin_actions (
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

-- ════════════════════════════════════════════════════════════════
--  AUDIT LOG — Actions sensibles (conformité)
-- ════════════════════════════════════════════════════════════════
CREATE TABLE audit_log (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NULL,
    action      VARCHAR(100) NOT NULL,
    ip_address  VARCHAR(45)  NULL,
    user_agent  VARCHAR(255) NULL,
    details     JSON         NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user   (user_id),
    INDEX idx_action (action),
    INDEX idx_date   (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ════════════════════════════════════════════════════════════════
--  LOGIN ATTEMPTS — Anti-bruteforce
-- ════════════════════════════════════════════════════════════════
CREATE TABLE login_attempts (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email        VARCHAR(190) NULL,
    ip_address   VARCHAR(45)  NOT NULL,
    success      TINYINT(1)   NOT NULL DEFAULT 0,
    attempted_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_date    (ip_address, attempted_at),
    INDEX idx_email_date (email, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ════════════════════════════════════════════════════════════════
--  OTP CODES — Codes 2FA temporaires
-- ════════════════════════════════════════════════════════════════
CREATE TABLE otp_codes (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    code_hash  VARCHAR(255) NOT NULL,
    expires_at DATETIME     NOT NULL,
    used       TINYINT(1)   NOT NULL DEFAULT 0,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_active (user_id, used, expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ════════════════════════════════════════════════════════════════
--  BÉNÉFICIAIRES
-- ════════════════════════════════════════════════════════════════
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ════════════════════════════════════════════════════════════════
--  VIREMENTS RÉCURRENTS
-- ════════════════════════════════════════════════════════════════
CREATE TABLE virements_recurrents (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id          INT UNSIGNED  NOT NULL,
    compte_id        INT UNSIGNED  NOT NULL,
    beneficiaire_id  INT UNSIGNED  NULL,
    iban_dest        VARCHAR(34)   NOT NULL,
    nom_dest         VARCHAR(120)  NOT NULL,
    montant          DECIMAL(15,2) NOT NULL,
    motif            VARCHAR(140)  NULL,
    frequence        ENUM('hebdomadaire','mensuel','trimestriel') NOT NULL DEFAULT 'mensuel',
    jour_execution   TINYINT       NOT NULL DEFAULT 1,
    prochain_le      DATE          NOT NULL,
    statut           ENUM('actif','suspendu','annule') NOT NULL DEFAULT 'actif',
    cree_le          DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)   REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (compte_id) REFERENCES comptes(id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ════════════════════════════════════════════════════════════════
--  CRÉDITS
-- ════════════════════════════════════════════════════════════════
CREATE TABLE credits (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id             INT UNSIGNED  NOT NULL,
    reference           VARCHAR(30)   NOT NULL UNIQUE,
    type                ENUM('immobilier','auto','travaux','consommation','professionnel') NOT NULL,
    montant             DECIMAL(15,2) NOT NULL,
    mensualite          DECIMAL(15,2) NOT NULL,
    taux                DECIMAL(5,3)  NOT NULL,
    duree_mois          SMALLINT      NOT NULL,
    solde_restant       DECIMAL(15,2) NOT NULL,
    prochaine_echeance  DATE          NULL,
    statut              ENUM('en_etude','accepte','refuse','en_cours','solde') NOT NULL DEFAULT 'en_etude',
    motif_refus         TEXT          NULL,
    cree_le             DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ════════════════════════════════════════════════════════════════
--  NOTIFICATIONS
-- ════════════════════════════════════════════════════════════════
CREATE TABLE notifications (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    type        ENUM('info','success','warning','error') NOT NULL DEFAULT 'info',
    icon        VARCHAR(40)  NULL,
    title       VARCHAR(140) NOT NULL,
    message     TEXT         NOT NULL,
    link_url    VARCHAR(255) NULL,
    link_label  VARCHAR(60)  NULL,
    is_read     TINYINT(1)   NOT NULL DEFAULT 0,
    cree_le     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_unread (user_id, is_read, cree_le)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ════════════════════════════════════════════════════════════════
--  TICKETS SUPPORT
-- ════════════════════════════════════════════════════════════════
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
    FOREIGN KEY (user_id)       REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (conseiller_id) REFERENCES admins(id)  ON DELETE SET NULL,
    INDEX idx_user   (user_id),
    INDEX idx_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ════════════════════════════════════════════════════════════════
--  MESSAGES SUPPORT
-- ════════════════════════════════════════════════════════════════
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ════════════════════════════════════════════════════════════════
--  SESSIONS ACTIVES
-- ════════════════════════════════════════════════════════════════
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ════════════════════════════════════════════════════════════════
--  SMS BANKING — Configuration par utilisateur
-- ════════════════════════════════════════════════════════════════
CREATE TABLE sms_banking (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       INT UNSIGNED NOT NULL UNIQUE,
    telephone     VARCHAR(30)  NOT NULL,
    pin           VARCHAR(20)  NOT NULL,
    is_active     TINYINT(1)   NOT NULL DEFAULT 1,
    alert_debit   TINYINT(1)   NOT NULL DEFAULT 1,
    alert_credit  TINYINT(1)   NOT NULL DEFAULT 1,
    alert_min     DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Montant minimum pour alerte',
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_tel (telephone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ════════════════════════════════════════════════════════════════
--  SMS BANKING LOGS — Historique des SMS entrants/sortants
-- ════════════════════════════════════════════════════════════════
CREATE TABLE sms_banking_logs (
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

-- ════════════════════════════════════════════════════════════════
--  USER SETTINGS — Préférences utilisateur
-- ════════════════════════════════════════════════════════════════
CREATE TABLE user_settings (
    user_id           INT UNSIGNED  PRIMARY KEY,
    plafond_virement  DECIMAL(12,2) NOT NULL DEFAULT 5000.00,
    plafond_paiement  DECIMAL(12,2) NOT NULL DEFAULT 2000.00,
    notif_email       TINYINT(1)    NOT NULL DEFAULT 1,
    notif_sms         TINYINT(1)    NOT NULL DEFAULT 1,
    notif_push        TINYINT(1)    NOT NULL DEFAULT 0,
    langue            VARCHAR(10)   NOT NULL DEFAULT 'fr',
    devise            VARCHAR(10)   NOT NULL DEFAULT 'EUR',
    theme             VARCHAR(20)   NOT NULL DEFAULT 'dark',
    updated_at        DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ════════════════════════════════════════════════════════════════
--  INVESTISSEMENTS
-- ════════════════════════════════════════════════════════════════
CREATE TABLE investissements (
    id              INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED   NOT NULL,
    produit         VARCHAR(80)    NOT NULL COMMENT 'Ex: GTB Performance, GTB Securise',
    type            VARCHAR(60)    NOT NULL DEFAULT 'performance',
    montant_initial DECIMAL(12,2)  NOT NULL,
    montant_actuel  DECIMAL(12,2)  NOT NULL,
    rendement_pct   DECIMAL(5,2)   NOT NULL DEFAULT 0.00,
    statut          ENUM('actif','cloture','suspendu') NOT NULL DEFAULT 'actif',
    date_debut      DATE           NOT NULL,
    date_fin        DATE           NULL,
    created_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ════════════════════════════════════════════════════════════════
--  FIN — Installation terminée
--  Prochaine étape : créer le compte admin via bin/create-admin.php
-- ════════════════════════════════════════════════════════════════
