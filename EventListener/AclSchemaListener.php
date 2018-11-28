<?php
/**
 * Created by PhpStorm.
 * User: alex.boyce
 * Date: 11/28/18
 * Time: 10:13 AM
 */

namespace Curiosity26\AclHelperBundle\EventListener;

use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Symfony\Component\Security\Acl\Dbal\Schema;

class AclSchemaListener
{
    private $schema;

    public function __construct(Schema $schema)
    {
        $this->schema = $schema;
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $args)
    {
        $schema = $args->getSchema();

        foreach ($schema->getTableNames() as $tableName) {
            if (false != preg_match('/^acl_/', $tableName)) {
                $this->schema->addToSchema($schema);
                break;
            }
        }
    }
}
