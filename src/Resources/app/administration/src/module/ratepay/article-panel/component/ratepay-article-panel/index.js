/*
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import template from './ratepay-article-panel.html.twig';
import './article-panel.scss';

const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('ratepay-article-panel', {
    template,

    inject: {
        "orderManagementService": "ratepay-order-management"
    },

    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            items: [],
            activeTab: 'shipping',
            processSuccess: false,
            orderId: null,
            loading: {
                list: true,
                deliver: false,
                cancel: false,
                cancelWithStock: false,
                rtn: false, // do not use `return`. it is a js key
                rtnWithStock: false,
                reload: false,
                addCredit: false,
                addDebit: false
            },
            addCredit: {
                showModal: false,
                value: 0.01,
                minValue: 0.01,
                maxValue: 10,
                name: this.$t('ratepay.articlePanel.modal.addCredit.defaultValue.name')
            },
            addDebit: {
                showModal: false,
                value: 0.01,
                minValue: 0.01,
                maxValue: null,
                name: this.$t('ratepay.articlePanel.modal.addDebit.defaultValue.name')
            }
        };
    },

    computed: {
        columns() {
            return [
                {
                    property: 'quantity',
                    label: this.$t('ratepay.articlePanel.table.count'),
                    allowResize: false,
                    align: 'center'
                },
                {
                    property: 'name',
                    label: this.$t('ratepay.articlePanel.table.articleName'),
                    allowResize: false
                },
                {
                    property: 'ordered',
                    label: this.$t('ratepay.articlePanel.table.ordered'),
                    allowResize: false,
                    align: 'center'
                },
                {
                    property: 'delivered',
                    label: this.$t('ratepay.articlePanel.table.delivered'),
                    allowResize: false,
                    align: 'center'
                },
                {
                    property: 'canceled',
                    label: this.$t('ratepay.articlePanel.table.canceled'),
                    allowResize: false,
                    align: 'center'
                },
                {
                    property: 'returned',
                    label: this.$t('ratepay.articlePanel.table.returned'),
                    allowResize: false,
                    align: 'center'
                }
            ];
        }
    },

    created() {
        this.orderId = this.$route.params.id;
        this.loadList();
    },

    methods: {
        loadList() {
            this.loading.list = true;
            return this.orderManagementService.load(this.orderId).then(response => {
                return new Promise((resolve, reject) => {
                    if (response.data) {
                        this.items = Object.values(response.data);
                        this.items.map(item => {
                            item.processDeliveryCancel = "0";
                            item.processReturn = "0";
                        });
                        this.loading.list = false;
                    }
                    resolve()
                });
            });
        },
        onClickButtonDeliver() {
            this.loading.deliver = true;
            this.orderManagementService
                .doAction('deliver', this.orderId, this.getProcessShippingCancelData())
                .then(response => {
                    this.showMessage(response, 'deliver');
                    this.loadList().then(() => {
                        this.loading.deliver = false;
                    });
                });
        },
        onClickButtonCancel(updateStock) {
            if (updateStock === false) {
                this.loading.cancel = true;
            } else {
                this.loading.cancelWithStock = true;
            }
            this.orderManagementService
                .doAction('cancel', this.orderId, this.getProcessShippingCancelData(), updateStock)
                .then(response => {
                    this.showMessage(response, 'cancel');
                    this.loadList().then(() => {
                        this.loading.cancel = false;
                        this.loading.cancelWithStock = false;
                    });
                });
        },
        onClickButtonReturn(updateStock) {
            if (updateStock === false) {
                this.loading.rtn = true;
            } else {
                this.loading.rtnWithStock = true;
            }
            this.orderManagementService
                .doAction('return', this.orderId, this.getProcessReturnData(), updateStock)
                .then(response => {
                    this.showMessage(response, 'return');
                    this.loadList().then(() => {
                        this.loading.rtn = false;
                        this.loading.rtnWithStock = false;
                    });
                });
        },
        onClickResetSelections() {
            this.loading.reload = true;
            this.loadList().then(() => {
                this.loading.reload = false;
            });
        },
        onClickButtonAddDebit() {
            this.orderManagementService
                .addItem('debit', this.orderId, this.addDebit.value, this.addDebit.name)
                .then(response => {
                    this.showMessage(response, 'addDebit');
                    this.loadList().then(() => {
                        this.addDebit.showModal = false;
                    });
                });
        },
        onClickButtonAddCredit() {
            this.orderManagementService
                .addItem('credit', this.orderId, this.addCredit.value, this.addCredit.name)
                .then(response => {
                    this.showMessage(response, 'addCredit');
                    this.loadList().then(() => {
                        this.addDebit.showModal = false;
                    });
                });
        },

        showMessage(response, type) {
            if (response.success) {
                this.createNotificationSuccess({
                    title: this.$tc('ratepay.articlePanel.messages.successTitle'),
                    message: this.$tc('ratepay.articlePanel.messages.' + type + '.success')
                });
            } else {
                this.createNotificationError({
                    title: this.$tc('ratepay.articlePanel.messages.failedTitle'),
                    message: response.message
                });
            }
        },
        getProcessShippingCancelData() {
            let items = [];
            this.items.forEach(function (item, index) {
                if (typeof item.processDeliveryCancel != 'undefined' && item.processDeliveryCancel > 0) {
                    items.push({'id': item.id, 'quantity': item.processDeliveryCancel});
                }
            });
            return items;
        },
        getProcessReturnData() {
            let items = [];
            this.items.forEach(function (item, index) {
                if (typeof item.processReturn != 'undefined' && item.processReturn > 0) {
                    items.push({'id': item.id, 'quantity': item.processReturn});
                }
            });
            return items;
        },
    },
});
