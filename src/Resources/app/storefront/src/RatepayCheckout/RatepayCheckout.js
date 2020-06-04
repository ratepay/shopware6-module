/*
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import Plugin from 'src/plugin-system/plugin.class';

export default class RatepayCheckout extends Plugin {

    init() {
        this.$el = $(this.el);
        this._registerEvents();
    }

    _registerEvents() {
    }
}

