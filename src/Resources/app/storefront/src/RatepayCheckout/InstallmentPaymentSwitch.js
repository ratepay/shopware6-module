/*
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import Plugin from 'src/plugin-system/plugin.class';

export default class InstallmentPaymentSwitch extends Plugin {

    static options = {
        hiddenCls: 'd-none',
        showCls: 'd-block',
        paymentTypeBankTransfer: 'BANK-TRANSFER',
        paymentTypeDirectDebit: 'DIRECT-DEBIT'
    };

    init() {
        this._sepaForm = this.el.querySelector('#rp-sepa-form');
        this._bankTransferLink = this.el.querySelector('#rp-switch-payment-type-bank-transfer');
        this._directDebitLink = this.el.querySelector('#rp-switch-payment-type-direct-debit');
        this._paymentTypeHolder = this.el.querySelector('#rp-calculation-payment-type');

        this._registerEvents();
    }

    _registerEvents() {
        if (this._bankTransferLink) {
            this._bankTransferLink.addEventListener('click', this._onSelectBankTransfer.bind(this));
        }
        if (this._directDebitLink) {
            this._directDebitLink.addEventListener('click', this._onSelectDirectDebit.bind(this));
        }
    }

    _onSelectBankTransfer() {
        this._hideSepaForm();
        this._hide(this._bankTransferLink);
        this._show(this._directDebitLink);
        this._paymentTypeHolder.value = this.options.paymentTypeBankTransfer;
    }

    _onSelectDirectDebit() {
        this._showSepaForm();
        this._show(this._bankTransferLink);
        this._hide(this._directDebitLink);
        this._paymentTypeHolder.value = this.options.paymentTypeDirectDebit;
    }

    _hideSepaForm() {
        if (this._sepaForm) {
            this._hide(this._sepaForm);
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
            this._show(this._sepaForm);
            this._sepaForm.querySelector('#rp-iban-account-holder').removeAttribute('disabled');
            this._sepaForm.querySelector('#rp-iban-account-number').removeAttribute('disabled');
            this._sepaForm.querySelector('#sepaconfirmation').removeAttribute('disabled');
            this._sepaForm.querySelector('#rp-iban-account-holder').setAttribute('required', 'required');
            this._sepaForm.querySelector('#rp-iban-account-number').setAttribute('required', 'required');
            this._sepaForm.querySelector('#sepaconfirmation').setAttribute('required', 'required');
        }
    }

    _hide(target) {
        if (target) {
            target.classList.remove(this.options.showCls);
            target.classList.add(this.options.hiddenCls);
        }
    }

    _show(target) {
        if (target) {
            target.classList.add(this.options.showCls);
            target.classList.remove(this.options.hiddenCls);
        }
    }
}
