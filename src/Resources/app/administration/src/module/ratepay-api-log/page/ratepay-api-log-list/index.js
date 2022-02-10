/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


import template from './ratepay-api-log-list.html.twig';
import './ratepay-api-log-list.scss';

// import xmlFormatter to format the XML with whitespaces
import xmlFormatter from 'xml-formatter';

// import highlight.js for xml highlighting
import hljs from 'highlight.js/lib/core';
import hljsXml from 'highlight.js/lib/languages/xml';
import 'highlight.js/styles/github.css';

const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('ratepay-api-log-list', {
    template,

    inject: [
        'repositoryFactory'
    ],

    mixins: [
        Mixin.getByName('listing'),
    ],

    data() {
        return {
            repository: null,
            entities: null,
            modalItem: null,
            searchTerm: null,
            initialLogId: null,
            isLoading: false,
            isLoaded: false,
            searchConfigEntity: 'ratepay_api_log',
        };
    },

    metaInfo() {
        return {
            title: this.$t('ratepay.apiLog.componentTitle')
        };
    },

    computed: {
        columns() {
            return [
                {
                    property: 'createdAt',
                    dataIndex: 'createdAt',
                    label: this.$t('ratepay.apiLog.global.labels.createdAt'),
                    allowResize: true
                }, {
                    property: 'operation',
                    dataIndex: 'operation',
                    label: this.$t('ratepay.apiLog.global.labels.operation'),
                    allowResize: true
                }, {
                    property: 'subOperation',
                    dataIndex: 'subOperation',
                    label: this.$t('ratepay.apiLog.global.labels.subOperation'),
                    allowResize: true
                }, {
                    property: 'result',
                    dataIndex: 'result',
                    label: this.$t('ratepay.apiLog.global.labels.result'),
                    allowResize: true
                }, {
                    property: 'additionalData.transactionId',
                    dataIndex: 'transactionId',
                    label: this.$t('ratepay.apiLog.global.labels.transactionId'),
                    allowResize: true
                }, {
                    property: 'additionalData.orderNumber',
                    dataIndex: 'orderNumber',
                    label: this.$t('ratepay.apiLog.global.labels.orderNumber'),
                    allowResize: true,
                    visible: false
                }, {
                    property: 'additionalData.firstName',
                    dataIndex: 'firstName',
                    label: this.$t('ratepay.apiLog.global.labels.firstName'),
                    allowResize: true,
                    visible: false,
                }, {
                    property: 'additionalData.lastName',
                    dataIndex: 'lastName',
                    label: this.$t('ratepay.apiLog.global.labels.lastName'),
                    allowResize: true,
                    visible: false,
                }];
        }
    },

    created() {
        this.searchTerm = this.$route.query.term !== undefined ? this.$route.query.term.trim() : "";
        this.initalLogId = this.$route.query.logId !== undefined ? this.$route.query.logId.trim() : null;
        this.repository = this.repositoryFactory.create('ratepay_api_log');
        hljs.registerLanguage('xml', hljsXml);
        hljs.configure({useBR: false});
        this.loadData();
    },

    watch: {
        $route() {
            this.searchTerm = this.$route.query.term !== undefined ? this.$route.query.term.trim() : "";
            this.initalLogId = this.$route.query.logId !== undefined ? this.$route.query.logId.trim() : null;
            this.loadData();
        }
    },

    methods: {
        formatXml(str) {
            return hljs.highlight('xml', xmlFormatter(str, {"collapseContent": true})).value
                // remove CDATA line break
                .replaceAll(/\r\n\s*&lt;!\[CDATA\[/gi, '&lt;![CDATA[')
                .replaceAll(/]]&gt;\r\n\s*/gi, ']]&gt;');
        },

        async loadData() {
            this.isLoading = true;
            let criteria = new Criteria();
            criteria.addSorting(Criteria.sort('createdAt', 'DESC'));

            if (this.searchTerm.length > 0) {
                criteria.setTerm(this.searchTerm)
            }

            const newCriteria = await this.addQueryScores(this.searchTerm, criteria);

            this.repository.search(newCriteria, Shopware.Context.api)
                .then((result) => {
                    this.entities = result;
                    if (this.initalLogId && this.entities.has(this.initalLogId)) {
                        this.modalItem = result.get(this.initalLogId);
                    }
                })
                .finally(() => {
                    this.isLoading = false;
                    this.isLoaded = false;
                });
        },
        onSearch(searchTerm) {
            this.initalLogId = null;
            this.searchTerm = searchTerm.trim();
            this.loadData();
        }
    }
});
