import template from './detail.html.twig';

require

const {Component, Mixin} = Shopware;

Component.register('ratepay.logViewer.detail', {
    template,

    inject: {
        'repositoryFactory': 'repositoryFactory'
    },

    mixins: [
        Mixin.getByName('notification')
    ],

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    data() {
        return {
            entity: null,
            isLoading: false,
            processSuccess: false,
            repository: null,
        };
    },

    created() {
        this.repository = this.repositoryFactory.create('ratepay_api_log');
        let prom = this.loadEntity();
    },

    methods: {
        loadEntity() {
            return this.repository
                .get(this.$route.params.id, Shopware.Context.api)
                .then((entity) => {
                    this.entity = entity;
                    return new Promise((resolve, reject) => {resolve(this.entity)});
                });
        }
    }
});
