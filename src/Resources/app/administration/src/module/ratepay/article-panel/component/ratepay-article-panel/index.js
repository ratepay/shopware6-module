/*
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import template from './ratepay-article-panel.html.twig';
import './article-panel.scss';

const {Component} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('ratepay-article-panel', {
    template,

    inject: {
        "orderManagementService": "ratepay-order-management"
    },

    data() {
        return {
            items: [],
            activeTab: 'shipping'
        };
    },

    computed: {
        columns() {
            return [
                {
                    property: 'quantity',
                    label: this.$t('ratepay.articlePanel.table.count'),
                    allowResize: false,
                    align: 'center'
                },
                {
                    property: 'name',
                    label: this.$t('ratepay.articlePanel.table.articleName'),
                    allowResize: false
                },
                {
                    property: 'ordered',
                    label: this.$t('ratepay.articlePanel.table.ordered'),
                    allowResize: false,
                    align: 'center'
                },
                {
                    property: 'delivered',
                    label: this.$t('ratepay.articlePanel.table.delivered'),
                    allowResize: false,
                    align: 'center'
                },
                {
                    property: 'canceled',
                    label: this.$t('ratepay.articlePanel.table.canceled'),
                    allowResize: false,
                    align: 'center'
                },
                {
                    property: 'returned',
                    label: this.$t('ratepay.articlePanel.table.returned'),
                    allowResize: false,
                    align: 'center'
                }
            ];
        }
    },

    created() {
        this.orderManagementService.load(this.$route.params.id).then(response => {
            if (response.data) {
                this.items = Object.values(response.data);
            }
        });
    },

    methods: {
        isLoading() {
            return this.$parent.isLoading;
        }
    },
});
