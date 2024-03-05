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
export default class AdminOrderLoginTokenApiService extends ApiService {

    /**
     * @constructor
     * @param {AxiosInstance} httpClient
     * @param {LoginService} loginService
     */
    constructor(httpClient, loginService) {
        super(httpClient, loginService, '_action/ratepay/admin-order');
        this.httpClient = httpClient;
        this.loginService = loginService;
        this.name = 'RatepayAdminOrderTokenAService';
    }

    requestTokenUrl(salesChannelId, salesChannelDomainId) {
        return this.httpClient
            .post(this.getApiBasePath() + '/create-storefront-url',
                {
                    salesChannelId: salesChannelId,
                    salesChannelDomainId: salesChannelDomainId
                },
                {
                    headers: this.getBasicHeaders()
                }
            ).then(response => response.data)
    }
}
