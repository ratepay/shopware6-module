/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import template from './ratepay-order-history-log-grid.html.twig';

const {Component} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('ratepay-order-history-log-grid', {
    template,

    inject: [
        'repositoryFactory'
    ],

    props: ['order'],

    data() {
        return {
            repository: null,
            entities: null
        };
    },

    computed: {
        columns() {
            return [
                {
                    property: 'createdAt',
                    dataIndex: 'createdAt',
                    label: this.$tc('ratepay.order-log-history.detailBase.column.date'),
                    allowResize: false
                },
                {
                    property: 'user',
                    dataIndex: 'user',
                    label: this.$tc('ratepay.order-log-history.detailBase.column.user'),
                    allowResize: false
                },
                {
                    property: 'event',
                    dataIndex: 'event',
                    label: this.$tc('ratepay.order-log-history.detailBase.column.event'),
                    allowResize: false
                },
                {
                    property: 'productName',
                    dataIndex: 'productName',
                    label: this.$tc('ratepay.order-log-history.detailBase.column.name'),
                    allowResize: true
                },
                {
                    property: 'quantity',
                    dataIndex: 'quantity',
                    label: this.$tc('ratepay.order-log-history.detailBase.column.count'),
                    allowResize: true
                }
            ];
        }
    },

    created() {
        this.loadOrderHistory();
    },

    methods: {
        loadOrderHistory() {
            this.repository = this.repositoryFactory.create('ratepay_order_history');

            let criteria = new Criteria();
            criteria.addFilter(Criteria.equals('orderId', this.order.id));
            criteria.addSorting(Criteria.sort('createdAt', 'DESC'));

            this.repository
                .search(criteria, Shopware.Context.api)
                .then((result) => {
                    this.entities = result;
                    this.reload = false;
                });
        }
    }
});
