import template from './detail.html.twig';

const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('ratepay.profileConfig.detail', {
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
            paymentConfigs: {},
            installmentConfigs: {},
            isLoading: false,
            processSuccess: false,
            repository: null,
            paymentConfigRepository: null,
            paymentConfigInstallmentRepository: null,
            disabledReloadButton: this.entity === null,
            currentTab: 'general'
        };
    },

    created() {
        this.repository = this.repositoryFactory.create('ratepay_profile_config');
        this.paymentConfigRepository = this.repositoryFactory.create('ratepay_profile_config_method');
        this.paymentConfigInstallmentRepository = this.repositoryFactory.create('ratepay_profile_config_method_installment');
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
            var me = this;
            me.paymentConfigs = {};
            me.installmentConfigs = {};
            return this.repository
                .get(this.$route.params.id, Shopware.Context.api)
                .then((entity) => {
                    return new Promise((resolve, reject) => {

                        // load the payment configs
                        let criteria = new Criteria();
                        criteria.addFilter(Criteria.equals('profileId', entity.id));
                        this.paymentConfigRepository.search(criteria, Shopware.Context.api).then((response) => {
                            let installmentMapping = {};
                            response.forEach((config) => {
                                me.paymentConfigs[config.paymentMethod] = config;
                                if (config.paymentMethod.indexOf('installment') >= 0) {
                                    installmentMapping[config.id] = config.paymentMethod;
                                }
                            });

                            return new Promise(() => {

                                // load the installment configs
                                let criteriaInstallment = new Criteria();
                                criteriaInstallment.setIds(Object.keys(installmentMapping));
                                this.paymentConfigInstallmentRepository.search(criteriaInstallment, Shopware.Context.api).then((response) => {
                                    response.forEach(installmentConfig => me.installmentConfigs[installmentMapping[installmentConfig.id]] = installmentConfig);
                                    return new Promise(() => {

                                        // when this field get updated, the component will be reload
                                        this.entity = entity;
                                        resolve(me.entity)
                                    });
                                });

                            });
                        });

                    });
                });
        },

        onClickSave() {
            let me = this;
            this.isLoading = true;

            this.repository
                .save(this.entity, Shopware.Context.api)
                .then(() => {
                    me.loadEntity();

                    this.onClickReloadConfig();
                    this.createNotificationSuccess({
                        title: this.$tc('ratepay.profile_config.messages.save.success'),
                        message: this.$tc('ratepay.profile_config.messages.save.success')
                    });

                    me.isLoading = false;
                    me.processSuccess = true;
                }).catch((exception) => {
                    this.isLoading = false;
                    this.createNotificationError({
                        title: this.$t('ratepay.profile_config.messages.save.error'),
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
                        message: this.$tc('ratepay.profile_config.messages.reload.success')
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
