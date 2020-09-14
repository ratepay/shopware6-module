/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import template from './sw-order-detail-base.html.twig';

const { Component } = Shopware;

Component.override('sw-order-detail-base', {
    template,

    methods: {

        onRatepayActionTriggered() {
            this.$refs.ratepayOrderHistory.loadOrderHistory();
            this.reloadOrder();
            // Preferred way, but the modal for debits and credits is not closing with it:
            // this.reloadEntityData();
        },

        reloadOrder() {
            return this.orderRepository.get(this.orderId, this.versionContext, this.orderCriteria).then((response) => {
                this.order = response;
                return Promise.resolve();
            }).catch(() => {
                return Promise.reject();
            });
        }

    }
});
