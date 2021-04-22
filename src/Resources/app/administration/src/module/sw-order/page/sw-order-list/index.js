import template from './sw-order-list.html.twig';

const {Component} = Shopware;

Component.override('sw-order-list', {
  template,

  data() {
    return {
      ratepayCreateOrderModal: false,
      salesChannels: null
    };
  },

  methods: {
    openRatepayCreateOrderModal() {
      this.ratepayCreateOrderModal = true
    }
  }
});

