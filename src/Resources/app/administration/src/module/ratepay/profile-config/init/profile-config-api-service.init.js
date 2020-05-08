import ProfileConfigApiService from '../service/profile-config.api.service.js';

Shopware.Application.addServiceProvider('ratepay-profile-config', container => {
    const initContainer = Shopware.Application.getContainer('init');
    return new ProfileConfigApiService(initContainer.httpClient, container.loginService);
});
