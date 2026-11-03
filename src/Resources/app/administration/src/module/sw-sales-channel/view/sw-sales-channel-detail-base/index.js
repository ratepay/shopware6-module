/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import template from './sw-sales-channel-detail-base.html.twig';
import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

Shopware.Component.override('sw-sales-channel-detail-base', {
    template,

    inject: ['ratepayGetDefaultPaymentMethodRequiresSecci'],

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    computed: {
        ratepayDefaultPaymentMethodRequiresSecci() {
            return this.ratepayGetDefaultPaymentMethodRequiresSecci();
        }
    }
});
