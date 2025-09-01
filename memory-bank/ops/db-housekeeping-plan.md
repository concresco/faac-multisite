# Piano Database Housekeeping (bozza, non eseguire senza approvazione)

## Obiettivi
- Ridurre transients orfani e scaduti
- Validare e ridurre dimensione `rewrite_rules`
- Analizzare e contenere tabelle grandi (revisions, forms, flamingo, redirection hits, log)

## Input report
- `memory-bank/dbhk_transients_24_25.tsv`
- `memory-bank/dbhk_rewrite_rules.tsv`
- `memory-bank/dbhk_big_tables_24_25.tsv`

## Transients (per-sito)
Esempio per `blog_id=24`:
```bash
PREFIX=$(wp db prefix)
TBL="${PREFIX}24_options"
# Conteggio e verifica
wp db query "SELECT COUNT(*) AS total FROM ${TBL} WHERE option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%';"
wp db query "SELECT COUNT(*) AS expired FROM ${TBL} WHERE (option_name LIKE '_transient_timeout_%' OR option_name LIKE '_site_transient_timeout_%') AND CAST(option_value AS UNSIGNED) < UNIX_TIMESTAMP();"
# Cleanup mirato (scaduti)
wp transient delete --expired --url="https://www.faac.nl/"
# Cleanup completo (valutare impatto):
# wp transient delete --all --url="https://www.faac.nl/"
```

## rewrite_rules (per-sito)
Ridurre regole ridondanti rigenerando i permalink:
```bash
# Flush via WP-CLI
wp rewrite flush --hard --url="https://en.geniusg.com/"
# Verifica dimensione dopo flush
PREFIX=$(wp db prefix); TBL="${PREFIX}22_options"; wp db query "SELECT LENGTH(option_value) FROM ${TBL} WHERE option_name='rewrite_rules';"
```

## wp_options autoload
Individuare opzioni autoload pesanti e valutarne la messa a `autoload='no'` se non necessarie al bootstrap front-end:
```bash
PREFIX=$(wp db prefix); TBL="${PREFIX}25_options"
wp db query "SELECT option_name, LENGTH(option_value) AS sz FROM ${TBL} WHERE autoload='yes' ORDER BY sz DESC LIMIT 50;"
# Esempio cambio (dopo verifica funzionale):
# wp db query "UPDATE ${TBL} SET autoload='no' WHERE option_name IN ('my_import_stores','iubenda_radar_api_configuration','out_of_the_box_settings');"
```

## Tabelle grandi (24 e 25)
Verifica e pulizia sicura:
```bash
# Revisions (WordPress core)
wp post list --post_type='any' --format=ids --url="https://entrancesolutions.faac.au/" | xargs -n100 -r wp post delete --force --url="https://entrancesolutions.faac.au/"
# Oppure limitare via WP_POST_REVISIONS in wp-config.php (già in produzione: valutare)

# Flamingo (contact form entries)
wp db query "SELECT COUNT(*) FROM pr_24_flamingo_inbound;" # valutare retention
# Esempio purge vecchi > 180 giorni (verificare schema prima di applicare):
# wp db query "DELETE FROM pr_24_flamingo_inbound WHERE date < DATE_SUB(NOW(), INTERVAL 180 DAY);"

# WPForms / altre forms: individuare prefissi tabella e applicare retention analoga

# Redirection hits
wp db query "SELECT COUNT(*) FROM pr_25_redirection_logs;" # se presente
# Esempio purge > 30 giorni:
# wp db query "DELETE FROM pr_25_redirection_logs WHERE created < DATE_SUB(NOW(), INTERVAL 30 DAY);"

# Ottimizzazione tabelle dopo cleanup
wp db optimize
```

## Backup e rollback
```bash
wp db export memory-bank/backup_before_dbhk.sql
# Rollback
wp db import memory-bank/backup_before_dbhk.sql
```

## Sequenza consigliata
1. Backup
2. Transients scaduti (per-sito, prima 24/25)
3. Flush rewrite (22/24/25)
4. Valutazione autoload (dry-run + applicazione mirata)
5. Pulizia tabelle grandi con retention
6. Optimize
7. Rilettura metriche (`perf_site_autoload_totals.tsv`, `dbhk_big_tables_24_25.tsv`)
