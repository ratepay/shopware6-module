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
                label: this.$t('ratepay.log_viewer.global.labels.id'),
                allowResize: true
                //primary: true
            }, {
                property: 'operation',
                dataIndex: 'operation',
                label: this.$t('ratepay.log_viewer.global.labels.operation'),
                allowResize: true
            }, {
                property: 'suboperation',
                dataIndex: 'suboperation',
                label: this.$t('ratepay.log_viewer.global.labels.suboperation'),
                allowResize: true
            }, {
                property: 'status',
                dataIndex: 'status',
                label: this.$t('ratepay.log_viewer.global.labels.status'),
                allowResize: true
            }, {
                property: 'request',
                dataIndex: 'request',
                label: this.$t('ratepay.log_viewer.global.labels.request'),
                allowResize: true
            }, {
                property: 'response',
                dataIndex: 'response',
                label: this.$t('ratepay.log_viewer.global.labels.response'),
                allowResize: true
            }];
        }
    },

    created() {
        this.repository = this.repositoryFactory.create('ratepay_log_viewer');

        this.repository
            .search(new Criteria(), Shopware.Context.api)
            .then((result) => {
                this.entities = result;
            });
    }
});
