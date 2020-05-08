const {Component} = Shopware;

Component.extend('ratepay.profileConfig.create', 'ratepay.profileConfig.detail', {
    methods: {
        loadEntity() {
            this.entity = this.repository.create(Shopware.Context.api);
            return new Promise((resolve, reject) => {resolve(this.entity)});
        },

        onClickSave() {
            this.isLoading = true;

            this.repository
                .save(this.entity, Shopware.Context.api)
                .then(() => {
                    this.isLoading = false;
                    this.createNotificationSuccess({
                        title: this.$tc('ratepay.profile_config.messages.save.success'),
                        message: this.$tc('ratepay.profile_config.messages.save.success')
                    });
                    this.$router.push({name: 'ratepay.profileConfig.detail', params: {id: this.entity.id, reloadConfig: true}});
                }).catch((exception) => {
                    this.isLoading = false;

                    this.createNotificationError({
                        title: this.$t('swag-bundle.detail.errorTitle'),
                        message: exception
                    });
                });
        }
    }
});
