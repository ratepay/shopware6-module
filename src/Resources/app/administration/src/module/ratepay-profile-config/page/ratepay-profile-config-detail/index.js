/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import template from './ratepay-profile-config-detail.html.twig';

const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('ratepay-profile-config-detail', {
    template,

    inject: {
        'repositoryFactory': 'repositoryFactory',
        'profileConfigApiService': 'ratepay-profile-config'
    },

    mixins: [
        Mixin.getByName('notification')
    ],

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    data() {
        return {
            entity: null,
            isLoading: false,
            processSuccess: false,
            repository: null,
            disabledReloadButton: this.entity === null,
            currentTab: 'general'
        };
    },

    created() {
        this.repository = this.repositoryFactory.create('ratepay_profile_config');
        let prom = this.loadEntity();
        if (this.$route.params.reloadConfig) {
            prom.then(() => {
                this.onClickReloadConfig()
            });
        }
    },

    methods: {
        lockReloadButton() {
            this.disabledReloadButton = true;
        },
        loadEntity() {
            let entityCriteria = new Criteria();
            entityCriteria.addAssociation('paymentMethodConfigs.paymentMethod');
            entityCriteria.addAssociation('paymentMethodConfigs.installmentConfig');
            entityCriteria.setIds([this.$route.params.id]);
            return this.repository.search(entityCriteria, Shopware.Context.api)
                .then((entity) => {
                    return new Promise((resolve, reject) => {
                        // when this field get updated, the component will be reload
                        this.entity = entity.first();
                        resolve(this.entity)
                    });
                });
        },

        onClickSave() {
            this.isLoading = true;

            this.repository
                .save(this.entity, Shopware.Context.api)
                .then(() => {
                    this.loadEntity();

                    this.onClickReloadConfig();
                    this.createNotificationSuccess({
                        title: this.$tc('ratepay.profileConfig.messages.save.success'),
                        message: this.$tc('ratepay.profileConfig.messages.save.success')
                    });

                    this.isLoading = false;
                    this.processSuccess = true;
                }).catch((exception) => {
                    this.isLoading = false;
                    this.createNotificationError({
                        title: this.$t('ratepay.profileConfig.messages.save.error.title'),
                        message: exception
                    });
            });
        },

        saveFinish() {
            this.disabledReloadButton = false;
            this.processSuccess = false;
        },

        onClickReloadConfig() {
            return this.profileConfigApiService.reloadConfig(this.entity.id).then((response) => {
                this.loadEntity();
                for (let [profileId, message] of Object.entries(response.success)) {
                    this.createNotificationSuccess({
                        title: profileId,
                        message: this.$tc('ratepay.profileConfig.messages.reload.success')
                    });
                }
                for (let [profileId, message] of Object.entries(response.error)) {
                    this.createNotificationError({
                        title: profileId,
                        message: message
                    });
                }
                this.$forceUpdate();
                return new Promise((resolve, reject) => {
                    resolve()
                });
            });
        },

        switchTab(tabId) {
            this.currentTab = tabId;
        }

    }
});
