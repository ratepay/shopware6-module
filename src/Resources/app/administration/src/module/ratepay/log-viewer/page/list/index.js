import template from './list.html.twig';
import './log-list.scss';

import formatter from '../../../../../lib/xml-formatter';

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
            entities: null,
            modalItem: null
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        columns() {
            return [
                {
                    property: 'createdAt',
                    dataIndex: 'createdAt',
                    label: this.$t('ratepay.api_log.global.labels.createdAt'),
                    allowResize: true
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
                    property: 'firstname',
                    dataIndex: 'firstname',
                    label: this.$t('ratepay.api_log.global.labels.firstname'),
                    allowResize: true
                }, {
                    property: 'lastname',
                    dataIndex: 'lastname',
                    label: this.$t('ratepay.api_log.global.labels.lastname'),
                    allowResize: true
                }, {
                    property: 'response',
                    dataIndex: 'response',
                    label: this.$t('ratepay.api_log.global.labels.response'),
                    allowResize: true
                }];
        }
    },

    created() {
        this.repository = this.repositoryFactory.create('ratepay_api_log');
        let criteria = new Criteria();
        criteria.addSorting(Criteria.sort('createdAt', 'DESC'));
        this.repository
            .search(criteria, Shopware.Context.api)
            .then((result) => {
                this.entities = result;
            });
    },
    methods: {
        formatXml(str) {
            return formatter.formatXml(str);
        }
    }
});
