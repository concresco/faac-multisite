# Contributing to FAAC Multisite

Grazie per il tuo interesse nel contribuire al progetto FAAC! Questo documento fornisce linee guida per contribuire efficacemente al progetto.

## Code of Conduct

### I nostri standard
- Utilizzo di linguaggio accogliente e inclusivo
- Rispetto per diversi punti di vista ed esperienze
- Accettazione costruttiva delle critiche
- Focus su ciò che è meglio per la community

### Comportamenti non accettabili
- Linguaggio o immagini sessualizzate
- Commenti offensivi o attacchi personali
- Molestie pubbliche o private
- Pubblicazione di informazioni private senza permesso

## Come Contribuire

### Reporting Bugs
1. Usa i bug report template su GitHub Issues
2. Includi sempre informazioni dettagliate sull'ambiente
3. Fornisci passi chiari per riprodurre il problema
4. Allega screenshot se pertinenti

### Suggesting Features
1. Usa i feature request template su GitHub Issues
2. Spiega chiaramente il problema che la feature risolverebbe
3. Considera l'impatto su tutto il multisite
4. Valuta l'effort di sviluppo richiesto

### Pull Requests

#### Branch Strategy
- `main`: Branch di produzione, sempre stabile
- `staging`: Branch per test pre-produzione
- `feature/[nome-feature]`: Branch per nuove feature
- `bugfix/[nome-bug]`: Branch per correzioni bug
- `hotfix/[nome-hotfix]`: Branch per fix urgenti in produzione

#### Workflow
1. **Fork** del repository
2. **Crea branch** dalla base appropriata:
   ```bash
   # Per feature
   git checkout -b feature/nuova-feature staging
   
   # Per bugfix
   git checkout -b bugfix/fix-problema main
   ```
3. **Sviluppa** seguendo le coding standards
4. **Test** il codice localmente
5. **Commit** con messaggi descrittivi
6. **Push** e crea Pull Request

#### Coding Standards

##### PHP (WordPress)
- Segui [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
- Usa camelCase per variabili, snake_case per funzioni
- Documenta funzioni con PHPDoc
- Evita query SQL dirette, usa WP Query API

```php
/**
 * Retrieves FAAC product data from API
 *
 * @param int $product_id Product ID
 * @return array|WP_Error Product data or error
 */
function faac_get_product_data( $product_id ) {
    // Implementation
}
```

##### CSS/SCSS
- Usa BEM methodology per classi CSS
- Organizza SCSS in componenti logici
- Evita !important quando possibile
- Mobile-first responsive design

```scss
// Good
.product-card {
    &__title {
        font-size: 1.2rem;
    }
    
    &__image {
        width: 100%;
    }
}

// Bad
.productCard {
    .title {
        font-size: 1.2rem !important;
    }
}
```

##### JavaScript
- Usa ES6+ quando possibile
- Namespace per evitare conflitti globali
- Commenta codice complesso
- Testa su browser target

```javascript
// Good
const FAAC = {
    products: {
        init() {
            // Implementation
        }
    }
};

// Bad
function initProducts() {
    // Global function
}
```

##### Twig Templates
- Usa nomi descrittivi per variabili
- Separa logica da presentazione
- Commenta template complessi
- Consistent indentation

```twig
{# Product card component #}
{% if product.title %}
    <h3 class="product-card__title">{{ product.title }}</h3>
{% endif %}
```

### Testing

#### Obbligatorio
- [ ] Test funzionalità su Chrome/Firefox/Safari
- [ ] Test responsive design (mobile/tablet/desktop)
- [ ] Verifica accessibilità base (contrasto, focus)
- [ ] PHP lint senza errori
- [ ] Nessun errore JavaScript in console

#### Consigliato
- Test su browser aggiuntivi (Edge, Opera)
- Test con screen reader
- Performance testing (GTMetrix)
- Cross-browser compatibility

### Commit Messages

Usa conventional commits format:

```
type(scope): subject

body

footer
```

#### Types
- `feat`: Nuova feature
- `fix`: Bug fix
- `docs`: Solo cambi documentazione
- `style`: Formatting, missing semicolons, etc
- `refactor`: Code change che non fix bug né aggiunge feature
- `test`: Aggiunta test
- `chore`: Changes to build process, dependencies

#### Examples
```
feat(theme): add product carousel component

Add responsive product carousel with Swiper.js integration.
Includes keyboard navigation and ARIA labels for accessibility.

Closes #123
```

```
fix(multisite): resolve domain mapping issue

Fix incorrect URL generation for subdomain installs
when SSL is enabled.

Fixes #456
```

### Security

#### Sensitive Data
- **MAI** commitare password, API keys, o database credentials
- Usa `wp-config-template.php` per esempi di configurazione
- Controlla sempre con `git diff` prima dei commit

#### WordPress Security
- Sanitizza sempre input utente
- Usa nonces per form submission
- Valida e escape output
- Segui [WordPress Security Guidelines](https://developer.wordpress.org/advanced-administration/security/)

```php
// Good
$product_id = intval( $_POST['product_id'] );
if ( wp_verify_nonce( $_POST['nonce'], 'product_action' ) ) {
    echo esc_html( get_post_meta( $product_id, 'title', true ) );
}

// Bad
$product_id = $_POST['product_id'];
echo get_post_meta( $product_id, 'title', true );
```

### Performance

#### Guidelines
- Optimize immagini prima del commit
- Minify CSS/JS per produzione
- Evita query database non necessarie
- Cache result quando possibile

#### Theme Development
- Usa `wp_enqueue_scripts` per CSS/JS
- Conditional loading per risorse specifiche
- Optimize Twig templates
- Monitor query count con Query Monitor

### Database Changes

#### Migrations
- **NON** modificare direttamente database in produzione
- Usa WP-CLI o custom migration script
- Backup sempre prima di migration
- Test migration su staging

#### Schema Changes
- Documenta cambi schema in PR description
- Includi rollback instructions
- Considera backward compatibility
- Update documentation

### Deployment

#### Pre-deployment Checklist
- [ ] Code review approvato
- [ ] Test suite verde
- [ ] Staging test completati
- [ ] Database migration pronta (se necessaria)
- [ ] Rollback plan definito

#### Post-deployment
- [ ] Smoke test su produzione
- [ ] Monitoring per errori
- [ ] Performance check
- [ ] User acceptance test

### Documentation

#### Required
- Aggiorna README.md per nuove feature
- Documenta breaking changes in CHANGELOG
- Includi inline comments per codice complesso
- Update deployment instructions se necessario

#### Helpful
- Screenshot per cambi UI
- Video demo per feature complesse
- API documentation per nuovi endpoint
- Architecture diagrams per cambi strutturali

### Questions?

Se hai domande su questi guidelines o need help:

1. Check existing issues e documentation
2. Ask in pull request comments
3. Contact maintainers
4. Create new issue con tag "question"

Grazie per aver contribuito a rendere FAAC Multisite migliore! 🚀
