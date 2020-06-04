/*
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import template from './ratepay-article-panel.html.twig';
import './article-panel.scss';

const { Component } = Shopware;

Component.register('ratepay-article-panel', {
    template,

    data() {
        return {
            data: [],
            activeTab: 'shipping'
        };
    },

    computed: {
        columns() {
            return [
                {
                    property: 'count',
                    label: this.$t('ratepay.article_panel.labels.count'),
                    allowResize: false
                },
                {
                    property: 'articlename',
                    label: this.$t('ratepay.article_panel.labels.articlename'),
                    allowResize: false
                },
                {
                    property: 'price',
                    label: this.$t('ratepay.article_panel.labels.price'),
                    allowResize: false
                },
                {
                    property: 'ordered',
                    label: this.$t('ratepay.article_panel.labels.ordered'),
                    allowResize: false
                },
                {
                    property: 'shipped',
                    label: this.$t('ratepay.article_panel.labels.shipped'),
                    allowResize: false
                },
                {
                    property: 'canceled',
                    label: this.$t('ratepay.article_panel.labels.canceled'),
                    allowResize: false
                },
                {
                    property: 'returned',
                    label: this.$t('ratepay.article_panel.labels.returned'),
                    allowResize: false
                }
            ];
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.data = [];
            this.data.push({
                articlename: 'Article 1',
                price: '1200',
                ordered: '1',
                shipped: '1',
                canceled: '0',
                returned: '0'
            },{
                articlename: 'Article 2',
                price: '1200',
                ordered: '1',
                shipped: '1',
                canceled: '0',
                returned: '0'
            });
        },

        isLoading() {
            return this.$parent.isLoading;
        }
    },
});
