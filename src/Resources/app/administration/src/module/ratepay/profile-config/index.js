/*
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import './page/list';
import './page/detail';
import './page/create';

import './init/profile-config-api-service.init';

import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

const {Module} = Shopware;

Module.register('ratepay-profileConfig', {
    type: 'plugin',
    name: 'ProfileConfig',
    title: 'ratepay.profile_config.general.mainMenuItemGeneral',
    description: 'sw-property.general.descriptionTextModule',
    color: '#ff3d58',
    icon: 'default-shopping-paper-bag-product',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    routes: {
        list: {
            component: 'ratepay.profileConfig.list',
            path: 'list'
        },
        detail: {
            component: 'ratepay.profileConfig.detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'ratepay.profileConfig.list'
            }
        },
        create: {
            component: 'ratepay.profileConfig.create',
            path: 'create',
            meta: {
                parentPath: 'ratepay.profileConfig.list'
            }
        }
    },

    navigation: [{
        id: 'sw-ratepay',
        label: 'ratepay.profile_config.general.mainMenuItemGeneral',
        color: '#ff3d58',
        path: 'ratepay.profileConfig.list',
        icon: 'default-shopping-paper-bag-product',
        position: 100
    }]
});
