# Contesto Tecnico

## Tecnologie Utilizzate
- WordPress Multisite
- LiteSpeed Web Server
- MariaDB
- PHP (versione gestita da RunCloud)
- RunCloud (pannello di controllo server)

## Setup di Sviluppo
- Server di produzione: LiteSpeed
- Ambiente: RunCloud managed
- Directory root: `/home/runcloud/webapps/FAAC`
- Database:
  - Nome: prod
  - Utente: prodfaac
  - Password: kt070mn8N0ZA9GUf
- **Ottimizzazione Ambiente Sviluppo (Cursor/Node.js):**
  - Implementato un servizio systemd (`cursor.service`) per gestire e limitare le risorse dei processi Node.js utilizzati da Cursor IDE.
  - Configurato uno script (`kill-cursor.sh`) per terminare automaticamente questi processi al logout SSH, riducendo l'uso di CPU/memoria quando l'IDE non è attivamente utilizzato.

## Vincoli Tecnici
1. Server
   - Sistema operativo: Linux
   - Web server: LiteSpeed
   - Gestione tramite RunCloud
   - Permessi file gestiti da runcloud:runcloud

2. WordPress
   - Configurazione multisite
   - Compatibilità plugin esistenti
   - Mantenimento struttura URL

3. Database
   - MariaDB
   - Struttura multisite
   - Tabelle prefissate

## Dipendenze
1. Core
   - WordPress Multisite
   - LiteSpeed Web Server
   - PHP
   - MariaDB

2. Configurazione
   - Regole di rewrite LiteSpeed
   - Virtual Host configuration
   - Configurazioni WordPress multisite

3. Servizi
   - RunCloud
   - DNS
   - SSL/TLS 