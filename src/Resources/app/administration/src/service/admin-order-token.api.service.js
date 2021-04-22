import ApiService from 'src/core/service/api.service';

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
        super(httpClient, loginService, 'ratepay/admin-order');
        this.httpClient = httpClient;
        this.loginService = loginService;
        this.name = 'RatepayAdminOrderTokenAService';
    }


    requestTokenUrl(salesChannelId, salesChannelDomainId) {
        return this.httpClient
            .post(this.getApiBasePath() + '/login-token',
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
