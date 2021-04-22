import AdminOrderLoginTokenApiService from '../service/admin-order-token.api.service.js';

Shopware.Application.addServiceProvider('ratepayAdminOrderLoginTokenService', container => {
    const initContainer = Shopware.Application.getContainer('init');
    return new AdminOrderLoginTokenApiService(initContainer.httpClient, container.loginService);
});
