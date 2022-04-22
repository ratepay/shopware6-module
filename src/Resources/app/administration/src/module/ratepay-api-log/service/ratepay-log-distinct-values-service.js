/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

const ApiService = Shopware.Classes.ApiService;

/**
 * @class
 * @property {AxiosInstance} httpClient
 */
export default class RatepayLogDistinctValuesService extends ApiService {

    /**
     * @constructor
     * @param {AxiosInstance} httpClient
     * @param {LoginService} loginService
     */
    constructor(httpClient, loginService) {
        super(httpClient, loginService, 'ratepay/api-log/distinct-values');
        this.httpClient = httpClient;
        this.name = 'RatepayApiLogDistinctValuesService';
    }


    getDistinctValues(field) {
        return this.httpClient
            .get(
                this.getApiBasePath() + '/' + field,
                {
                    headers: this.getBasicHeaders()
                }
            ).then(response => response.data)
    }
}

Shopware.Application.addServiceProvider('RatepayLogDistinctValuesService', container => {
    const initContainer = Shopware.Application.getContainer('init');
    return new RatepayLogDistinctValuesService(initContainer.httpClient, container.loginService);
});
