/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import Plugin from 'src/plugin-system/plugin.class';

export default class InstallmentPaymentSwitch extends Plugin {

    static options = {
        paymentTypeBankTransfer: 'BANK-TRANSFER',
        paymentTypeDirectDebit: 'DIRECT-DEBIT',
        selectorTypeField: 'input[name="ratepay[installment][paymentType]"]'
    };

    init() {
        this._sepaForm = this.el.querySelector('#rp-sepa-form');
        this.el.querySelectorAll(this.options.selectorTypeField).forEach(element => {
            element.addEventListener('change', this._onChangeType.bind(this));
        });

        this._onChangeType();
    }

    _onChangeType() {
        let currentValue = this.el.querySelector(this.options.selectorTypeField + ':checked').value;

        if (currentValue === this.options.paymentTypeDirectDebit) {
            this._showSepaForm();
        } else {
            this._hideSepaForm()
        }
    }

    _hideSepaForm() {
        if (this._sepaForm) {
            this._sepaForm.querySelector('#rp-iban-account-holder').removeAttribute('required');
            this._sepaForm.querySelector('#rp-iban-account-number').removeAttribute('required');
            this._sepaForm.querySelector('#sepaconfirmation').removeAttribute('required');
            this._sepaForm.querySelector('#rp-iban-account-holder').setAttribute('disabled', 'disabled');
            this._sepaForm.querySelector('#rp-iban-account-number').setAttribute('disabled', 'disabled');
            this._sepaForm.querySelector('#sepaconfirmation').setAttribute('disabled', 'disabled');
        }
    }

    _showSepaForm() {
        if (this._sepaForm) {
            this._sepaForm.querySelector('#rp-iban-account-holder').removeAttribute('disabled');
            this._sepaForm.querySelector('#rp-iban-account-number').removeAttribute('disabled');
            this._sepaForm.querySelector('#sepaconfirmation').removeAttribute('disabled');
            this._sepaForm.querySelector('#rp-iban-account-holder').setAttribute('required', 'required');
            this._sepaForm.querySelector('#rp-iban-account-number').setAttribute('required', 'required');
            this._sepaForm.querySelector('#sepaconfirmation').setAttribute('required', 'required');
        }
    }
}
