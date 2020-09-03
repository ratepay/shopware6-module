/*
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import template from './ratepay-profile-config-list.html.twig';

const {Component} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('ratepay-profile-config-list', {
    template,

    inject: [
        'repositoryFactory'
    ],

    metaInfo() {
        return {
            title: this.$t('ratepay.profileConfig.componentTitle')
        };
    },

    data() {
        return {
            repository: null,
            entities: null
        };
    },

    computed: {
        columns() {
            return [{
                property: 'profileId',
                dataIndex: 'profileId',
                label: this.$t('ratepay.profileConfig.global.labels.profile_id'),
                routerLink: 'ratepay.profile.config.detail',
                allowResize: true
                //primary: true
            }, {
                property: 'salesChannel.name',
                dataIndex: 'salesChannel.name',
                label: this.$t('ratepay.profileConfig.global.labels.sales_channel'),
                allowResize: true
            }, /*{ Not in use, yet.
                property: 'backend',
                dataIndex: 'backend',
                label: this.$t('ratepay.profileConfig.global.labels.backend'),
                allowResize: true
            },*/ {
                property: 'status',
                dataIndex: 'status',
                label: this.$t('ratepay.profileConfig.global.labels.status'),
                allowResize: true
            }
            ];
        }
    },

    created() {
        this.repository = this.repositoryFactory.create('ratepay_profile_config');

        let criteria = new Criteria();
        criteria.addAssociation('salesChannel');
        this.repository
            .search(criteria, Shopware.Context.api)
            .then((result) => {
                this.entities = result;
            });
    }
});
