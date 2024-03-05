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
export default class OrderManagementApiService extends ApiService {

    /**
     * @constructor
     * @param {AxiosInstance} httpClient
     * @param {LoginService} loginService
     */
    constructor(httpClient, loginService) {
        super(httpClient, loginService, '_action');
        this.httpClient = httpClient;
        this.loginService = loginService;
        this.name = 'ratepayOrderManagementService';
    }
    _getPath(orderId, action) {
        return this.getApiBasePath() + '/order/' + orderId + '/ratepay/' + action;
    }

    load(orderId) {
        return this.httpClient
            .get(this._getPath(orderId, 'info'),
                {
                    headers: this.getBasicHeaders()
                }
            ).then(response => response.data)
    }


    doAction(action, orderId, items, updateStock) {
        return this.httpClient
            .post(this._getPath(orderId, action),
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
            .post(this._getPath(orderId, 'addItem'),
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
