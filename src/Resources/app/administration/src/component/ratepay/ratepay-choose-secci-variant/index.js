/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import template from './ratepay-choose-secci-variant.html.twig';
import './ratepay-choose-secci-variant.scss';
import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

const { Criteria } = Shopware.Data;

Shopware.Component.register('ratepay-choose-secci-variant', {
    template,

    inject: ['repositoryFactory'],

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    emits: ['update:value'],

    props: {
        value: {
            type: [String, Number],
            required: false,
            default: null
        },
        label: {
            type: String,
            required: false,
            default: ''
        },
        disabled: {
            type: Boolean,
            required: false,
            default: false
        }
    },

    data() {
        return {
            salesChannelsWithSecciDefaultPaymentMethod: [],
            groupName: `ratepay-secci-${Shopware.Utils.createId()}`,
            variants: [
                { value: '1', snippet: 'above', image: 'rpaypayments/administration/static/img/secci-above.svg' },
                { value: '2', snippet: 'below', image: 'rpaypayments/administration/static/img/secci-below.svg' }
            ]
        };
    },

    async created() {
        this.salesChannelsWithSecciDefaultPaymentMethod = await this.loadSalesChannelsRequiringSecci();
    },

    computed: {
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        },
    },

    methods: {
        async loadSalesChannelsRequiringSecci() {
            const repository = this.repositoryFactory.create('ratepay_profile_config_method');
            const criteria = new Criteria();
            criteria.addFilter(Criteria.equals('requireSecci', true));
            criteria.addFilter(Criteria.equals('profile.status', true));
            criteria.addAssociation('profile.salesChannel');
            criteria.addSorting(Criteria.sort('id', 'ASC'));

            const salesChannels = new Map();
            const configurations = await repository.search(criteria, Shopware.Context.api);
            configurations.forEach((configuration) => {
                const salesChannel = configuration.profile?.salesChannel;

                // SECCI is configured per profile, so match within the same sales channel.
                if (salesChannel && salesChannel.paymentMethodId === configuration.paymentMethodId) {
                    salesChannels.set(salesChannel.id, salesChannel.translated.name);
                }
            });

            console.info("Sales channels with SECCI:", Array.from(salesChannels.values()).join(', '));
            return Array.from(salesChannels.values());
        },

        isSelected(value) {
            return String(this.value) === value;
        },

        selectVariant(value) {
            if (!this.disabled) {
                this.$emit('update:value', value);
            }
        }
    }
});
