import Plugin from 'src/plugin-system/plugin.class';

export default class RatepayPayments extends Plugin {

    init() {
        this.$el = $(this.el);

        this.ibanAccountNumberInput = this.$el.find('#rp-iban-account-number');

        this.bankCodeForm = this.$el.find('#rp-form-bank-code');
        this.bankCodeInput = this.$el.find('#rp-bank-code');

        this._registerEvents();
    }

    _registerEvents() {
        if (this.ibanAccountNumberInput.length) {
            this.ibanAccountNumberInput.on('blur keyup change click', () => {
                if (this._getIbanCountryCode(this.ibanAccountNumberInput.val()) == 'de') {
                    this._hideBankCode();
                }
                else {
                    this._showBankCode();
                }
            });
        }
    }

    _getIbanCountryCode(formValue) {
        return formValue.substring(0,2).toLowerCase();
    }

    _hideBankCode() {
        this.bankCodeForm.hide();
        this.bankCodeInput.prop('required', false);
        this.bankCodeInput.prop('disabled', true);
    }

    _showBankCode() {
        this.bankCodeForm.show();
        this.bankCodeInput.prop('disabled', false);
        this.bankCodeInput.prop('required', true);
    }
}

