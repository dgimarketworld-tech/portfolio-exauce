# GTB Bank — Global Trust Bank
## Installation (prêt en 5 minutes)

### Prérequis
- PHP 8.1+ avec extensions : PDO, PDO_MySQL, mbstring, openssl
- MySQL 8.0+ ou MariaDB 10.6+
- Apache/Nginx avec mod_rewrite

### 1. Base de données
```bash
mysql -u root -p < sql/schema.sql
```

### 2. Configuration
Éditez `backend/config.php` :
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'gtb');
define('DB_USER', 'votre_user');
define('DB_PASS', 'votre_password');
```

### 3. Compte admin
```bash
php bin/create-admin.php
```

### 4. Données de démonstration (optionnel)
```bash
php bin/seed.php
```

### 5. Permissions
```bash
chmod 755 logs/ uploads/
```

### Accès
- **Site public** : `/pages-publiques/index.html`
- **Dashboard client** : `/dashboard/index.php`
- **Administration** : `/admin/index.php`
- **Connexion** : `/authentification/login.php`

### Architecture
```
backend/          → Cœur PHP (DB, session, sécurité)
sql/              → Schéma complet + extensions
authentification/ → Login/Signup + API
dashboard/        → Espace client PHP/MySQL
admin/            → Backoffice PHP/MySQL
pages-publiques/  → Site vitrine HTML
produits-bancaires/ → Pages produits HTML
```
