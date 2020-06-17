/*
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import OrderManagement from '../service/order-management.api.service.js';

Shopware.Application.addServiceProvider('order-management-service', container => {
    const initContainer = Shopware.Application.getContainer('init');
    return new OrderManagement(initContainer.httpClient, container.loginService);
});
