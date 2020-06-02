/*
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import './page/list';

import deDE from './snippet/de-DE.json';
import enGB from "./snippet/en-GB.json";

const {Module} = Shopware;

Module.register('ratepay-logViewer', {
    type: 'plugin',
    name: 'logViewer',
    title: 'ratepay.log_viewer.general.subMenuItemApiLogViewer',
    description: 'sw-property.general.descriptionTextModule',
    color: '#ff3d58',
    icon: 'default-shopping-paper-bag-product',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    routes: {
        list: {
            component: 'ratepay.logViewer.list',
            path: 'list'
        }
    },

    navigation: [{
        parent: 'sw-ratepay',
        label: 'ratepay.log_viewer.general.subMenuItemApiLogViewer',
        path: 'ratepay.logViewer.list',
        position: 10
    }]

});
