/*
 * Copyright (c) Ratepay GmbH
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
            isLoading: {
                page: true,
                save: false,
                reloadConfig: false,
            },
            processSuccessState: {
                save: false,
                reloadConfig: false,
            },
            repository: null,
            disabledReloadButton: this.entity === null,
            currentTab: 'general'
        };
    },

    computed: {
        dateFilter() {
            return Shopware.Filter.getByName('date');
        },
    },

    created() {
        this.repository = this.repositoryFactory.create('ratepay_profile_config');
        this.loadEntity().then(() => (this.$route.query.reloadConfig && this.onClickReloadConfig()));
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
                    this.entity = entity.first();
                    this.isLoading.page = false;
                });
        },

        onClickSave() {
            this._lockButtons(true);

            this.repository
                .save(this.entity, Shopware.Context.api)
                .then(async () => {
                    await this.onClickSaveAfter();
                    this.createNotificationSuccess({
                        title: this.$tc('ratepay.profileConfig.messages.save.success'),
                        message: this.$tc('ratepay.profileConfig.messages.save.success')
                    });

                    this._lockButtons(false, true);
                    this.processSuccess = true;
                })
                .catch((exception) => {
                    this._lockButtons(false);
                    this.createNotificationError({
                        title: this.$t('ratepay.profileConfig.messages.save.error.title'),
                        message: exception
                    });
                });
        },

        _lockButtons(state, processState = undefined) {
            this.isLoading.save = state;
            this.isLoading.reloadConfig = state;
            if (processState !== undefined) {
                this.processSuccessState.save = processState;
                this.processSuccessState.reloadConfig = processState;
            }
        },

        async onClickSaveAfter() {
            await this.loadEntity();
            await this.onClickReloadConfig();
        },

        saveFinish() {
            this.processSuccessState.save = processState;
            this.processSuccessState.reloadConfig = processState;
        },

        onClickReloadConfig() {
            this.isLoading.reloadConfig = true;
            return this.profileConfigApiService.reloadConfig(this.entity.id).then(async (response) => {
                await this.loadEntity();
                if (response?.error) {
                    for (let [profileId, message] of Object.entries(response.error)) {
                        this.createNotificationError({
                            title: profileId,
                            message: message
                        });
                    }
                } else {
                    this.createNotificationSuccess({
                        title: this.entity.profileId,
                        message: this.$tc('ratepay.profileConfig.messages.reload.success')
                    });
                }
                this.$forceUpdate();
                this.isLoading.reloadConfig = false;
                this.processSuccessState.reloadConfig = true;
            }).catch((error) => {
                console.error(error);
                if (error.response?.data?.success === false) {
                    this.createNotificationError({
                        title: this.entity.profileId,
                        message: error.response?.data?.message
                    });
                } else {

                }
            });
        },

        switchTab(tabId) {
            this.currentTab = tabId;
        }

    }
});
