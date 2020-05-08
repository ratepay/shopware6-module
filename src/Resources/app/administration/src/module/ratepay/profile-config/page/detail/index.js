import template from './detail.html.twig';

const {Component, Mixin} = Shopware;

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
            isLoading: false,
            processSuccess: false,
            repository: null,
            disabledReloadButton: this.entity === null
        };
    },

    created() {
        this.repository = this.repositoryFactory.create('ratepay_profile_config');
        let prom = this.loadEntity();
        if(this.$route.params.reloadConfig) {
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
            return this.repository
                .get(this.$route.params.id, Shopware.Context.api)
                .then((entity) => {
                    this.entity = entity;
                    return new Promise((resolve, reject) => {resolve(this.entity)});
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
                for(let [profileId, message] of Object.entries(response.success)) {
                    this.createNotificationSuccess({
                        title: profileId,
                        message: this.$tc('ratepay.profile_config.messages.reload.success')
                    });
                }
                for(let [profileId, message] of Object.entries(response.error)) {
                    this.createNotificationError({
                        title: profileId,
                        message: message
                    });
                }
                this.$forceUpdate();
                return new Promise((resolve, reject) => {resolve()});
            });
        }

    }
});
