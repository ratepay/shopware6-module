/*
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import Plugin from 'src/plugin-system/plugin.class';

export default class Installment extends Plugin {

    init() {
        this._registerEvents();
    }

    _registerEvents() {
        this.$el.hide();
    }
}


