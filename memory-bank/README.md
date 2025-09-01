# FAAC Multisite - Comandi Sicurezza

## Monitoraggio Registrazioni Sospette

### Scansione TSV con WP-CLI
```bash
# Scansione completa con alert email
wp faacsec mb-scan --email=security@faactechnologies.com

# Esecuzione manuale (cron orario consigliato)
0 * * * * cd /home/runcloud/webapps/FAAC && wp faacsec mb-scan --email=security@faactechnologies.com
```

## Verifica Header Sicurezza

### Test singolo dominio
```bash
curl -sSLI https://www.faac.it/ | tr -d '\r' | egrep -i '^(Strict-Transport-Security|X-Frame-Options|X-Content-Type-Options|Referrer-Policy|Permissions-Policy):'
```

### Test tutti i domini
```bash
for d in www.faacentrancesolutions.co.uk www.faacdoorsandshutters.co.uk www.faacentrancesolutions.fr www.geniusg.com en.geniusg.com www.geniusg.fr www.faac.si www.faac.hu www.faac-automatischedeuren.nl www.faac.nl www.faac.ch www.faac.biz www.faac.at entrancesolutions.faac.au staging.entrancesolutions.faac.au; do echo "===== $d"; curl -sSLI https://$d/ | tr -d '\r' | egrep -i '^(Strict-Transport-Security|X-Frame-Options|X-Content-Type-Options|Referrer-Policy|Permissions-Policy):' | cat; done
```

## Verifica Plugin Sicurezza

### Controllo MU-plugins attivi
```bash
ls -la wp-content/mu-plugins/
```

### Controllo plugin attivi
```bash
wp plugin list --status=active
```

## Log e Monitoraggio

### Controllo log WordPress
```bash
tail -f wp-content/debug.log
```

### Controllo log server
```bash
sudo journalctl -u lsws -f
```

## Test Funzionalità

### Verifica reCAPTCHA login
```bash
# Test endpoint login
curl -sSL https://www.faac.it/wp-login.php | grep -i recaptcha
```

### Verifica endpoint REST bloccati
```bash
# Test endpoint utenti (dovrebbe essere bloccato per anonimi)
curl -sSL https://www.faac.it/wp-json/wp/v2/users/
```

## Manutenzione

### Pulizia transients
```bash
wp transient delete --all
```

### Verifica integrità core
```bash
wp core verify-checksums
```

### Backup database
```bash
wp db export backup-$(date +%Y%m%d-%H%M%S).sql
```

## Note Importanti

- **Non modificare** i MU-plugins di sicurezza senza testare
- **Verificare sempre** gli header dopo modifiche .htaccess
- **Monitorare** i log per attività sospette
- **Testare** le funzionalità PIM IAKI dopo modifiche sicurezza
