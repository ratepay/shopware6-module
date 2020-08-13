/*
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import './page/ratepay-profile-config-list';
import './page/ratepay-profile-config-detail';
import './page/ratepay-profile-config-create';

import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

const {Module} = Shopware;

Module.register('ratepay-profile-config', {
    type: 'plugin',
    name: 'profile-config',
    title: 'ratepay.profileConfig.general.mainMenuItemGeneral',
    description: 'sw-property.general.descriptionTextModule',
    color: '#ff3d58',
    icon: 'default-shopping-paper-bag-product',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    routes: {
        list: {
            component: 'ratepay-profile-config-list',
            path: 'list'
        },
        detail: {
            component: 'ratepay-profile-config-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'ratepay.profile.config.list'
            }
        },
        create: {
            component: 'ratepay-profile-config-create',
            path: 'create',
            meta: {
                parentPath: 'ratepay.profile.config.list'
            }
        }
    },

    navigation: [{
        id: 'ratepay',
        label: 'ratepay.profileConfig.general.mainMenuItemGeneral',
        color: '#ff3d58',
        path: 'ratepay.profile.config.list',
        icon: 'default-shopping-paper-bag-product',
        position: 100
    }]
});
