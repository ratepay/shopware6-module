<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Logging\Controller;

use Doctrine\DBAL\Connection;
use Ratepay\RpayPayments\Components\Logging\Model\ApiRequestLogEntity;
use Ratepay\RpayPayments\Components\Logging\Model\Definition\ApiRequestLogDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Field\AssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StorageAware;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/ratepay', defaults: [
    '_routeScope' => ['administration'],
])]
class DistinctValues extends AbstractController
{
    public function __construct(
        private readonly Connection $connection,
        private readonly DefinitionInstanceRegistry $definitionRegistry
    ) {
    }

    #[Route(path: '/api-log/distinct-values/{fields}', name: 'ratepay.admin.api-log.distinct-values', methods: ['GET'])]
    public function distinctValues(string $fields = ''): Response
    {
        $allowedFields = [
            ApiRequestLogEntity::FIELD_OPERATION,
            ApiRequestLogEntity::FIELD_SUB_OPERATION,
            ApiRequestLogEntity::FIELD_REASON_CODE,
            ApiRequestLogEntity::FIELD_REASON_TEXT,
            ApiRequestLogEntity::FIELD_STATUS_CODE,
            ApiRequestLogEntity::FIELD_STATUS_TEXT,
            ApiRequestLogEntity::FIELD_RESULT_CODE,
            ApiRequestLogEntity::FIELD_RESULT_TEXT,
            ApiRequestLogEntity::FIELD_VERSION,
        ];

        $fields = explode('|', $fields);

        $fields = array_filter($fields, static fn ($item): bool => in_array($item, $allowedFields, true));

        $definition = $this->definitionRegistry->get(ApiRequestLogDefinition::class);

        $results = [];
        foreach ($fields as $field) {
            $fieldDefinition = $definition->getField($field);
            if (!$fieldDefinition instanceof StorageAware || $fieldDefinition instanceof AssociationField) {
                continue;
            }

            $columnName = $fieldDefinition->getStorageName();

            $qb = $this->connection->createQueryBuilder();
            $qb->distinct()
                ->select($columnName)
                ->from($definition->getEntityName())
                ->andWhere($qb->expr()->isNotNull($columnName))
                ->andWhere($columnName . " != ''");

            $results[] = [
                'name' => $field,
                'options' => $qb->executeQuery()->fetchFirstColumn(),
            ];
        }

        return $this->json([
            'success' => true,
            'results' => $results,
        ]);
    }
}
