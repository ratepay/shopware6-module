/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import Plugin from 'src/plugin-system/plugin.class';
import FormSerializeUtil from 'src/utility/form/form-serialize.util';
import ElementLoadingIndicatorUtil from 'src/utility/loading-indicator/element-loading-indicator.util';

export default class Secci extends Plugin {

    init() {
        this._submitButtons = this.el.querySelectorAll('[data-ratepay-secci-delivery-type]');
        this._resetButtons = this.el.querySelectorAll('[data-ratepay-secci-reset]');
        this._form = document.forms['confirmOrderForm'];
        this._action = this.el.dataset.ratepaySecciAction;
        this._errorUrl = this.el.dataset.ratepaySecciErrorUrl;
        this._motoEmailInput = this.el.querySelector('#ratepaySecciEmail');
        this._motoSuccessMail = this.el.querySelector('[data-ratepay-secci-moto-mail]');
        this._motoSuccessDocumentId = this.el.querySelector('[data-ratepay-secci-moto-documentid]');
        this._registerEvents();
    }

    _registerEvents() {
        this._submitButtons.forEach(button => button.addEventListener('click', this.onSubmit.bind(this)));
        this._resetButtons.forEach(button => button.addEventListener('click', this._onReset.bind(this)));
    }

    onSubmit(event) {
        this._addLoadingIndicators();
        this._sendRequest(event);
    }

    _onReset() {
        this.el.classList.remove('ratepay--secci_banner--downloaded', 'ratepay--secci_banner--mailed');
    }

    _sendRequest(event) {
        const deliveryType = event.currentTarget.dataset.ratepaySecciDeliveryType;

        const formData = FormSerializeUtil.serialize(this._form);
        formData.append('ratepay[secciDeliveryType]', deliveryType);

        if (this._motoEmailInput) {
            formData.append('motoSecciEmail', this._motoEmailInput.value);
        }

        const fetchOptions = {
            method: 'POST',
            headers: {'X-Requested-With': 'XMLHttpRequest'},
            body: formData,
        };

        fetch(this._action, fetchOptions)
            .then(response => response.json())
            .then(response => this._processResponse(response))
            .catch(() => this._goToErrorPage());
    }

    _processResponse(response) {
        if (response.success) {
            this._removeLoadingIndicators();
            this.el.classList.remove('ratepay--secci_banner--error');
            this.el.classList.add(response.deliveryMethod === 'pdf' ? 'ratepay--secci_banner--downloaded' : 'ratepay--secci_banner--mailed');

            if (response.document) {
                this._downloadPdf(response.document);
            } else if (response.deliveryMethod === 'moto') {
                this._motoSuccessMail.textContent = response.email;
                this._motoSuccessDocumentId.textContent = response.documentId;
            }
        } else {
            this._goToErrorPage();
        }
    }

    _addLoadingIndicators() {
        this._submitButtons.forEach(button => ElementLoadingIndicatorUtil.create(button));
    }

    _removeLoadingIndicators() {
        this._submitButtons.forEach(button => ElementLoadingIndicatorUtil.remove(button));
    }

    _downloadPdf(documentData) {
        const {id, contentType = 'application/pdf', data} = documentData;

        // Decode base64 string to a binary array for optimal memory and processing efficiency
        const byteCharacters = atob(data);
        const byteNumbers = new Uint8Array(byteCharacters.length);
        for (let i = 0; i < byteCharacters.length; i++) {
            byteNumbers[i] = byteCharacters.charCodeAt(i);
        }

        const blob = new Blob([byteNumbers], {type: contentType});
        const blobUrl = URL.createObjectURL(blob);

        // Ensure the filename provides clear identification for the user
        const fileName = id.toLowerCase().endsWith('.pdf') ? id : `${id}.pdf`;

        // Create temporary anchor to initiate the download without altering the DOM layout
        const downloadLink = document.createElement('a');
        downloadLink.href = blobUrl;
        downloadLink.download = fileName;
        downloadLink.style.display = 'none';

        document.body.appendChild(downloadLink);
        downloadLink.click();

        // Release resources immediately to minimise memory overhead
        document.body.removeChild(downloadLink);
        window.URL.revokeObjectURL(blobUrl);
    }

    _goToErrorPage() {
        window.location = this._errorUrl;
    }
}
