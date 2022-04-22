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
        'repositoryFactory',
        'filterFactory',
        'RatepayLogDistinctValuesService'
    ],

    mixins: [
        Mixin.getByName('listing'),
    ],

    data() {
        return {
            entityName: 'ratepay_api_log',
            repository: null,
            entities: null,
            modalItem: null,
            searchTerm: null,
            initialLogId: null,
            isLoading: false,
            isLoaded: false,
            searchConfigEntity: 'ratepay_api_log',

            storeKey: 'grid.filter.ratepay_api_log',
            activeFilterNumber: 0,
            filterCriteria: [],
            defaultFilters: [
                'createdAt-filter',
                'subOperation-filter',
                'operation-filter',
                'resultCode-filter',
                'resultText-filter',
                'reasonCode-filter',
                'reasonText-filter',
                'statusCode-filter',
                'statusText-filter'
            ],

            automaticFilter: [
                'operation',
                'subOperation',
                'resultCode',
                'resultText',
                'reasonCode',
                'reasonText',
                'statusCode',
                'statusText'
            ],

            filterOptions: {
                operation: null,
                subOperation: null,
                resultCode: null,
                resultText: null,
                reasonCode: null,
                reasonText: null,
                statusCode: null,
                statusText: null,
            },
        };
    },

    metaInfo() {
        return {
            title: this.$t('ratepay.apiLog.componentTitle')
        };
    },

    computed: {
        columns() {
            return [{
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
                property: 'resultCode',
                dataIndex: 'resultCode',
                label: this.$t('ratepay.apiLog.global.labels.resultCode'),
                allowResize: true,
                visible: false
            }, {
                property: 'resultText',
                dataIndex: 'resultText',
                label: this.$t('ratepay.apiLog.global.labels.resultText'),
                allowResize: true,
                visible: false
            }, {
                property: 'statusCode',
                dataIndex: 'statusCode',
                label: this.$t('ratepay.apiLog.global.labels.statusCode'),
                allowResize: true,
                visible: false
            }, {
                property: 'statusText',
                dataIndex: 'statusText',
                label: this.$t('ratepay.apiLog.global.labels.statusText'),
                allowResize: true,
                visible: false
            }, {
                property: 'reasonCode',
                dataIndex: 'reasonCode',
                label: this.$t('ratepay.apiLog.global.labels.reasonCode'),
                allowResize: true,
                visible: false
            }, {
                property: 'reasonText',
                dataIndex: 'reasonText',
                label: this.$t('ratepay.apiLog.global.labels.reasonText'),
                allowResize: true,
                visible: false
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
            }, {
                property: 'additionalData.descriptor',
                dataIndex: 'descriptor',
                label: this.$t('ratepay.apiLog.global.labels.descriptor'),
                allowResize: true,
                visible: false,
            }];
        },

        defaultCriteria() {
            const defaultCriteria = new Criteria();

            if (this.searchTerm.length > 0) {
                defaultCriteria.setTerm(this.searchTerm)
            }

            defaultCriteria.addSorting(Criteria.sort('createdAt', 'DESC'));

            this.filterCriteria.forEach(filter => {
                defaultCriteria.addFilter(filter);
            });

            return defaultCriteria;
        },

        listFilters() {
            let filter = {
                'createdAt-filter': {
                    property: 'createdAt',
                    type: 'date-filter',
                    label: this.$t('ratepay.apiLog.global.labels.createdAt'),
                    dateType: 'datetime-local',
                    fromFieldLabel: null,
                    toFieldLabel: null,
                    showTimeframe: false,
                }
            };

            this.automaticFilter.forEach((item) => {
                filter[item + '-filter'] = {
                    property: item,
                    type: 'multi-select-filter',
                    label: this.$t('ratepay.apiLog.global.labels.' + item),
                    options: this.filterOptions[item]
                }
            });

            return this.filterFactory.create(this.entityName, filter);
        },
    },

    created() {
        this.searchTerm = this.$route.query.term !== undefined ? this.$route.query.term.trim() : "";
        this.initalLogId = this.$route.query.logId !== undefined ? this.$route.query.logId.trim() : null;
        this.repository = this.repositoryFactory.create(this.entityName);
        hljs.registerLanguage('xml', hljsXml);
        hljs.configure({useBR: false});
        this.loadData();

        this.RatepayLogDistinctValuesService.getDistinctValues(this.automaticFilter.join('|')).then((response) => {
            response.results.forEach((field) => {
                this.filterOptions[field.name] = field.options.map((item) => {
                    return {label: item, value: item}
                });
            })
        });
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
            let criteria = await Shopware.Service('filterService').mergeWithStoredFilters(
                this.storeKey,
                this.defaultCriteria
            );

            criteria = await this.addQueryScores(this.searchTerm, criteria);

            this.repository.search(criteria, Shopware.Context.api)
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

            this.activeFilterNumber = criteria.filters.length;
        },

        onSearch(searchTerm) {
            this.initalLogId = null;
            this.searchTerm = searchTerm.trim();
            this.loadData();
        },

        updateCriteria(criteria) {
            this.page = 1;
            this.filterCriteria = criteria;
        },
    }
});
