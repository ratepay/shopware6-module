import template from './list.html.twig';

const {Component} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('ratepay.profileConfig.list', {
    template,

    inject: [
        'repositoryFactory'
    ],

    data() {
        return {
            repository: null,
            entities: null
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        columns() {
            return [{
                property: 'profileId',
                dataIndex: 'profileId',
                label: this.$t('ratepay.profile_config.global.labels.profile_id'),
                routerLink: 'ratepay.profileConfig.detail',
                allowResize: true
                //primary: true
            }, {
                property: 'salesChannel.name',
                dataIndex: 'salesChannel.name',
                label: this.$t('ratepay.profile_config.global.labels.sales_channel'),
                allowResize: true
            }, {
                property: 'backend',
                dataIndex: 'backend',
                label: this.$t('ratepay.profile_config.global.labels.backend'),
                allowResize: true
            }, {
                property: 'status',
                dataIndex: 'status',
                label: this.$t('ratepay.profile_config.global.labels.status'),
                allowResize: true
            }
            ];
        }
    },

    created() {
        this.repository = this.repositoryFactory.create('ratepay_profile_config');

        this.repository
            .search(new Criteria(), Shopware.Context.api)
            .then((result) => {
                this.entities = result;
            });
    }
});
