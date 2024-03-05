/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import template from './ratepay-order-management.html.twig';
import './ratepay-order-management.scss';

const {Component, Mixin} = Shopware;
const { Criteria } = Shopware.Data;

Component.register('ratepay-order-management', {
    template,

    inject: {
        orderManagementService: "ratepay-order-management-service",
        repositoryFactory: "repositoryFactory"
    },

    props: ['order'],

    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            items: [],
            taxes: [],
            defaultTax: null,
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
            showCreditModal: false,
            showDebitModal: false,
            addCredit: {
                showModal: false,
                data: {
                    amount: null,
                    name: null,
                    tax: null,
                    taxId: null
                }
            },
            addDebit: {
                showModal: false,
                data: {
                    amount: null,
                    name: null,
                    tax: null,
                    taxId: null
                }
            }
        };
    },

    computed: {
        currencyFilter() {
            return Shopware.Filter.getByName('currency');
        },

        columns() {
            return [
                {
                    property: 'quantity',
                    label: this.$t('ratepay.orderManagement.table.count'),
                    allowResize: false,
                    align: 'center'
                },
                {
                    property: 'name',
                    label: this.$t('ratepay.orderManagement.table.articleName'),
                    allowResize: false
                },
                {
                    property: 'ordered',
                    label: this.$t('ratepay.orderManagement.table.ordered'),
                    allowResize: false,
                    align: 'center'
                },
                {
                    property: 'position.delivered',
                    label: this.$t('ratepay.orderManagement.table.delivered'),
                    allowResize: false,
                    align: 'center'
                },
                {
                    property: 'position.canceled',
                    label: this.$t('ratepay.orderManagement.table.canceled'),
                    allowResize: false,
                    align: 'center'
                },
                {
                    property: 'position.returned',
                    label: this.$t('ratepay.orderManagement.table.returned'),
                    allowResize: false,
                    align: 'center'
                },
                {
                    property: 'unitPrice',
                    label: this.$t('ratepay.orderManagement.table.unitPrice'),
                    allowResize: false,
                    align: 'right'
                },
                {
                    property: 'totalPrice',
                    label: this.$t('ratepay.orderManagement.table.totalPrice'),
                    allowResize: false,
                    align: 'right'
                }
            ];
        },
        taxRepository() {
            return this.repositoryFactory.create('tax');
        },
    },

    created() {
        this.loadList();
        this.loadTaxes();
    },

    methods: {
        loadList() {
            this.loading.list = true;
            return this.orderManagementService.load(this.order.id).then(response => {
                return new Promise((resolve, reject) => {
                    if (response.data) {
                        this.items = Object.values(response.data);
                        this.items.map(item => {
                            item.processDeliveryCancel = item.position.maxDelivery.toString();
                            item.processReturn = item.position.maxReturn.toString();
                        });
                        this.loading.list = false;
                    }
                    resolve()
                });
            });
        },
        loadTaxes() {
            return this.taxRepository.search(new Criteria(1, 500), Shopware.Context.api).then((res) => {
                this.taxes = res;
                this.defaultTax = res[0];
                this.initCredit();
                this.initDebit();
            });
        },
        initCredit() {
            this.addCredit.data.tax = this.defaultTax;
            this.addCredit.data.taxId = this.defaultTax.id;
        },
        initDebit() {
            this.addDebit.data.tax = this.defaultTax;
            this.addDebit.data.taxId = this.defaultTax.id;
        },
        updateCreditTax() {
            if (this.addCredit.data.taxId) {
                this.addCredit.data.tax = this.taxes.get(this.addCredit.data.taxId);
            }
        },
        updateDebitTax() {
            if (this.addDebit.data.taxId) {
                this.addDebit.data.tax = this.taxes.get(this.addDebit.data.taxId);
            }
        },
        onClickButtonDeliver() {
            this.loading.deliver = true;
            this.orderManagementService
                .doAction('deliver', this.order.id, this.getProcessShippingCancelData())
                .then(() => {
                    this.showMessage(null, 'deliver');
                    this.loadList().then(() => {
                        this.loading.deliver = false;
                        this.$emit('reload-entity-data');
                    });
                })
                .catch((error) => {
                    this.loading.deliver = false;
                    this.showMessage(error, 'deliver');
                });
        },
        onClickButtonCancel(updateStock) {
            if (updateStock === false) {
                this.loading.cancel = true;
            } else {
                this.loading.cancelWithStock = true;
            }
            this.orderManagementService
                .doAction('cancel', this.order.id, this.getProcessShippingCancelData(), updateStock)
                .then(() => {
                    this.showMessage(null, 'cancel');
                    this.loadList().then(() => {
                        this.loading.cancel = false;
                        this.loading.cancelWithStock = false;
                        this.$emit('reload-entity-data');
                    });
                })
                .catch((error) => {
                    this.loading.cancel = false;
                    this.loading.cancelWithStock = false;
                    this.showMessage(error, 'cancel');
                });
        },
        onClickButtonReturn(updateStock) {
            if (updateStock === false) {
                this.loading.rtn = true;
            } else {
                this.loading.rtnWithStock = true;
            }
            this.orderManagementService
                .doAction('refund', this.order.id, this.getProcessReturnData(), updateStock)
                .then(() => {
                    this.showMessage(null, 'return');
                    this.loadList().then(() => {
                        this.loading.rtn = false;
                        this.loading.rtnWithStock = false;
                        this.$emit('reload-entity-data');
                    });
                })
                .catch((error) => {
                    this.loading.rtn = false;
                    this.loading.rtnWithStock = false;
                    this.showMessage(error, 'return');
                });
        },
        onClickResetSelections() {
            this.loading.reload = true;
            this.loadList().then(() => {
                this.loading.reload = false;
                this.items.forEach(function (item, index) {
                    item.processDeliveryCancel = '0';
                    item.processReturn = '0';
                });
            });
        },
        onClickButtonAddDebit() {
            this.loading.addDebit = true;
            if (!this.validateCreditDebit(this.addDebit.data)) {
                this.loading.addDebit = false;
                return;
            }
            this.orderManagementService
                .addItem(
                    this.order.id,
                    this.addDebit.data.name,
                    this.addDebit.data.amount,
                    this.addDebit.data.taxId
                ).then(() => {
                    this.showMessage(null, 'addDebit');
                    this.loadList().then(() => {
                        this.onCloseDebitModal();
                        this.loading.addDebit = false;
                        this.$emit('reload-entity-data');
                        this.initDebit();
                    });
                    this.addDebit.data.name = null;
                    this.addDebit.data.amount = null;
                    this.addDebit.data.taxId = null;
                })
                .catch((error) => {
                    this.loading.addDebit = false;
                    this.showMessage(error, 'addDebit');
                });
        },
        onClickButtonAddCredit() {
            this.loading.addCredit = true;
            if (!this.validateCreditDebit(this.addCredit.data)) {
                this.loading.addCredit = false;
                return;
            }
            this.orderManagementService
                .addItem(
                    this.order.id,
                    this.addCredit.data.name,
                    this.addCredit.data.amount * -1,
                    this.addCredit.data.taxId
                ).then(() => {
                    this.showMessage(null, 'addCredit');
                    this.loadList().then(() => {
                        this.onCloseCreditModal();
                        this.loading.addCredit = false;
                        this.$emit('reload-entity-data');
                        this.initCredit();
                    });
                    this.addCredit.data.name = null;
                    this.addCredit.data.amount = null;
                    this.addCredit.data.taxId = null;
                })
                .catch((error) => {
                    this.loading.addCredit = false;
                    this.showMessage(error, 'addCredit');
                });
        },
        validateCreditDebit(creditDebit) {
            if (!creditDebit.taxId) {
                this.showMessage(this.$tc('ratepay.orderManagement.messages.creditDebitValidation.missingTax'));
                return false;
            }

            if (!creditDebit.name) {
                this.showMessage(this.$tc('ratepay.orderManagement.messages.creditDebitValidation.missingName'));
                return false;
            }

            if (creditDebit.amount <= 0) {
                this.showMessage(this.$tc('ratepay.orderManagement.messages.creditDebitValidation.amountTooLow'));
                return false;
            }

            return true;
        },

        showMessage(error, type) {
            if(error === null && typeof type === 'string') {
                this.createNotificationSuccess({
                    title: this.$tc('ratepay.orderManagement.messages.successTitle'),
                    message: this.$tc('ratepay.orderManagement.messages.' + type + '.success')
                });
                return;
            }

            if (typeof error === 'string') {
                this.createNotificationError({
                    title: this.$tc('ratepay.orderManagement.messages.failedTitle'),
                    message: error
                });
                return;
            }

            const response = error.response;
            if (response?.data?.errors) {
                response.data.errors.forEach((error, i) => {
                    let message = error.detail;
                    if (this.$te('ratepay.errors.' + error.code)) {
                        message = this.$tc('ratepay.errors.' + error.code)
                    }
                    this.createNotificationError({
                        title: error.title ?? this.$tc('ratepay.orderManagement.messages.failedTitle'),
                        message: message
                    });
                });
            } else {
                this.showMessage(response?.data?.message ?? error.message ?? this.$tc('ratepay.orderManagement.messages.failedTitle'))
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

        onShowDebitModal() {
            this.addDebit.showModal = true;
        },

        onCloseDebitModal() {
            this.addDebit.showModal = false;
        },

        onShowCreditModal() {
            this.addCredit.showModal = true;
        },

        onCloseCreditModal() {
            this.addCredit.showModal = false;
        },

        getTaxLabel(tax) {
            if (!tax) {
                return '';
            }

            if (this.$te(`global.tax-rates.${tax.name}`)) {
                return this.$tc(`global.tax-rates.${tax.name}`);
            }

            return tax.name;
        },
    }
});
