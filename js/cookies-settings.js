const DATA_KEY = 'bs.cookiebar';
const EVENT_KEY = `.${DATA_KEY}`;
const EVENT_CLOSE = `close${EVENT_KEY}`;
const banner = document.querySelector('.cookiebar');
//const cookieBar = bootstrap.Cookiebar.getOrCreateInstance();

console.log(cookiesVars, cookiesSettings);

const preferencesLink = document.getElementById('cookies-settings');
preferencesLink.addEventListener("click", function (event) {
    event.preventDefault();
    const cookieModal = new bootstrap.Modal(document.getElementById('cookieModal'));
    cookieModal.show();
});

banner.addEventListener(EVENT_CLOSE, function (event) {
    // TRIGGER EVENTO
});