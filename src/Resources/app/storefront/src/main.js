import RatepayPayments from './RatepayPayments/RatepayPayments';

window.PluginManager.register('KlarnaPayments', KlarnaPayments, '[data-is-klarna-payments]');

if (module.hot) {
    module.hot.accept();
}
