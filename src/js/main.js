// =============================================================================
// MAIN JAVASCRIPT - WP Digital Italia
// =============================================================================
// Importa solo i moduli Bootstrap Italia e librerie necessarie

// 1. Import SCSS custom (instead of precompiled CSS)
import '../scss/main.scss'

// 2. Leaflet (per mappe)
import 'leaflet/dist/leaflet.css'
import L from 'leaflet'

// 3. Bootstrap Italia - Solo i moduli utilizzati nel tema
import {
  Alert,
  BackToTop,
  Button,
  Collapse,
  Cookiebar,
  Dropdown,
  Forward,
  HeaderSticky,
  HistoryBack,
  Input,
  InputPassword,
  InputSearchAutocomplete,
  Modal,
  NavBarCollapsible,
  NavScroll,
  Notification,
  Offcanvas,
  Tab,
  Toast,
  Tooltip,
  init,
} from 'bootstrap-italia'

// 4. Custom modules
import './cookies-settings'

// 5. Export to window for global access
window.bootstrapItalia = {
  Alert,
  BackToTop,
  Button,
  Collapse,
  Cookiebar,
  Dropdown,
  Forward,
  HeaderSticky,
  HistoryBack,
  Input,
  InputPassword,
  InputSearchAutocomplete,
  Modal,
  NavBarCollapsible,
  NavScroll,
  Notification,
  Offcanvas,
  Tab,
  Toast,
  Tooltip,
  init,
}

// 6. Leaflet configuration
L.Icon.Default.prototype.options.imagePath = '/wp-content/themes/wp-digital-italia/dist/images/'
window.L = L
