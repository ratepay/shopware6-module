// Import all necessary Storefront plugins and scss files
import RatepayPayments from './RatepayPayments/RatepayPayments';

// Register them via the existing PluginManager
const PluginManager = window.PluginManager;
PluginManager.register('RatepayPayments', RatepayPayments);
