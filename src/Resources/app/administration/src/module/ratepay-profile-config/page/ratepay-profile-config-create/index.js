/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

const {Component} = Shopware;

Component.extend('ratepay-profile-config-create', 'ratepay-profile-config-detail', {
    methods: {
        loadEntity() {
            this.entity = this.repository.create(Shopware.Context.api);

            // shopware issue: fix bug that no false values will be submitted
            this.entity.onlyAdminOrders = false;
            this.entity.sandbox = false;

            return Promise.resolve(this.entity);
        },

        onClickSaveAfter() {
            this.$router.push({
                name: 'ratepay.profile.config.detail',
                params: { id: this.entity.id },
                query: { reloadConfig: '1' }
            });
        }
    }
});
