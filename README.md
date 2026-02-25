# Digital Italia WordPress Theme

## Demo

Visualizza il tema in azione: [https://wp-digital-italia.programmato.it/](https://wp-digital-italia.programmato.it/)

## Descrizione

Il tema WordPress "Digital Italia" è basato sul framework Bootstrap Italia e progettato per creare siti web conformi agli standard del design italiano per la web. Ideale per siti governativi, istituzionali e della Pubblica Amministrazione, con particolare attenzione alla conformità alle linee guida AgID.

## Funzionalità Principali

### Framework e Design

- **Bootstrap Italia**: Integrazione completa con il framework ufficiale per un design moderno, responsivo e conforme alle linee guida di design per i siti della PA
- **Font Titillium Web e Roboto Mono**: Font ufficiali inclusi per la tipografia
- **Layout responsivo**: Adattamento automatico per desktop, tablet e mobile
- **Supporto RTL**: Compatibilità con lingue da destra a sinistra
- **Vite**: Build system moderno per JavaScript e CSS

### Sistema di Prenotazione Appuntamenti

Sistema completo per la gestione delle prenotazioni online:

- **Gestione Slot**: Creazione, modifica ed eliminazione di slot orari per appuntamenti
- **Generazione Automatica**: Generazione batch di slot per range di date con configurazione flessibile (orari, durata, pausa pranzo, intervallo tra slot)
- **Calendario Frontend**: Interfaccia guidata per i cittadini (selezione mese → giorno → orario)
- **Gestione Giorni di Chiusura**: Supporto per giorni festivi singoli e ricorrenze settimanali
- **Email Automatiche**: Conferma prenotazione all'utente e notifica all'amministratore
- **API AJAX**: Endpoint per interazione frontend/backend

### Custom Post Types

- **Servizio**: Post type per la gestione dei servizi offerti dall'ente
- **Appuntamento**: Post type per le prenotazioni, con metabox per dati richiedente, date e servizio associato
- **Persona**: Post type per le persone che compongono l'organizzazione, con supporto per foto e metabox per dati anagrafici
- **Dipartimento**: Post type gerarchico per i dipartimenti dell'organizzazione, con logo e metabox informativi
- **Bando**: Post type per bandi di gara e concorsi, con URL personalizzato `/bandi/{anno}/{slug}/`

### Tassonomie

- **Tipologia Persona**: Tassonomia per categorizzare le persone (es. Dirigente, Funzionario, etc.)
- **Argomento Bando**: Tassonomia per categorizzare i bandi

### Menu e Navigazione

- **7 Posizioni Menu**: Top Menu, Main Menu, Footer One/Two/Three/Four, Footer Bottom
- **Menu Walker Bootstrap 5**: Tre walker personalizzati per menu compliant con Bootstrap Italia:
  - `bootstrap_5_wp_main_menu_walker`: Menu principale con dropdown
  - `bootstrap_5_wp_simple_menu_walker`: Menu semplice
  - `bootstrap_5_wp_inline_menu_walker`: Menu inline

### Breadcrumbs

Sistema di breadcrumb completo con:
- Supporto microdata Schema.org
- Navigazione per pagine, post, CPT, archivi, tassonomie
- Integrazione con Elementor

### Cookie Consent (GDPR)

- **Banner Cookie**: Barra di consenso configurabile
- **Modal Preferenze**: Gestione granulare dei consensi
- **Tipologie Supportate**: Analytics, Marketing, Social, YouTube
- **Dispatcher JavaScript**: Sistema per attivare/fallback script in base alle preferenze

### WordPress Customizer

Sezioni dedicate per:
- **Site Contact**: Email, telefono, indirizzo, P.IVA, città, URP, Amministrazione Trasparente
- **Site Socials**: Facebook, Instagram, Twitter
- **Cookies Settings**: Configurazione banner, scadenza, pagina privacy, tipologie cookie
- **Loghi**: Logo principale, logo amministrazione appartenenza, logo chiaro per footer

### Integrazioni

- **Elementor**: Supporto nativo con breadcrumb automatici e ottimizzazione risorse
- **Jetpack**: Compatibilità con il plugin Jetpack
- **CMB2**: Libreria inclusa per metabox avanzati con estensioni:
  - Font Awesome picker
  - Select2 fields
  - Attached posts
  - Conditional logic

### Blocchi Gutenberg

Override del blocco "Ultimi Post" con styling Bootstrap Italia (card, grid/list layout)

### Template di Pagina

- **page-booking.php**: Template per la prenotazione appuntamenti
- **page-home.php**: Template homepage
- **page-full.php**: Template a larghezza piena

### Template Single

- **single-servizio.php**: Template per singolo servizio
- **single-persona.php**: Template per singola persona
- **single-dipartimento.php**: Template per singolo dipartimento
- **single-bando.php**: Template per singolo bando

### Template Archive

- **archive-persona.php**: Archivio persone
- **archive-bando.php**: Archivio bandi

## Requisiti

- WordPress 5.0+
- PHP 7.4+

## Installazione

1. Scarica il tema dal repository
2. Carica la cartella `wp-digital-italia` in `wp-content/themes/`
3. Attiva il tema da Aspetto > Temi
4. Configura le opzioni dal Customizer (Aspetto > Personalizza)

## Personalizzazione

### Child Theme

Per personalizzazioni che sopravvivono agli aggiornamenti, creare un child theme:

```css
/*
Theme Name: Digital Italia Child
Template: wp-digital-italia
Version: 1.0.0
*/

/* Aggiungi le tue personalizzazioni CSS qui */
```

### Personalizzare il Cookie Dispatcher

Il tema include un oggetto JavaScript `cookiesDispatcher` per gestire le callback delle scelte cookie. Per personalizzarlo:

1. Deregistra lo script predefinito nel functions.php del child theme:

```php
function deregister_default_cookies_dispatcher() {
    wp_deregister_script('cookies-settings');
}
add_action('wp_enqueue_scripts', 'deregister_default_cookies_dispatcher', 100);
```

2. Registra il tuo script personalizzato:

```php
function register_custom_cookies_dispatcher() {
    wp_enqueue_script('custom-cookies-dispatcher', get_stylesheet_directory_uri() . '/js/custom-cookies-dispatcher.js', ['jquery'], '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'register_custom_cookies_dispatcher');
```

3. Implementa le callback:

```javascript
const cookiesDispatcher = {
    cookies_settings_consent_analytics: {
        activate: function () {
            // Codice per attivare analytics
        },
        fallback: function () {
            // Codice per disattivare analytics
        }
    }
}
```

## Database Tables

Il tema crea automaticamente le seguenti tabelle al momento dell'attivazione:

- `wp_booking_slots`: Gestione degli slot orari
- `wp_booking_reservations`: Gestione delle prenotazioni
- `wp_booking_closed_days`: Giorni di chiusura

## Hooks e Filtri

### Filtri Utili

- `wpdi_breadcrumbs_defaults`: Personalizza le opzioni dei breadcrumb
- `digital_italia_custom_background_args`: Personalizza il background

### Funzioni Helper

- `wpdi_get_breadcrumbs($args)`: Restituisce l'HTML dei breadcrumb
- `wpdi_breadcrumbs($args)`: Stampa i breadcrumb
- `get_menu_by_location($location)`: Ottiene le voci menu per location
- `dci_filter_uo_with_services($uos)`: Filtra UO che offrono servizi
- `dci_uo_offers_service($uo_id, $service_id)`: Verifica se una UO offre un servizio

## API AJAX

### Frontend (pubbliche)

- `get_available_appointments`: Ottiene mesi/giorni/slot disponibili
- `submit_booking`: Invia una prenotazione

### Admin (autenticazione richiesta)

- `swb_get_slots`: Ottiene slot per mese
- `swb_delete_slot`: Elimina uno slot
- `swb_quick_add_slot`: Aggiunge rapidamente uno slot
- `swb_generate_range`: Genera slot per un range di date
- `swb_bulk_delete_month`: Elimina tutti gli slot di un mese
- `swb_add_closed_day`: Aggiunge un giorno di chiusura
- `swb_remove_closed_day`: Rimuove un giorno di chiusura

## File Structure

```
wp-digital-italia/
├── bootstrap-italia/          # Framework Bootstrap Italia
├── classes/                   # Walker per menu
├── inc/
│   ├── admin/tipologie/       # Custom Post Types
│   ├── includes/              # Sistema booking
│   ├── lib/                   # Librerie (CMB2, TGM, etc.)
│   └── *.php                  # Funzionalità core
├── js/                        # JavaScript frontend
├── assets/                    # CSS, immagini
├── page-templates/            # Template di pagina
├── template-parts/            # Parti di template
├── wp-digital-italia-extension/ # Estensioni CPT
└── *.php                      # File template principali
```

## Contributi

Contributi benvenuti! Apri una issue o una pull request sul repository.

## Licenza

Distribuito sotto licenza MIT.

## Contatti

Per assistenza: [luca.terribili@tten.it](mailto:luca.terribili@tten.it)
