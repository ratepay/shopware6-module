/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

const { Criteria } = Shopware.Data;

Shopware.Component.override('sw-sales-channel-detail', {
    provide() {
        return {
            ratepayGetDefaultPaymentMethodRequiresSecci: () => this.ratepayDefaultPaymentMethodRequiresSecci
        };
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
        async onSave() {
            if (this.ratepayDefaultPaymentMethodRequiresSecci) {
                return;
            }

            await this.loadRatepayDefaultPaymentMethodSecciRequirement();

            if (this.ratepayDefaultPaymentMethodRequiresSecci) {
                return;
            }

            return this.$super('onSave');
        },

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
                const configurations = await this.repositoryFactory
                    .create('ratepay_profile_config_method')
                    .search(criteria, Shopware.Context.api);

                // Ignore responses for a previously selected channel or payment method.
                if (key === this.ratepayDefaultPaymentMethodKey) {
                    this.ratepayDefaultPaymentMethodRequiresSecci = configurations.length > 0;
                }
            } catch (error) {
                Shopware.Utils.debug.error('sw-sales-channel-detail', error);
            }
        }
    }
});
