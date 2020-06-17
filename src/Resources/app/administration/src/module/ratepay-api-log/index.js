/*
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import './page/ratepay-api-log-list';

import deDE from './snippet/de-DE.json';
import enGB from "./snippet/en-GB.json";

const {Module} = Shopware;

Module.register('ratepay-api-log', {
    type: 'plugin',
    name: 'api-log',
    title: 'ratepay.apiLog.general.subMenuItemApiLogViewer',
    description: 'sw-property.general.descriptionTextModule',
    color: '#ff3d58',
    icon: 'default-shopping-paper-bag-product',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    routes: {
        list: {
            component: 'ratepay-api-log-list',
            path: 'list'
        }
    },

    navigation: [{
        parent: 'ratepay',
        label: 'ratepay.apiLog.general.subMenuItemApiLogViewer',
        path: 'ratepay.api.log.list',
        position: 10
    }]

});
