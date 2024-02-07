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

    computed: {
        salesChannelDomains() {
            return this.selectedSalesChannelId ? this.salesChannels.get(this.selectedSalesChannelId)?.domains ?? [] : [];
        },
    },

    methods: {
        navigateToFrontend() {
            this.ratepayAdminOrderLoginTokenService.requestTokenUrl(
                this.selectedSalesChannelId,
                this.selectedSalesChannelDomainId
            ).then((response) => {
                window.open(response.url);
            });
        }
    }
});
