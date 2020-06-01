<?php

namespace Ratepay\RatepayPayments\Bootstrap;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class CustomFields extends AbstractBootstrap
{
    /**
     * @var EntityRepositoryInterface
     */
    private $customFieldSetRepository;

    public function injectServices(): void
    {
        $this->customFieldSetRepository = $this->container->get('custom_field_set.repository');
    }

    public function update()
    {
        $this->install();
    }

    public function install()
    {
        $this->customFieldSetRepository->create([
            [
                'name' => 'ratepay_order_item_custom_fields',
                'active' => true,
                'config' => [
                    'label' => [
                        'de-DE' => 'Ratepay Zusatzfelder',
                        'en-GB' => 'Ratepay Custom Fields'
                    ],
                ],
                'customFields' => [
                    [
                        'name' => 'delivered',
                        'type' => CustomFieldTypes::BOOL,
                        'config' => [
                            'label' => [
                                'de-DE' => 'Versendet',
                                'en-GB' => 'Delivered'
                            ]
                        ]
                    ],
                    [
                        'name' => 'cancelled',
                        'type' => CustomFieldTypes::BOOL,
                        'config' => [
                            'label' => [
                                'de-DE' => 'Storniert',
                                'en-GB' => 'Cancelled'
                            ]
                        ]
                    ],
                    [
                        'name' => 'returned',
                        'type' => CustomFieldTypes::BOOL,
                        'config' => [
                            'label' => [
                                'de-DE' => 'Retourniert',
                                'en-GB' => 'Returned'
                            ]
                        ]
                    ],
                ],
                'relations' => [
                    [
                        'entityName' => 'order_line_item'
                    ]
                ]
            ]
        ], $this->defaultContext);

    }

    public function uninstall($keepUserData = false)
    {

    }

    public function activate()
    {
        $this->install();
    }

    public function deactivate()
    {

    }
}
