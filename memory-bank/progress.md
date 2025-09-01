# Stato Avanzamento

## Completato
- [x] Setup iniziale ambiente RunCloud
- [x] Importazione database
- [x] Creazione memory bank
- [x] Analisi requisiti progetto
- [x] Ottimizzazione performance server (sysctl, MariaDB, Redis, Firewall)
- [x] Risoluzione problema alto utilizzo CPU (processi `bash`)
- [x] Risoluzione problema comunicazione RunCloud Agent (firewall)
- [x] Creazione script base manutenzione server
- [x] Implementazione ottimizzazione risorse per Cursor/Node.js (servizio systemd e script di cleanup)

## In Corso
- [ ] Analisi configurazioni Apache esistenti
- [ ] Preparazione migrazione file WordPress
- [ ] Studio conversione regole .htaccess

## Da Fare
1. Migrazione File
   - [ ] Copia file WordPress
   - [ ] Verifica permessi
   - [ ] Controllo integrità

2. Configurazione Server
   - [ ] Conversione regole .htaccess
   - [ ] Setup virtual host LiteSpeed
   - [ ] Configurazione SSL

3. WordPress Multisite
   - [ ] Verifica configurazione
   - [ ] Test funzionalità
   - [ ] Controllo domini/sottodominii

4. Ottimizzazione
   - [ ] Setup cache LiteSpeed
   - [ ] Ottimizzazione PHP
   - [ ] Tuning database

## Problemi Noti
- Nessun problema critico al momento
- In attesa di verificare compatibilità configurazioni

## Note di Progresso
- Database importato con successo
- Ambiente base preparato
- Memory bank creata e popolata
- Pronti per iniziare la migrazione effettiva

## Stato Attuale
- Server environment setup and basic configuration complete.
- WordPress site migrated and accessible, but experienced initial credential issues (resolved).
- Significant server performance optimizations applied (sysctl, MariaDB, Redis, Firewall).
- High CPU usage issue caused by rogue `bash` processes resolved.
- RunCloud Agent communication issue resolved (firewall rule added).
- Server performance is now stable, CPU usage is normal.

## Cosa Funziona
- Web server (LiteSpeed) serving the WordPress site.
- Database (MariaDB) connected and serving data.
- PHP-FPM processing requests.
- Redis installed and configured for WordPress object caching (requires WP plugin).
- UFW Firewall active with basic rules + RunCloud Agent port.
- Fail2ban installed and active.
- RunCloud panel communication with the server agent is functional.

## Cosa Resta da Fare
- Configure PHP settings via RunCloud panel (memory_limit, opcache, etc.).
- Install and configure Redis Object Cache plugin in WordPress.
- Populate and schedule the server maintenance script (`/usr/local/bin/maintenance.sh`).
- Comprehensive testing of the WordPress site functionality and performance under load.
- Configure backups (if not already done via RunCloud or other means).
- Implement any remaining security hardening measures.
- **Monitorare efficacia ottimizzazione Cursor/Node.js** (uso risorse, funzionamento servizio/script).

## Problemi Noti
- None currently active. Previously resolved: high CPU usage, RunCloud agent timeout.
- Need to monitor for recurrence of high CPU usage from unidentified `bash` processes.

## Sicurezza - Hardening (2025-08-31)
- Registrazioni utenti disabilitate network-wide; REST utenti limitato ad admin/super admin
- Rimozione Wordfence/WAF; nessun auto_prepend attivo
- Login hardening: throttling (5 tentativi/10 min, lock 15 min) + reCAPTCHA v2 Invisible su login/CF7/WPForms
- Header sicurezza attivi (HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy) sui domini principali (esclusi faac.biz, staging AU, faac.si, faac.ch su richiesta)
- Monitor TSV sospetti con MU-plugin e comando WP-CLI (cron orario consigliato)

## Attività Pendenti (Sicurezza)
- Allineare header su www.faac.si e www.faac.ch (esclusi faac.biz e staging AU)