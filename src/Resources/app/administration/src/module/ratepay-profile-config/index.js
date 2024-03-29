/*
 * Copyright (c) Ratepay GmbH
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
    title: 'ratepay.profileConfig.componentTitle',
    icon: 'regular-cog',
    color: '#e4233e',
    entity: 'ratepay_profile_config',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    routes: {
        list: {
            component: 'ratepay-profile-config-list',
            path: 'list',
            meta: {
                parentPath: 'sw.settings.index'
            }
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

    settingsItem: [
        {
            to: 'ratepay.profile.config.list',
            group: 'plugins',
            iconComponent: 'ratepay-plugin-icon',
        }
    ]
});
