/*
 * Copyright (c) 2020 RatePAY GmbH
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
            this.addCredit.data.amount = [
                {
                    net: null,
                    gross: null,
                    currencyId: this.order.currencyId,
                    linked: true
                }
            ];
            this.addCredit.data.name = this.$t('ratepay.orderManagement.modal.addCredit.defaultValue.name');
            this.addCredit.data.tax = this.defaultTax;
            this.addCredit.data.taxId = this.defaultTax.id;
        },
        initDebit() {
            this.addDebit.data.amount = [
                {
                    net: null,
                    gross: null,
                    currencyId: this.order.currencyId,
                    linked: true
                }
            ];
            this.addDebit.data.name = this.$t('ratepay.orderManagement.modal.addDebit.defaultValue.name');
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
                .then(response => {
                    this.showMessage(response, 'deliver');
                    this.loadList().then(() => {
                        this.loading.deliver = false;
                        this.$emit('ratepayActionTriggered');
                    });
                })
                .catch((response) => {
                    this.loading.deliver = false;
                    this.showMessage(response, 'deliver');
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
                .then(response => {
                    this.showMessage(response, 'cancel');
                    this.loadList().then(() => {
                        this.loading.cancel = false;
                        this.loading.cancelWithStock = false;
                        this.$emit('ratepayActionTriggered');
                    });
                })
                .catch((response) => {
                    this.loading.cancel = false;
                    this.loading.cancelWithStock = false;
                    this.showMessage(response, 'cancel');
                });
        },
        onClickButtonReturn(updateStock) {
            if (updateStock === false) {
                this.loading.rtn = true;
            } else {
                this.loading.rtnWithStock = true;
            }
            this.orderManagementService
                .doAction('return', this.order.id, this.getProcessReturnData(), updateStock)
                .then(response => {
                    this.showMessage(response, 'return');
                    this.loadList().then(() => {
                        this.loading.rtn = false;
                        this.loading.rtnWithStock = false;
                        this.$emit('ratepayActionTriggered');
                    });
                })
                .catch((response) => {
                    this.loading.rtn = false;
                    this.loading.rtnWithStock = false;
                    this.showMessage(response, 'return');
                });
        },
        onClickResetSelections() {
            this.loading.reload = true;
            this.loadList().then(() => {
                this.loading.reload = false;
                this.items.forEach(function (item, index) {
                    item.processDeliveryCancel = 0;
                    item.processReturn = 0;
                });
                this.$emit('ratepayActionTriggered');
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
                    this.addDebit.data.amount[0].gross,
                    this.addDebit.data.tax.taxRate
                ).then(response => {
                    this.showMessage(response, 'addDebit');
                    this.loadList().then(() => {
                        this.onCloseDebitModal();
                        this.loading.addDebit = false;
                        this.$emit('ratepayActionTriggered');
                        this.initDebit();
                    });
                })
                .catch((response) => {
                    this.loading.addDebit = false;
                    this.showMessage(response, 'addDebit');
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
                    (this.addCredit.data.amount[0].gross * -1),
                    this.addCredit.data.tax.taxRate
                ).then(response => {
                    this.showMessage(response, 'addCredit');
                    this.loadList().then(() => {
                        this.onCloseCreditModal();
                        this.loading.addCredit = false;
                        this.$emit('ratepayActionTriggered');
                        this.initCredit();
                    });
                })
                .catch((response) => {
                    this.loading.addCredit = false;
                    this.showMessage(response, 'addCredit');
                });
        },
        validateCreditDebit(creditDebit) {
            if (!creditDebit.taxId) {
                this.showMessage({
                    success: false,
                    message: this.$tc('ratepay.orderManagement.messages.creditDebitValidation.missingTax')
                });
                return false;
            }

            if (!creditDebit.name) {
                this.showMessage({
                    success: false,
                    message: this.$tc('ratepay.orderManagement.messages.creditDebitValidation.missingName')
                });
                return false;
            }

            if (!creditDebit.amount[0]) {
                this.showMessage({
                    success: false,
                    message: this.$tc('ratepay.orderManagement.messages.creditDebitValidation.missingAmount')
                });
                return false;
            }

            if (creditDebit.amount[0].gross <= 0) {
                this.showMessage({
                    success: false,
                    message: this.$tc('ratepay.orderManagement.messages.creditDebitValidation.amountTooLow')
                });
                return false;
            }

            return true;
        },

        showMessage(response, type) {
            if (response.success) {
                this.createNotificationSuccess({
                    title: this.$tc('ratepay.orderManagement.messages.successTitle'),
                    message: this.$tc('ratepay.orderManagement.messages.' + type + '.success')
                });
            } else {
                this.createNotificationError({
                    title: this.$tc('ratepay.orderManagement.messages.failedTitle'),
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
        }
    }
});
