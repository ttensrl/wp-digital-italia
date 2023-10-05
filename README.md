# Digital Italia WordPress Theme

## Descrizione

Il tema WordPress "Digital Italia" è basato sul framework Bootstrap Italia e è progettato per creare un sito web conforme agli standard del design italiano per il web. Questo tema offre una solida base per la creazione di siti web governativi, istituzionali o corporate con un'attenzione particolare alla conformità alle linee guida dell'AgID.

## Funzionalità Principali

- Integrazione completa con Bootstrap Italia per un design moderno e responsivo.
- Compatibilità con le linee guida dell'AgID per l'accessibilità e l'usabilità.
- Layout flessibile e personalizzabile per adattarsi alle esigenze del tuo progetto.
- Pannelli di personalizzazione per il controllo del logo, del colore e di altri aspetti del design.
- Supporto per widget personalizzati per l'aggiunta di contenuti aggiuntivi.
- Ottimizzato per la velocità e la sicurezza.

## Installazione

1. Scarica il tema "Digital Italia" dal repository GitHub.
2. Carica il tema nella cartella `wp-content/themes/` della tua installazione di WordPress.
3. Attiva il tema dalla sezione "Aspetto" nel tuo pannello di controllo WordPress.

# Personalizzazione del Tema "Digital Italia" s

Puoi personalizzare il tema "Digital Italia" attraverso il pannello di personalizzazione di WordPress. Questo ti consente di apportare modifiche al logo, ai colori principali e ad altri aspetti del design per adattarli al tuo progetto.

## Creazione di un Child Theme

Per personalizzare ulteriormente il tema "Digital Italia" e garantire che le tue modifiche sopravvivano agli aggiornamenti del tema principale, puoi creare un child theme. Ecco come farlo:

1. **Crea una Cartella per il Child Theme**: Nella directory dei temi del tuo sito WordPress (di solito `wp-content/themes/`), crea una nuova cartella per il tuo child theme. Ad esempio, puoi chiamarla "digital-italia-child".

2. **Crea un File style.css**: All'interno della cartella del tuo child theme, crea un file chiamato `style.css`. In questo file, specifica le informazioni del tuo child theme. Ecco un esempio:

    ```css
    /*
    Theme Name: Digital Italia Child
    Template: digital-italia
    Version: 1.0.0
    */

    /* Aggiungi le tue personalizzazioni CSS qui */
    ```

   Assicurati di sostituire "Digital Italia Child" con il nome desiderato per il tuo child theme e "digital-italia" con il nome del tema genitore che desideri ereditare.

3. **Attiva il Tuo Child Theme**: Vai al pannello di amministrazione di WordPress e attiva il tuo child theme dalla sezione "Aspetto" > "Temi".

4. **Aggiungi Personalizzazioni**: Ora puoi aggiungere le tue personalizzazioni CSS o altri file al tuo child theme. Qualsiasi file con lo stesso nome e percorso del tema genitore verrà sovrascritto dal tuo child theme.

## Personalizzazione dell'oggetto JavaScript `cookiesDispatcher`

Il tema "Digital Italia" include un oggetto JavaScript chiamato `cookiesDispatcher`, che gestisce le preferenze dei cookie scelte dall'utente. Se desideri personalizzare questo oggetto, segui questi passaggi:

1. **Deregistra lo Script Predefinito**:

   Nel tuo child theme, puoi deregistrare lo script predefinito `cookies-dispatcher` utilizzando la funzione `wp_deregister_script()`. Assicurati di farlo all'interno del file `functions.php` del tuo child theme. Ecco un esempio:

    ```php
    function deregister_default_cookies_dispatcher() {
        wp_deregister_script('cookies-dispatcher');
    }
    add_action('wp_enqueue_scripts', 'deregister_default_cookies_dispatcher', 100);
    ```

2. **Registra il Tuo Script Personalizzato**:

   Dopo aver deregistrato lo script predefinito, puoi registrare il tuo script personalizzato all'interno del file `functions.php` del tuo child theme utilizzando `wp_enqueue_script()`. Ecco un esempio:

    ```php
    function register_custom_cookies_dispatcher() {
        wp_enqueue_script('custom-cookies-dispatcher', get_stylesheet_directory_uri() . '/js/custom-cookies-dispatcher.js', array('jquery'), '1.0.0', true);
    }
    add_action('wp_enqueue_scripts', 'register_custom_cookies_dispatcher');
    ```

   Assicurati di sostituire `'custom-cookies-dispatcher'` con un nome univoco per il tuo script personalizzato e specifica il percorso corretto al tuo file JavaScript all'interno del child theme.

3. **Personalizza l'oggetto `cookiesDispatcher`**:

   Ora puoi personalizzare l'oggetto `cookiesDispatcher` nel tuo file JavaScript personalizzato (`custom-cookies-dispatcher.js`). Implementa le funzioni `activate` e `fallback` per gestire le callback delle scelte dell'utente per ciascun cookie specifico. Ecco un esempio:

    ```javascript
    const cookiesDispatcher = {
        cookies_settings_consent_analytics: {
            activate: function () {
                // Codice per l'attivazione dei cookie di analytics
            },
            fallback: function () {
                // Codice per il fallback dei cookie di analytics
            }
        },
        // Altri cookie settings...
    }
    ```

   Personalizza le funzioni `activate` e `fallback` in base alle esigenze del tuo progetto e delle tue preferenze relative ai cookie.

Con questi passaggi, hai creato un child theme per personalizzare il tema "Digital Italia" e hai personalizzato l'oggetto JavaScript `cookiesDispatcher` per gestire le preferenze dei cookie nel tuo sito WordPress.

## Contributi

Se desideri contribuire allo sviluppo del tema "Digital Italia", sei il benvenuto! Puoi inviare suggerimenti, segnalare problemi o proporre modifiche tramite il repository GitHub del tema.

## Licenza

Il tema "Digital Italia" è distribuito con licenza open source sotto i termini della licenza MIT. Puoi utilizzarlo, modificarlo e distribuirlo gratuitamente.

## Contatti

Per ulteriori informazioni o assistenza, contatta il nostro team all'indirizzo email [luca.terribili@tten.it](mailto:luca.terribili@tten.it).

Grazie per aver scelto il tema "Digital Italia" per il tuo sito web WordPress!
