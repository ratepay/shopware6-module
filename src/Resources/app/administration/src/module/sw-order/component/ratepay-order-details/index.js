/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import template from './ratepay-order-details.html.twig';
import './ratepay-order-details.scss';

const {Component} = Shopware;

Component.register('ratepay-order-details', {
    template,

    props: ['order'],
});
