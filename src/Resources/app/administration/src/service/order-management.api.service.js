/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import ApiService from 'src/core/service/api.service';

/**
 * @class
 * @property {AxiosInstance} httpClient
 */
export default class OrderManagementApiService extends ApiService {
    /**
     * @constructor
     * @param {AxiosInstance} httpClient
     * @param {LoginService} loginService
     */
    constructor(httpClient, loginService) {
        super(httpClient, loginService, 'ratepay/order-management');
        this.httpClient = httpClient;
        this.loginService = loginService;
        this.name = 'ratepayOrderManagementService';
    }

    load(orderId) {
        return this.httpClient
            .get(this.getApiBasePath() + '/load/' + orderId,
                {
                    headers: this.getBasicHeaders()
                }
            ).then(response => response.data)
    }


    doAction(action, orderId, items, updateStock) {
        return this.httpClient
            .post(this.getApiBasePath() + '/' + action + '/' + orderId,
                {
                    items: items,
                    updateStock: typeof updateStock == 'boolean' ? updateStock : null
                },
                {
                    headers: this.getBasicHeaders()
                }
            ).then(response => response.data)
    }

    addItem(orderId, name, grossAmount, taxId) {
        return this.httpClient
            .post(this.getApiBasePath() + '/addItem/' + orderId,
                {
                    name,
                    grossAmount,
                    taxId
                },
                {
                    headers: this.getBasicHeaders()
                }
            ).then(response => response.data)
    }
}
