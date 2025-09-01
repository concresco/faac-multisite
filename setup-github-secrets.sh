#!/bin/bash

echo "🔐 FAAC Multisite - Setup GitHub Secrets"
echo "========================================"

REPO="concresco/faac-multisite"

echo "Questo script ti aiuterà a configurare i GitHub Secrets necessari per il deployment automatico."
echo ""

# Funzione per aggiungere un secret
add_secret() {
    local secret_name=$1
    local secret_description=$2
    
    echo "📝 Configurando: $secret_name"
    echo "   Descrizione: $secret_description"
    read -s -p "   Inserisci il valore per $secret_name: " secret_value
    echo ""
    
    if [ -n "$secret_value" ]; then
        echo "$secret_value" | gh secret set "$secret_name" --repo "$REPO"
        echo "✅ $secret_name configurato con successo"
    else
        echo "⚠️  $secret_name saltato (valore vuoto)"
    fi
    echo ""
}

echo "🚀 Configurazione Secrets per Deployment"
echo "----------------------------------------"

# Secrets per Production
echo "📦 PRODUCTION DEPLOYMENT SECRETS:"
add_secret "PROD_HOST" "Hostname o IP del server di produzione (es. 192.168.1.100)"
add_secret "PROD_USER" "Username SSH per il server di produzione (es. runcloud)"
add_secret "PROD_SSH_KEY" "Chiave privata SSH per accesso al server di produzione"

echo "🧪 STAGING DEPLOYMENT SECRETS:"
add_secret "STAGING_HOST" "Hostname o IP del server di staging"
add_secret "STAGING_USER" "Username SSH per il server di staging"
add_secret "STAGING_SSH_KEY" "Chiave privata SSH per accesso al server di staging"

echo "🔔 NOTIFICATION SECRETS (opzionali):"
read -p "Vuoi configurare notifiche Slack/Discord? (y/n): " setup_notifications

if [ "$setup_notifications" = "y" ] || [ "$setup_notifications" = "Y" ]; then
    add_secret "SLACK_WEBHOOK" "URL del webhook Slack per notifiche deployment"
    add_secret "DISCORD_WEBHOOK" "URL del webhook Discord per notifiche deployment"
fi

echo "🎉 Configurazione Secrets completata!"
echo ""
echo "📋 Secrets configurati:"
gh secret list --repo "$REPO"

echo ""
echo "🔗 Prossimi passi:"
echo "1. Verifica che tutti i secrets siano configurati correttamente"
echo "2. Testa il deployment su staging con un piccolo cambiamento"
echo "3. Configura il team di sviluppo con accesso al repository"
echo ""
echo "📖 Per maggiori dettagli consulta: DEPLOYMENT-GUIDE.md"
