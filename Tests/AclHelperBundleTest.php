<?php
/**
 * Created by PhpStorm.
 * User: alex.boyce
 * Date: 11/2/18
 * Time: 2:53 PM
 */

namespace Curiosity26\AclHelperBundle\Tests;

use Curiosity26\AclHelperBundle\Entity\Entry;
use Curiosity26\AclHelperBundle\Entity\SecurityIdentity;
use Curiosity26\AclHelperBundle\Helper\AclHelper;
use Curiosity26\AclHelperBundle\QueryBuilder\AclHelperQueryBuilder;
use Curiosity26\AclHelperBundle\Tests\Entity\TestObject;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Acl\Dbal\Schema;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\User;

class AclHelperBundleTest extends DatabaseTestCase
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authDecider;

    /**
     * @var MutableAclProviderInterface
     */
    private $aclProvider;

    /**
     * @var AclHelper
     */
    private $aclHelper;

    /**
     * @var AclHelperQueryBuilder
     */
    private $queryBuilder;

    /**
     * @var Schema
     */
    private $schema;

    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        parent::setUp();
        $this->authDecider = $this->get(AuthorizationCheckerInterface::class);
        $this->aclProvider = $this->get('security.acl.provider');
        $this->aclHelper = $this->get(AclHelper::class);
        $this->queryBuilder = $this->get(AclHelperQueryBuilder::class);
        $this->schema = $this->get('security.acl.dbal.schema');
    }

    protected function setupAclSchemas()
    {
        $connection = $this->doctrine->getConnection();
        $this->schema->addToSchema($connection->getSchemaManager()->createSchema());

        foreach ($this->schema->toSql($connection->getDatabasePlatform()) as $sql) {
            $connection->exec($sql);
        }
    }

    protected function loadSchemas(): array
    {
        return [
            TestObject::class
        ];
    }

    public function testView()
    {
        $manager = $this->doctrine->getManager();
        $agent = $this->aclHelper->createAgent(TestObject::class);
        $permMap = new BasicPermissionMap();
        $testObject = new TestObject();
        $testObject->setName('Wicked Cool Object');

        $manager->persist($testObject);
        $manager->flush();

        $objectIdentity = ObjectIdentity::fromDomainObject($testObject);
        $acl = $this->aclProvider->createAcl($objectIdentity);

        $owner1 = new User('owner1', 'owner1_pass');
        $owner1Identity = UserSecurityIdentity::fromAccount($owner1);
        $acl->insertObjectAce($owner1Identity, MaskBuilder::MASK_OWNER);
        $this->aclProvider->updateAcl($acl);

        $maskBuilder = $permMap->getMaskBuilder();
        foreach ($permMap->getMasks(BasicPermissionMap::PERMISSION_VIEW, $testObject) as $mask) {
            $maskBuilder->add($mask);
        }

        $mask1      = $maskBuilder->get();
        $owner1Objs = $agent->findAll($mask1, $owner1);

        $this->assertNotNull($owner1Objs);
        $obj1 = $owner1Objs[0];

        $this->assertNotNull($obj1);
    }
}
