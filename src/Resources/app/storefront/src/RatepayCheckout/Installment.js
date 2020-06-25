/*
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import Plugin from 'src/plugin-system/plugin.class';
import LoadingIndicator from 'src/utility/loading-indicator/loading-indicator.util';
import HttpClient from 'src/service/http-client.service';

// xhr call storage
let xhr = null;

export default class Installment extends Plugin {

    static options = {
        calculationTypeTime: 'time',
        calculationTypeRate: 'rate'
    };

    init() {
        this._runtimeSelect = this.el.querySelector('#rp-btn-runtime');
        this._rateInput = this.el.querySelector('#rp-rate-value');
        this._rateButton = this.el.querySelector('#rp-rate-button');
        this._resultContainer = this.el.querySelector('#rp-result-container');
        this._typeHolder = this.el.querySelector('#rp-calculation-type');
        this._valueHolder = this.el.querySelector('#rp-calculation-value');

        this._registerEvents();
    }

    _registerEvents() {
        this._runtimeSelect.addEventListener('change', this._onSelectRuntime.bind(this));
        this._rateInput.addEventListener('input', this._onInputRate.bind(this));
        this._rateButton.addEventListener('click', this._onSubmitRate.bind(this));
    }

    _onSelectRuntime() {
        this._fetchInstallmentPlan(this.options.calculationTypeTime, this._runtimeSelect.value);
    }

    _onInputRate() {
        if (this._rateInput.value === '') {
            this._rateButton.setAttribute('disabled', 'disabled');
        }  else {
            this._rateButton.removeAttribute('disabled');
        }
    }

    _onSubmitRate() {
        this._fetchInstallmentPlan(this.options.calculationTypeRate, this._rateInput.value);
    }

    _fetchInstallmentPlan(type, value) {
        const client = new HttpClient(window.accessKey, window.contextToken);
        const url = `${window.rpInstallmentCalculateUrl}?type=${type}&value=${value}`;

        this._activateLoader();

        // interrupt already running ajax calls
        if (xhr) xhr.abort();

        const cb = (response) => {
            this._setContent(response);
            this._typeHolder.value = type;
            this._valueHolder.value = value;
        };

        xhr = client.get(url, this._executeCallback.bind(this, cb));
    }

    _activateLoader() {
        this._setContent(`<div style="text-align: center">${LoadingIndicator.getTemplate()}</div>`);
    }

    _executeCallback(cb, response)  {
        if (typeof cb === 'function') {
            cb(response);
        }
    }

    _setContent(content) {
        this._resultContainer.innerHTML = content;
    }

}
