/*
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import template from './sw-order-line-items-grid.html.twig';

const { Component } = Shopware;

Component.override('sw-order-line-items-grid', {
    template,

    data() {
        return {
            showLineItemDeleteRestrictionModal: false,
        }
    },

    computed: {
        isOrderPayedWithRatepay() {
            // ToDo: Detect ratepay order

            return true;
        },
    },

    methods: {
        onDeleteSelectedItems() {
            if (this.isOrderPayedWithRatepay) {
                this.showLineItemDeleteRestrictionModal = true;
            } else {
                this.$super('onDeleteSelectedItems')
            }
        },
        onCloseLineItemDeleteRestrictionModal () {
            this.showLineItemDeleteRestrictionModal = false;
        }
    }
});
