import 'bootstrap-italia/dist/css/bootstrap-italia.min.css'
import * as bootstrapItalia from 'bootstrap-italia'
import './cookies-settings'
import 'leaflet/dist/leaflet.css'
import L from 'leaflet'

window.bootstrapItalia = bootstrapItalia
L.Icon.Default.prototype.options.imagePath = '/wp-content/themes/wp-digital-italia/dist/images/'