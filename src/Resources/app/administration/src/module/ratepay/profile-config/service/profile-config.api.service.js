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
export default class ProfileConfigApiService extends ApiService {
    /**
     * @constructor
     * @param {AxiosInstance} httpClient
     * @param {LoginService} loginService
     */
    constructor(httpClient, loginService) {
        super(httpClient, loginService, 'ratepay/profile-configuration');
        this.httpClient = httpClient;
        this.loginService = loginService;
        this.name = 'ratepayConfigService';
    }

    /**
     * @returns {Promise<{id: number, category: string, type: string, joke: ?string, setup: ?string, delivery: ?string}>}
     */
    reloadConfig(id) {
        return this.httpClient
            .post(this.getApiBasePath()+'/reload-config/',
                {
                    id: id
                },
                {
                    headers: this.getBasicHeaders()
                }
            ).then(response => response.data)
    }
}
