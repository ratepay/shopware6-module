/*
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import template from './ratepay-article-panel.html.twig';
import './article-panel.scss';

const { Component } = Shopware;
const {Criteria} = Shopware.Data;

Component.register('ratepay-article-panel', {
    template,

    inject: [
        'repositoryFactory'
    ],

    data() {
        return {
            data: [],
            repository: null,
            entities: null,
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
                    property: 'label',
                    label: this.$t('ratepay.article_panel.labels.articlename'),
                    allowResize: false
                },
                {
                    property: 'totalPrice',
                    label: this.$t('ratepay.article_panel.labels.price'),
                    allowResize: false
                },
                {
                    property: 'quantity',
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

        this.repository = this.repositoryFactory.create('order_line_item');
        let criteria = new Criteria();
        criteria.addFilter(Criteria.equals('orderId', this.$route.params.id));
        this.repository
            .search(criteria, Shopware.Context.api)
            .then((result) => {
                this.entities = result;
            });
    },

    methods: {
        isLoading() {
            return this.$parent.isLoading;
        }
    },
});
