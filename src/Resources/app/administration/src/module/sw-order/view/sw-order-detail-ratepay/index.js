/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import template from './sw-order-detail-ratepay.html.twig';

const {Component} = Shopware;
const {mapState} = Component.getComponentHelper();

Shopware.Component.register('sw-order-detail-ratepay', {
    template,

    metaInfo() {
        return {
            title: 'Ratepay'
        };
    },

    computed: {
        ...mapState('swOrderDetail', [
            'order',
        ]),
    },

    created() {
        this.$emit('loading-change', false);
    }
});

Shopware.Module.register('sw-order-detail-tab-ratepay', {
    routeMiddleware(next, currentRoute) {
        if (currentRoute.name === 'sw.order.detail') {
            currentRoute.children.push({
                name: 'sw.order.detail.ratepay',
                path: '/sw/order/detail/:id/ratepay', // TODO maybe the path before "ratepay" can be removed
                component: 'sw-order-detail-ratepay',
                meta: {
                    parentPath: "sw.order.detail",
                    meta: {
                        parentPath: 'sw.order.index',
                        privilege: 'order.viewer',
                    },
                }
            });
        }
        next(currentRoute);
    }
});
