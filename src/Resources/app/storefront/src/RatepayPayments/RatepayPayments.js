export default class RatepayPayments extends Plugin {

    init() {
        this.$el = $(this.el);
        this.$ibanAccountNumberInput = this.$el.find('#rp-iban-account-number');
        this.$bankCodeForm = this.$el.find('#rp-form-bank-code');
        this._registerEvents();
    }

    _registerEvents() {
        this.$ibanAccountNumberInput.on('change', () => {

        });

    }
}

