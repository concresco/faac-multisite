# FAAC Multisite WordPress Application

## Panoramica

Applicazione web multisite WordPress per FAAC, gestita con LiteSpeed Web Server e configurata per un ambiente di produzione scalabile. Il progetto utilizza una architettura multisite per gestire diversi domini e linguaggi per i prodotti FAAC a livello internazionale.

## Stack Tecnologico

### Backend
- **WordPress Multisite**: Gestione multi-dominio con subdomain install
- **PHP**: Versione gestita tramite RunCloud
- **MariaDB**: Database per contenuti e configurazioni
- **LiteSpeed Web Server**: Server web ad alte prestazioni

### Frontend
- **Tema FAAC Custom**: Tema personalizzato basato su Timber/Twig
- **Bootstrap**: Framework CSS
- **Swiper**: Libreria per slider/carousel
- **SCSS**: Preprocessore CSS

### Infrastruttura
- **RunCloud**: Pannello di controllo server
- **LiteSpeed Cache**: Caching avanzato
- **SSL/TLS**: Certificati di sicurezza
- **Matomo**: Analytics

## Struttura Progetto

```
/
├── wp-content/
│   ├── themes/
│   │   └── faac/               # Tema personalizzato principale
│   ├── plugins/                # Plugin WordPress
│   ├── mu-plugins/             # Must-use plugins per configurazioni custom
│   └── uploads/               # File media (escluso da Git)
├── memory-bank/               # Documentazione e script operativi
├── wp-config-template.php     # Template per configurazione
└── README.md
```

## Configurazione Iniziale

### 1. Database
Crea un database MariaDB e un utente con privilegi completi:

```sql
CREATE DATABASE your_database_name;
CREATE USER 'your_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON your_database_name.* TO 'your_user'@'localhost';
FLUSH PRIVILEGES;
```

### 2. Configurazione WordPress
1. Copia `wp-config-template.php` in `wp-config.php`
2. Modifica le seguenti configurazioni:

```php
// Database
define( 'DB_NAME', 'your_database_name' );
define( 'DB_USER', 'your_database_user' );
define( 'DB_PASSWORD', 'your_database_password' );

// Security Keys
// Genera nuove chiavi su: https://api.wordpress.org/secret-key/1.1/salt/
define( 'AUTH_KEY', 'your_generated_key' );
// ... altre chiavi

// Multisite
define( 'DOMAIN_CURRENT_SITE', 'your_main_domain.com' );

// API e Servizi Esterni
define('MAILGUN_APIKEY', 'your_mailgun_api_key');
define('RECAPTCHA_SITE_KEY', 'your_recaptcha_site_key');
```

### 3. Servizi Esterni Richiesti

#### Mailgun (Email)
- Registrati su [Mailgun](https://www.mailgun.com/)
- Ottieni API key e dominio
- Configura DNS per autenticazione email

#### reCAPTCHA (Sicurezza)
- Configura su [Google reCAPTCHA](https://www.google.com/recaptcha/)
- Ottieni Site Key e Secret Key
- Usa reCAPTCHA v2 Invisible

### 4. Permessi File
```bash
# Directory
find . -type d -exec chmod 755 {} \;
# File
find . -type f -exec chmod 644 {} \;
# wp-config.php
chmod 600 wp-config.php
```

## Funzionalità Chiave

### Multisite
- **Gestione Multi-dominio**: Supporto per diversi domini internazionali
- **Condivisione Risorse**: Tema e plugin condivisi tra siti
- **Gestione Centralizzata**: Amministrazione unificata

### Sicurezza
- **Login Throttling**: Protezione da attacchi brute force (5 tentativi/10min)
- **Security Headers**: HSTS, X-Frame-Options, CSP
- **File Edit Disabled**: Editor file disabilitato nel admin
- **User Registration Blocked**: Registrazioni disabilitate

### Performance
- **LiteSpeed Cache**: Caching avanzato a livello server
- **Asset Optimization**: Minificazione CSS/JS
- **Database Optimization**: Cleanup automatico transients
- **WP-Cron Ottimizzato**: Gestione cron multisite efficiente

### Monitoraggio
- **Query Monitor**: Debug performance query
- **Error Logging**: Sistema di logging centralizzato
- **Matomo Analytics**: Tracking privacy-compliant

## Sviluppo

### Prerequisiti
- PHP 8.1+
- Composer
- Node.js & Yarn (per tema)
- WP-CLI

### Setup Ambiente Sviluppo
1. Clona il repository
2. Configura database e wp-config.php
3. Installa dipendenze tema:
```bash
cd wp-content/themes/faac
composer install
yarn install
yarn build
```

### Build Tema
```bash
cd wp-content/themes/faac
yarn dev     # Development con watch
yarn build   # Production build
```

## Deployment

### Server Requirements
- LiteSpeed Web Server
- PHP 8.1+ con estensioni: mysqli, gd, curl, zip, mbstring
- MariaDB 10.4+
- SSL/TLS certificate

### Deploy Process
1. Upload file via Git o FTP
2. Configura wp-config.php
3. Importa database
4. Build asset tema
5. Configura permessi file
6. Test funzionalità

### Monitoring Post-Deploy
- Verifica SSL certificate
- Test performance con GTMetrix
- Controlla error logs
- Verifica backup automatici

## Backup

### Database
```bash
# Export
wp db export backup_$(date +%Y%m%d).sql

# Import  
wp db import backup_file.sql
```

### File
- Backup completo daily via RunCloud
- Backup incrementali ogni 6 ore
- Retention: 30 giorni

## Troubleshooting

### Common Issues

**Error 500**
- Controlla error logs in `memory-bank/ops/`
- Verifica permessi file
- Check plugin compatibility

**Slow Performance**
- Flush LiteSpeed cache
- Optimize database: `wp db optimize`
- Check slow queries con Query Monitor

**Multisite Issues**
- Verifica configurazione DNS
- Check .htaccess/LiteSpeed rules
- Validate network settings

### Support
- Documentazione: `memory-bank/README.md`
- Logs operativi: `memory-bank/ops/`
- Issue tracking: GitHub Issues

## Security

### Backup Sicurezza
- Database backup prima di major updates
- File backup settimanali
- Test restore procedure mensili

### Monitoring
- Login attempts monitoring
- File integrity checks
- Security headers validation

### Updates
- WordPress core: Automatici per security
- Plugin: Review + test in staging
- Tema: Version control tramite Git

## License

Progetto proprietario FAAC. Tutti i diritti riservati.

## Contributors

Maintainer: Team FAAC Development

---

Per supporto tecnico o questioni specifiche, consulta la documentazione in `memory-bank/` o apri un issue su GitHub.
