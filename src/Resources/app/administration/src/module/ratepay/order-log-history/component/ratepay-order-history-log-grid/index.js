import template from './ratepay-order-history-log-grid.html.twig';

const { Component } = Shopware;

Component.register('ratepay-order-history-log-grid', {
    template,

    data() {
        return {
            data: []
        };
    },

    computed: {
        columns() {
            return [
                {
                    property: 'date',
                    label: this.$tc('ratepay.order-log-history.detailBase.column.date'),
                    allowResize: false
                },
                {
                    property: 'user',
                    label: this.$tc('ratepay.order-log-history.detailBase.column.user'),
                    allowResize: true
                },
                {
                    property: 'event',
                    label: this.$tc('ratepay.order-log-history.detailBase.column.event'),
                    allowResize: false
                },
                {
                    property: 'name',
                    label: this.$tc('ratepay.order-log-history.detailBase.column.name'),
                    allowResize: true
                },
                {
                    property: 'number',
                    label: this.$tc('ratepay.order-log-history.detailBase.column.number'),
                    allowResize: true
                },
                {
                    property: 'count',
                    label: this.$tc('ratepay.order-log-history.detailBase.column.count'),
                    allowResize: true
                },
            ];
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.data = [];
            this.data.push({
                date: 'bind data',
                user: 'bind data',
                event: 'bind data',
                name: 'bind data',
                number: 'bind data',
                count: 'bind data'
            });
        },

        isLoading() {
            return this.$parent.isLoading;
        }
    },
});
