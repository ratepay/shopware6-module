/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import './page/ratepay-api-log-list';
import './component/shopware/sw-search-bar-item';

import deDE from './snippet/de-DE.json';
import enGB from "./snippet/en-GB.json";

const {Module} = Shopware;

Module.register('ratepay-api-log', {
    type: 'plugin',
    name: 'api-log',
    title: 'ratepay.apiLog.componentTitle',
    icon: 'default-badge-warning',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    routes: {
        list: {
            component: 'ratepay-api-log-list',
            path: 'list',
            meta: {
                parentPath: 'sw.settings.index'
            }
        }
    },

    settingsItem: [
        {
            to: 'ratepay.api.log.list',
            group: 'plugins',
            icon: 'default-badge-warning',
        }
    ]

});
