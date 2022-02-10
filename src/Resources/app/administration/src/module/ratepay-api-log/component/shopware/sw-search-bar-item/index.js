const { Application, Component } = Shopware;

import template from './sw-search-bar-item.html.twig';
import './sw-search-bar-item.scss';

Component.override('sw-search-bar-item', {
    template
});

Application.addServiceProviderDecorator('searchTypeService', searchTypeService => {
    searchTypeService.upsertType('ratepay_api_log', {
        entityName: 'ratepay_api_log',
        //entityService: 'ratepayApiLogService',
        placeholderSnippet: 'global.placeholderSearchBar.ratepay_api_log',
        listingRoute: 'ratepay.api.log.list'
    });

    return searchTypeService;
});
