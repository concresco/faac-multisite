# Contesto Attivo

## Focus Attuale
- Migrazione iniziale del sito WordPress multisite
- Conversione configurazioni da Apache a LiteSpeed
- Setup ambiente di produzione
- Current work focus: Server performance optimization and troubleshooting.

## Cambiamenti Recenti
- Creazione ambiente RunCloud
- Importazione database
- Setup directory di progetto
- Recent changes:
  - Optimized system settings (sysctl: swappiness, vfs_cache_pressure).
  - Optimized MariaDB configuration (innodb_buffer_pool_size, etc.).
  - Installed and configured Redis for object caching (wp-config.php updated).
  - Identified and terminated rogue `bash` processes causing high CPU usage.
  - Configured UFW firewall, adding rules for SSH, HTTP, HTTPS, and RunCloud Agent (TCP 34210).
  - Resolved RunCloud Agent communication timeout issue by adding the necessary firewall rule.
  - Created a basic server maintenance script (`/usr/local/bin/maintenance.sh`).
  - **Implementata ottimizzazione risorse per Cursor/Node.js:**
    - Creato e configurato servizio systemd (`cursor.service`) per limitare CPU/memoria.
    - Configurato script (`kill-cursor.sh`) per terminare processi Node.js al logout SSH.

## Prossimi Passi
1. Analisi configurazioni Apache esistenti
2. Migrazione file WordPress
3. Conversione regole .htaccess per LiteSpeed
4. Configurazione virtual host
5. Test funzionalità multisite
6. Verifica performance
- Next steps:
  - Monitor server performance and stability.
  - User to configure PHP settings via RunCloud panel.
  - User to install Redis Object Cache plugin in WordPress.
  - Populate and schedule the server maintenance script.
  - **Verificare efficacia ottimizzazione Cursor/Node.js:** Monitorare l'uso delle risorse e il corretto funzionamento del servizio `cursor.service` e dello script di cleanup `kill-cursor.sh`.

## Decisioni Attive
1. Gestione Permessi
   - Utilizzo utente runcloud:runcloud
   - Configurazione permessi directory

2. Configurazione Server
   - Migrazione da Apache a LiteSpeed
   - Adattamento regole rewrite

3. Ottimizzazione
   - Setup caching LiteSpeed
   - Configurazione ottimale PHP
   - Ottimizzazione database

## Considerazioni
- Mantenimento funzionalità esistenti
- Minimizzazione downtime
- Backup e rollback strategy
- Monitoraggio performance post-migrazione
- Active decisions and considerations:
  - User opted out of WordPress caching plugin installation (e.g., WP Rocket) for now.
  - High CPU usage was caused by specific `bash` processes, likely runaway or orphaned, originating from `adin` user sessions via `sudo su`. The root cause of these processes needs monitoring. 