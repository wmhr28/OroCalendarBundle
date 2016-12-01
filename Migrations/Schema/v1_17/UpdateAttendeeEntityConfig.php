<?php

namespace Oro\Bundle\CalendarBundle\Migrations\Schema\v1_17;

use Psr\Log\LoggerInterface;

use Doctrine\DBAL\Types\Type;

use Oro\Bundle\EntityConfigBundle\Entity\ConfigModel;
use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;

class UpdateAttendeeEntityConfig extends ParametrizedMigrationQuery
{
    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        $logger = new ArrayLogger();
        $this->migrateConfigs($logger, true);

        return $logger->getMessages();
    }

    /**
     * {@inheritdoc}
     */
    public function execute(LoggerInterface $logger)
    {
        $this->migrateConfigs($logger);
    }

    /**
     * @param LoggerInterface $logger
     * @param bool $dryRun
     */
    protected function migrateConfigs(LoggerInterface $logger, $dryRun = false)
    {
        $query  = 'SELECT c.id, c.data FROM oro_entity_config c'
            . ' WHERE c.class_name = :class_name AND c.mode = :mode';
        $params = ['class_name' => 'Oro\Bundle\CalendarBundle\Entity\Attendee', 'mode' => ConfigModel::MODE_DEFAULT];
        $types  = ['class_name' => Type::STRING, 'mode' => Type::STRING];
        $this->logQuery($logger, $query, $params, $types);

        // prepare update query
        $rows = $this->connection->fetchAll($query, $params, $types);
        $row = $rows[0];
        $data = $this->connection->convertToPHPValue($row['data'], 'array');
        if (isset($data['security'])) {
            unset($data['security']);
            $id = $row['id'];
            $updateQuery = [
                'UPDATE oro_entity_config SET data = :data WHERE id = :id',
                ['id' => $id, 'data' => $data],
                ['id' => Type::INTEGER, 'data' => Type::TARRAY]
            ];
            $this->logQuery($logger, $updateQuery[0], $updateQuery[1], $updateQuery[2]);
            if (!$dryRun) {
                $this->connection->executeUpdate($updateQuery[0], $updateQuery[1], $updateQuery[2]);
            }
        }
    }
}
