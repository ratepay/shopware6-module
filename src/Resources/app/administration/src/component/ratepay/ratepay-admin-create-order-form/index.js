/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import template from './ratepay-admin-create-order-form.twig';
import deDE from "./snippet/de-DE.json";
import enGB from "./snippet/en-GB.json";

const {Component} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('ratepay-admin-create-order-form', {
    template,

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    inject: ['repositoryFactory', 'ratepayAdminOrderLoginTokenService'],

    data() {
        return {
            loading: false,
            salesChannels: null,
            salesChannelDomains: null,

            selectedSalesChannelId: null,
            selectedSalesChannelDomainId: null,

            salesChannelRepository: null,
            salesChannelDomainRepository: null
        };
    },

    created() {
        this.salesChannelRepository = this.repositoryFactory.create('sales_channel');
        this.salesChannelDomainRepository = this.repositoryFactory.create('sales_channel_domain');

        let criteria = new Criteria();
        criteria.addFilter(Criteria.not('AND', [Criteria.equals('domains.url', null)]));
        criteria.addFilter(Criteria.equals('active', true));
        criteria.addAssociation('domains');

        this.loading = true;
        this.salesChannelRepository
            .search(criteria, Shopware.Context.api)
            .then((result) => {
                this.salesChannels = result.filter((item) => {
                    return item.domains.length > 0;
                });
                this.loading = false;
            });
    },

    methods: {
        selectSalesChannel() {
            let criteria = new Criteria();
            criteria.addFilter(Criteria.equals('salesChannelId', this.selectedSalesChannelId));

            this.loading = true;
            this.salesChannelDomainRepository
                .search(criteria, Shopware.Context.api)
                .then((result) => {
                    this.salesChannelDomains = result;
                });
        },

        navigateToFrontend() {
            this.ratepayAdminOrderLoginTokenService.requestTokenUrl(
                this.selectedSalesChannelId,
                this.selectedSalesChannelDomainId
            ).then((response) => {
                console.log(arguments);
                window.open(response.url);
            });
        }
    }
});
