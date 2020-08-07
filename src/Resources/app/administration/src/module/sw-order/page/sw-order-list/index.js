/*
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import template from './sw-order-list.html.twig';

const { Component } = Shopware;

Component.override('sw-order-list', {
    template
});
