import template from './list.html.twig';

const {Component} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('ratepay.logViewer.list', {
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
                property: 'id',
                dataIndex: 'id',
                label: this.$t('ratepay.api_log.global.labels.id'),
                allowResize: true,
                routerLink: 'ratepay.logViewer.detail',
            }, {
                property: 'operation',
                dataIndex: 'operation',
                label: this.$t('ratepay.api_log.global.labels.operation'),
                allowResize: true
            }, {
                property: 'suboperation',
                dataIndex: 'suboperation',
                label: this.$t('ratepay.api_log.global.labels.suboperation'),
                allowResize: true
            }, {
                property: 'status',
                dataIndex: 'status',
                label: this.$t('ratepay.api_log.global.labels.status'),
                allowResize: true
            }, {
                property: 'created_at',
                dataIndex: 'created_at',
                label: this.$t('ratepay.api_log.global.labels.created_at'),
                allowResize: true
            }, {
                property: 'firstname',
                dataIndex: 'firstname',
                label: this.$t('ratepay.api_log.global.labels.firstname'),
                allowResize: true
            }, {
                property: 'lastname',
                dataIndex: 'lastname',
                label: this.$t('ratepay.api_log.global.labels.lastname'),
                allowResize: true
            }];
        }
    },

    created() {
        this.repository = this.repositoryFactory.create('ratepay_api_log');

        this.repository
            .search(new Criteria(), Shopware.Context.api)
            .then((result) => {
                this.entities = result;
            });
    }
});
