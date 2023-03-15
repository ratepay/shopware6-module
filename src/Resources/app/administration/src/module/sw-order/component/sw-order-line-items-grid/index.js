/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import template from './sw-order-line-items-grid.html.twig';

const {Component} = Shopware;

Component.override('sw-order-line-items-grid', {
    template,

    data() {
        return {
            showLineItemDeleteRestrictionModal: false,
        }
    },

    methods: {
        onDeleteSelectedItems() {
            // method for multi-selection delete
            if (this.order.extensions.ratepayData) {
                this.showLineItemDeleteRestrictionModal = true;
            } else {
                this.$super('onDeleteSelectedItems')
            }
        },
        onDeleteItem(item, itemIndex) {
            // method for context-selection delete
            if (this.order.extensions.ratepayData) {
                this.showLineItemDeleteRestrictionModal = true;
            } else {
                this.$super('onConfirmDelete', item, itemIndex)
            }
        },
        onCloseLineItemDeleteRestrictionModal() {
            this.showLineItemDeleteRestrictionModal = false;
        }
    }
});
