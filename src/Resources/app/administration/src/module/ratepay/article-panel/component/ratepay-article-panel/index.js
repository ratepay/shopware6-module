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
            data: []
        };
    },

    computed: {
        columns() {
            return [
                {
                    property: 'count',
                    label: 'count',
                    allowResize: false
                },
                {
                    property: 'articlename',
                    label: 'articlename',
                    allowResize: false
                },
                {
                    property: 'price',
                    label: 'price',
                    allowResize: true
                },
                {
                    property: 'ordered',
                    label: 'ordered',
                    allowResize: false
                },
                {
                    property: 'shipped',
                    label: 'shipped',
                    allowResize: false
                },
                {
                    property: 'canceled',
                    label: 'canceled',
                    allowResize: false
                },
                {
                    property: 'returned',
                    label: 'returned',
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
