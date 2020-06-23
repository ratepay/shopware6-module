/*
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

const {Component} = Shopware;

Component.extend('ratepay-profile-config-create', 'ratepay-profile-config-detail', {
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
                        title: this.$tc('ratepay.profileConfig.messages.save.success'),
                        message: this.$tc('ratepay.profileConfig.messages.save.success')
                    });
                    this.$router.push({name: 'ratepay.profile.config.detail', params: {id: this.entity.id, reloadConfig: true}});
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
