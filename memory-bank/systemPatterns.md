# Pattern di Sistema

## Architettura
- WordPress Multisite
- Web Server: LiteSpeed (migrato da Apache)
- Database: MariaDB
- PHP: Versione gestita da RunCloud

## Decisioni Tecniche Chiave
1. Migrazione da Apache a LiteSpeed
   - Conversione delle regole .htaccess
   - Adattamento delle configurazioni del virtual host
   - Ottimizzazione per le prestazioni

2. Gestione Multisite
   - Mantenimento della struttura esistente
   - Configurazione dei domini e sottodominii
   - Gestione delle regole di rewrite

3. Gestione File
   - Migrazione completa dei contenuti
   - Mantenimento delle strutture delle directory
   - Gestione dei permessi dei file

## Pattern di Design
- Struttura WordPress Multisite standard
- Configurazione LiteSpeed ottimizzata
- Gestione centralizzata dei temi e plugin
- Sistema di caching integrato

## Relazioni tra Componenti
1. Web Server
   - LiteSpeed gestisce le richieste HTTP
   - Processa le regole di rewrite
   - Comunica con PHP

2. WordPress
   - Gestione del multisite
   - Routing delle richieste ai siti
   - Gestione dei contenuti

3. Database
   - Archivio dati centralizzato
   - Gestione delle tabelle multisite
   - Ottimizzazione delle query 