# FAAC Multisite - Deployment Guide

## Stato Corrente

✅ **Completato:**
- Repository Git inizializzato con commit iniziale
- File .gitignore configurato per escludere dati sensibili
- Branch `main` e `staging` creati
- GitHub Actions CI/CD pipeline configurato
- Documentazione completa (README.md, CONTRIBUTING.md)
- Template wp-config.php per setup sicuro

## Prossimi Passi per Completare Setup GitHub

### 1. Creare Repository GitHub

1. **Accedi a GitHub** (https://github.com)
2. **Clicca "New repository"**
3. **Configura repository:**
   - **Repository name:** `faac-multisite`
   - **Description:** `FAAC WordPress Multisite - International entrance solutions platform`
   - **Visibility:** Private (consigliato per codice proprietario)
   - **NON inizializzare con README** (abbiamo già i file)

4. **Copia l'URL del repository** (sarà simile a: `git@github.com:username/faac-multisite.git`)

### 2. Collegare Repository Locale a GitHub

```bash
# Nella directory /home/runcloud/webapps/FAAC
git remote add origin git@github.com:YOUR_USERNAME/faac-multisite.git

# Push del branch main
git push -u origin main

# Push del branch staging
git push -u origin staging
```

### 3. Configurare Branch Protection

Nel repository GitHub:
1. Vai su **Settings > Branches**
2. **Aggiungi rule per `main`:**
   - ☑️ Require pull request reviews before merging
   - ☑️ Require status checks to pass before merging
   - ☑️ Require branches to be up to date before merging
   - ☑️ Include administrators
3. **Aggiungi rule per `staging`:**
   - ☑️ Require status checks to pass before merging

### 4. Configurare GitHub Secrets per Deployment

In **Settings > Secrets and variables > Actions**, aggiungi:

#### Per Staging:
- `STAGING_HOST`: IP o hostname server staging
- `STAGING_USER`: username SSH (es. `runcloud`)
- `STAGING_SSH_KEY`: chiave privata SSH per deployment

#### Per Production:
- `PROD_HOST`: IP o hostname server produzione
- `PROD_USER`: username SSH (es. `runcloud`)
- `PROD_SSH_KEY`: chiave privata SSH per deployment

### 5. Setup SSH Key per Deployment (se necessario)

Se non hai già SSH key per deployment:

```bash
# Genera nuova chiave SSH per deployment
ssh-keygen -t rsa -b 4096 -C "deployment@faac-multisite" -f ~/.ssh/faac_deploy

# Copia chiave pubblica sul server
ssh-copy-id -i ~/.ssh/faac_deploy.pub user@server

# Aggiungi chiave privata ai GitHub Secrets
cat ~/.ssh/faac_deploy  # Copia questo contenuto nei Secrets
```

## Workflow di Sviluppo

### Sviluppo Feature
```bash
# Crea branch feature da staging
git checkout staging
git pull origin staging
git checkout -b feature/nome-feature

# Sviluppa e committa
git add .
git commit -m "feat: descrizione feature"

# Push e crea PR
git push origin feature/nome-feature
# Crea Pull Request su GitHub verso staging
```

### Release Production
```bash
# Merge staging -> main via Pull Request su GitHub
# Il deployment avverrà automaticamente via GitHub Actions
```

## Configurazione Monitoraggio

### Notifiche Deploy
Configura webhook per notifiche deploy in Slack/Discord:
1. **Settings > Webhooks** in GitHub
2. **URL endpoint:** il tuo webhook
3. **Eventi:** Pushes, Pull requests, Workflow runs

### Monitoring Production
- **Uptime:** Configura monitoring esterni (UptimeRobot, Pingdom)
- **Performance:** GTMetrix alerts per degradazione performance
- **Logs:** Monitora log applicazione e server

## Backup Strategy

### Database
```bash
# Backup automatico daily (già configurato in GitHub Actions)
wp db export "memory-bank/backup_$(date +%Y%m%d).sql"
```

### Files
```bash
# Backup completo settimanale
rsync -av --exclude-from='.gitignore' /home/runcloud/webapps/FAAC/ /backup/faac/
```

## Sicurezza

### Regular Updates
- **WordPress Core:** Aggiornamenti security automatici
- **Plugin:** Review monthly in staging
- **Theme:** Version control via Git

### Access Control
- **SSH Keys:** Rotation ogni 6 mesi
- **Database:** Password complesse, accesso limitato
- **WordPress:** Admin accounts minimi

## Performance Optimization

### Server Level
- **LiteSpeed Cache:** Configurato e attivo
- **Database:** Optimize scheduled weekly
- **Images:** WebP conversion setup

### Application Level
```bash
# Test performance
wp db optimize
wp cache flush
wp transient delete --all
```

## Troubleshooting

### Common Issues

**Git push rejected:**
```bash
git pull origin main --rebase
git push origin main
```

**Deployment fails:**
1. Verifica SSH connectivity
2. Controlla GitHub Secrets
3. Review deployment logs in Actions

**Performance issues:**
```bash
# Clear all caches
wp cache flush
wp litespeed-purge all
wp transient delete --all
```

### Support Contacts
- **GitHub Issues:** Per bug e feature requests
- **Server:** RunCloud support panel
- **Emergency:** Escalation procedure in documentazione interna

## Testing Strategy

### Pre-Deploy Testing
- ✅ PHP Lint (automatico in CI)
- ✅ Security scan (automatico in CI)
- ✅ Backup creation (automatico in CI)
- ✅ Staging deployment test

### Post-Deploy Validation
- ✅ Site health check (automatico in CI)
- ✅ Performance metrics
- ✅ SSL certificate validity
- ✅ Critical user paths testing

---

## Status Tracking

- [ ] Repository GitHub creato
- [ ] Remote origin configurato
- [ ] Branch protection rules attivate
- [ ] GitHub Secrets configurati
- [ ] SSH deployment key setup
- [ ] First deployment test su staging
- [ ] Production deployment test
- [ ] Monitoring attivato
- [ ] Team access configurato

**Prossima milestone:** First successful deployment via GitHub Actions

---

*Ultimo aggiornamento: $(date +%Y-%m-%d)*
*Maintainer: FAAC Development Team*
