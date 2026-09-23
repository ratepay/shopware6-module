import template from './ratepay-choose-secci-variant.html.twig';
import './ratepay-choose-secci-variant.scss';
import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

Shopware.Component.register('ratepay-choose-secci-variant', {
    template,

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    emits: ['update:value'],

    props: {
        value: {
            type: [String, Number],
            required: false,
            default: null
        },
        label: {
            type: String,
            required: false,
            default: ''
        },
        disabled: {
            type: Boolean,
            required: false,
            default: false
        }
    },

    data() {
        return {
            groupName: `ratepay-secci-${Shopware.Utils.createId()}`,
            variants: [
                { value: '1', snippet: 'above', image: 'rpaypayments/administration/static/img/secci-above.svg' },
                { value: '2', snippet: 'below', image: 'rpaypayments/administration/static/img/secci-below.svg' }
            ]
        };
    },

    computed: {
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        }
    },

    methods: {
        isSelected(value) {
            return String(this.value) === value;
        },

        selectVariant(value) {
            if (!this.disabled) {
                this.$emit('update:value', value);
            }
        }
    }
});
