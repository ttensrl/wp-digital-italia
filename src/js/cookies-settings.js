import { Cookiebar, Modal } from 'bootstrap-italia'

window.cookiesDispatcher = window.cookiesDispatcher || {}
const DATA_KEY = 'bs.cookiebar';
const EVENT_KEY = `.${DATA_KEY}`;
const EVENT_CLOSE = `close${EVENT_KEY}`;
const COOKIE_NAME = 'cookies_settings';
const SETTING_ANCHOR = '#cookies-settings';
const banner = document.querySelector('.cookiebar');
const cookieBar = new Cookiebar(banner);

//console.log(cookiesVars, cookiesSettings);

const preferencesLink = document.getElementById('cookies-settings');
preferencesLink.addEventListener("click", function (event) {
    event.preventDefault();
    const cookieModal = new Modal(document.getElementById('cookieModal'));
    cookieModal.show();
});

banner.addEventListener(EVENT_CLOSE, function (event) {
    history.replaceState(null, null, ' ');
    window.dispatchEvent(new HashChangeEvent("hashchange"));
    // Inizializza un oggetto JavaScript per archiviare i risultati
    const checkboxStatus = {};
    // Utilizza forEach per iterare attraverso cookiesVars
    cookiesVars.forEach((checkboxId) => {
        const checkboxElement = document.getElementById(checkboxId);
        if (checkboxElement) {
            // Assegna il valore booleano al nome della checkbox nell'oggetto dei risultati
            checkboxStatus[checkboxId] = checkboxElement.checked;
        }
    });
    const cookiesStatus = JSON.stringify(checkboxStatus);
    const expireDate = new Date();
    expireDate.setDate(expireDate.getDate() + cookiesSettings.cookie_expire);
    const c_value = escape(cookiesStatus) + ('; expires=' + expireDate.toUTCString());
    document.cookie = COOKIE_NAME + '=' + c_value + '; path=/; SameSite=Strict';
    // Fire event
    const dispatchCookiesEvent = new Event('dispatchCookies', { bubbles: true });
    dispatchCookiesEvent.cookies = checkboxStatus;
    document.dispatchEvent(dispatchCookiesEvent);
});

/**
 * LISTENER
 */

document.addEventListener('DOMContentLoaded', function() {
    const cookieString = getCookie(COOKIE_NAME);
    if(cookieString) {
        const cookieObject = JSON.parse(cookieString);
        // Populate checkbox
        for (const propertyName in cookieObject) {
            if (cookieObject.hasOwnProperty(propertyName)) {
                const propertyValue = cookieObject[propertyName];
                if(propertyValue === true) {
                    const checkboxElement = document.getElementById(propertyName);
                    if (checkboxElement) {
                        checkboxElement.checked = true;
                    }
                }
            }
        }
        // Fire event
        const dispatchCookiesEvent = new Event('dispatchCookies', { bubbles: true });
        dispatchCookiesEvent.cookies = cookieObject;
        document.dispatchEvent(dispatchCookiesEvent);
    }
});

window.addEventListener('hashchange', function() {
    const currentHash = window.location.hash;
    if (currentHash === SETTING_ANCHOR) {
        cookieBar.show();
    }
});


document.addEventListener('dispatchCookies', (evt) => {
   if(evt.hasOwnProperty('cookies')) {
       const services = evt.cookies;
       for (const sid in services) {
           if (typeof cookiesDispatcher[sid] === 'object') {
               if (services[sid] === true && typeof cookiesDispatcher[sid].activate === 'function') {
                   cookiesDispatcher[sid].activate();
               } else if (typeof cookiesDispatcher[sid].fallback === 'function') {
                   cookiesDispatcher[sid].fallback();
               }
           }
       }
   }
});

function getCookie(name) {
    let cookieValue = null;
    if (document.cookie && document.cookie !== '') {
        const cookies = document.cookie.split(';');
        for (let i = 0; i < cookies.length; i++) {
            const cookie = cookies[i].trim();
            // Verifica se il cookie inizia con il nome specificato
            if (cookie.substring(0, name.length + 1) === (name + '=')) {
                // Estrai il valore del cookie
                cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                break;
            }
        }
    }
    return cookieValue;
}