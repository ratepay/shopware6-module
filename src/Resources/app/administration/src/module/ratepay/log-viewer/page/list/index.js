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
                allowResize: true
                //primary: true
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
                property: 'request',
                dataIndex: 'request',
                label: this.$t('ratepay.api_log.global.labels.request'),
                allowResize: false,
            }, {
                property: 'response',
                dataIndex: 'response',
                label: this.$t('ratepay.api_log.global.labels.response'),
                allowResize: false,
                width: 100
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
