/*
 * Copyright (c) 2020 RatePAY GmbH
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

    /**
     * @returns {Promise<{id: number, category: string, type: string, joke: ?string, setup: ?string, delivery: ?string}>}
     */
    load(orderId) {
        return this.httpClient
            .get(this.getApiBasePath() + '/load/' + orderId,
                {
                    headers: this.getBasicHeaders()
                }
            ).then(response => response.data)
    }
}
