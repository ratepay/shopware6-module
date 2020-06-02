// Import all necessary Storefront plugins and scss files
import RatepayCheckout from './RatepayCheckout/RatepayCheckout';

// Register them via the existing PluginManager
const PluginManager = window.PluginManager;
PluginManager.register('RatepayCheckout', RatepayCheckout);
