<?php

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

/**
 * @Route("/api/ratepay", defaults={"_routeScope"={"administration"}})
 */
class DistinctValues extends AbstractController
{

    private Connection $connection;

    private DefinitionInstanceRegistry $definitionRegistry;

    public function __construct(Connection $connection, DefinitionInstanceRegistry $definitionRegistry)
    {
        $this->connection = $connection;

        $this->definitionRegistry = $definitionRegistry;
    }

    /**
     * @Route("/api-log/distinct-values/{fields}", name="ratepay.admin.api-log.distinct-values", methods={"GET"})
     */
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

        $fields = array_filter($fields, static fn($item): bool => in_array($item, $allowedFields));

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
                'options' => $qb->execute()->fetchFirstColumn()
            ];
        }

        return $this->json([
            'success' => true,
            'results' => $results,
        ]);
    }
}
