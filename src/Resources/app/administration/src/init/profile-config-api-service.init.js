/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import ProfileConfigApiService from '../service/profile-config.api.service.js';

Shopware.Application.addServiceProvider('ratepay-profile-config', container => {
    const initContainer = Shopware.Application.getContainer('init');
    return new ProfileConfigApiService(initContainer.httpClient, container.loginService);
});
