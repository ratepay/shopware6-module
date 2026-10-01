/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import template from './sw-sales-channel-detail-base.html.twig';
import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

const { Criteria } = Shopware.Data;

Shopware.Component.override('sw-sales-channel-detail-base', {
    template,

    inject: ['systemConfigApiService'],

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    data() {
        return {
            ratepayDefaultPaymentMethodRequiresSecci: false
        };
    },

    computed: {
        ratepayDefaultPaymentMethodKey() {
            const { id, paymentMethodId } = this.salesChannel ?? {};

            return id && paymentMethodId ? `${id}:${paymentMethodId}` : null;
        }
    },

    watch: {
        ratepayDefaultPaymentMethodKey: {
            immediate: true,
            handler() {
                this.loadRatepayDefaultPaymentMethodSecciRequirement();
            }
        }
    },

    methods: {
        async loadRatepayDefaultPaymentMethodSecciRequirement() {
            const key = this.ratepayDefaultPaymentMethodKey;
            this.ratepayDefaultPaymentMethodRequiresSecci = false;

            if (!key) {
                return;
            }

            const criteria = new Criteria(1, 1);
            criteria.addFilter(Criteria.equals('requireSecci', true));
            criteria.addFilter(Criteria.equals('profile.status', true));
            criteria.addFilter(Criteria.equals('profile.salesChannelId', this.salesChannel.id));
            criteria.addFilter(Criteria.equals('paymentMethodId', this.salesChannel.paymentMethodId));

            try {
                const config = await this.systemConfigApiService.getValues('RpayPayments.config');

                if (config['RpayPayments.config.ratepaySecciVariant'] != 1) {
                    return;
                }

                const configurations = await this.repositoryFactory
                    .create('ratepay_profile_config_method')
                    .search(criteria, Shopware.Context.api);

                // Ignore responses for a previously selected channel or payment method.
                if (key === this.ratepayDefaultPaymentMethodKey) {
                    this.ratepayDefaultPaymentMethodRequiresSecci = configurations.length > 0;
                }
            } catch (error) {
                Shopware.Utils.debug.error('sw-sales-channel-detail-base', error);
            }
        }
    }
});
