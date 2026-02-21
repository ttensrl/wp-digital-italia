(function() {
    'use strict';

    window.initDipartimentoMap = function(mapId, lat, lng, label) {
        var mapElement = document.getElementById(mapId);
        if (!mapElement || typeof L === 'undefined') {
            return;
        }

        var map = L.map(mapId).setView([lat, lng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        var marker = L.marker([lat, lng]).addTo(map);

        if (label) {
            marker.bindPopup(label).openPopup();
        }
    };

})();
