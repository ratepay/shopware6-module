const {Component} = Shopware;

Component.extend('ratepay.profileConfig.create', 'ratepay.profileConfig.detail', {
    methods: {
        getEntity() {
            this.entity = this.repository.create(Shopware.Context.api);
        },

        onClickSave() {
            this.isLoading = true;

            this.repository
                .save(this.entity, Shopware.Context.api)
                .then(() => {
                    this.isLoading = false;
                    this.$router.push({name: 'ratepay.profileConfig.detail', params: {id: this.entity.id}});
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
