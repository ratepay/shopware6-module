import './icon.scss';

const { Component } = Shopware;

Component.register('ratepay-plugin-icon', {
    template: '<img class="ratepay-plugin-icon" :src="\'rpaypayments/plugin.png\' | asset">'
});
