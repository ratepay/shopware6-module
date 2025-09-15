import template from './sw-order-detail.html.twig';
import deDE from "./snippet/de-DE.json";
import enGB from "./snippet/en-GB.json";

const {Component} = Shopware;

Component.override('sw-order-detail', {
    template,
    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    computed: {
        orderCriteria() {
            const criteria = this.$super('orderCriteria');

            criteria.addAssociation('ratepayData');
            criteria.addAssociation('lineItems.ratepayData');

            return criteria;
        }
    }
});
