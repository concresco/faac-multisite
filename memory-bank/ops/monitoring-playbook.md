## Playbook monitoraggio rollout cron multisite

Obiettivo: aumentare la frequenza del cron minimizzando l'impatto su CPU/IO/DB.

### Metriche da osservare
- Load average sistema (1/5/15 min)
- Error log PHP/LiteSpeed
- Slow queries MariaDB (SHOW PROCESSLIST, Performance Schema, pt-query-digest se disponibile)
- Durata media `wp cron` per sito
- Hit/Miss cache (LSCache/Cloudflare) dopo esecuzione cron

### Comandi utili (read-only)
```
wp cron event list --fields=hook,next_run --format=table --url="https://en.geniusg.com/"
php -r 'var_export(opcache_get_status(false));'
curl -sSI https://en.geniusg.com/ | egrep -i '^(HTTP/|cache-control:|cf-cache-status:|x-litespeed-cache:)' 
```

### Troubleshooting
- Se i job si accodano: aumentare BATCH_SIZE oppure frequency, ma verificare IO/DB
- Se compaiono timeout: aumentare TIMEOUT o ridurre BATCH_SIZE
- Se purge cache massivo impatta TTFB: rivedere regole purge/plugin

### Rollback
- Tornare alla Fase 1 o al fallback semplice (wp-cron.php ogni 10–30 min)


