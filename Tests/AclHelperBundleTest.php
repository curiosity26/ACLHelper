<?php
/**
 * Created by PhpStorm.
 * User: alex.boyce
 * Date: 11/2/18
 * Time: 2:53 PM
 */

namespace Curiosity26\AclHelperBundle\Tests;

use Curiosity26\AclHelperBundle\Entity\AclClass;
use Curiosity26\AclHelperBundle\Entity\Entry;
use Curiosity26\AclHelperBundle\Entity\ObjectIdentity;
use Curiosity26\AclHelperBundle\Entity\SecurityIdentity;
use Curiosity26\AclHelperBundle\Helper\AclHelper;
use Curiosity26\AclHelperBundle\QueryBuilder\AclHelperQueryBuilder;
use Curiosity26\AclHelperBundle\Tests\Entity\TestObject;
use Symfony\Component\Security\Acl\Domain\PermissionGrantingStrategy;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity as AclObjectIdentity;

class AclHelperBundleTest extends DatabaseTestCase
{
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

    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        parent::setUp();
        $this->aclProvider  = $this->get('security.acl.provider');
        $this->aclHelper    = $this->get(AclHelper::class);
        $this->queryBuilder = $this->get(AclHelperQueryBuilder::class);
    }

    protected function loadSchemas(): array
    {
        return [
            AclClass::class,
            Entry::class,
            ObjectIdentity::class,
            SecurityIdentity::class,
            TestObject::class,
        ];
    }

    public function testView()
    {
        $manager    = $this->doctrine->getManager();
        $agent      = $this->aclHelper->createAgent(TestObject::class);
        $aclManager = $this->aclHelper->createAclManager();
        $permMap    = new BasicPermissionMap();

        $objectIdentity = new AclObjectIdentity('class', TestObject::class);
        $aclManager->aclFor($objectIdentity);

        $owner1         = new User('owner1', 'owner1_pass');
        $owner1Identity = UserSecurityIdentity::fromAccount($owner1);
        $aclManager->insertClassAce($owner1Identity, MaskBuilder::MASK_OWNER);

        $moderatorIdentity = new RoleSecurityIdentity('ROLE_MODERATOR');
        $aclManager->insertClassAce(
            $moderatorIdentity,
            MaskBuilder::MASK_VIEW | MaskBuilder::MASK_EDIT | MaskBuilder::MASK_DELETE
        );

        $userRoleIdentity = new RoleSecurityIdentity('ROLE_USER');
        $aclManager->insertClassAce($userRoleIdentity, MaskBuilder::MASK_VIEW);

        $aclManager->save();

        $testObject = new TestObject();
        $testObject->setName('Wicked Cool Object');

        $childObject = new TestObject();
        $childObject->setName('Child');
        $testObject->addChild($childObject);

        $manager->persist($testObject);
        $manager->flush();

        $testObject2 = new TestObject();
        $testObject2->setName('Wicked Cool Object 2');

        $manager->persist($testObject2);
        $manager->flush();

        $aclManager->aclFor($testObject2);

        $maskBuilder = $permMap->getMaskBuilder();
        foreach ($permMap->getMasks(BasicPermissionMap::PERMISSION_VIEW, $testObject) as $mask) {
            $maskBuilder->add($mask);
        }

        $mask1      = $maskBuilder->get();
        $owner1Objs = $agent->findAll($mask1, $owner1);

        $this->assertCount(3, $owner1Objs);
        $obj1 = $owner1Objs[0];

        $this->assertNotNull($obj1);

        $viewUser   = new User('view', 'view_user1', ['ROLE_USER']);
        $owner2Objs = $agent->findAll($mask1, $viewUser);
        $this->assertCount(3, $owner2Objs);
        $owner2Objs = $agent->findAll($mask1, $viewUser, PermissionGrantingStrategy::ANY, false);
        $this->assertCount(1, $owner2Objs);
        $owner2Objs = $agent->findBy($mask1, $viewUser, ['name' => 'Wicked Cool Object']);
        $this->assertCount(1, $owner2Objs);

        $modObjs = $agent->findAll($mask1, $moderatorIdentity);
        $this->assertCount(3, $modObjs);
        $modObjs = $agent->findBy($mask1, $moderatorIdentity, ['name' => ['LIKE' => 'Wicked %']]);
        $this->assertCount(2, $modObjs);

        $roleAdmin = new RoleSecurityIdentity('ROLE_ADMIN');
        $adminObjs = $agent->findAll($mask1, $roleAdmin);
        $this->assertCount(3, $adminObjs);
        $adminObjs = $agent->findBy($mask1, $roleAdmin, ['children.name' => 'Child']);
        $this->assertCount(1, $adminObjs);
        $adminObjs = $agent->findBy($mask1, $roleAdmin, ['parent.name' => ['LIKE' => 'Wicked %']]);
        $this->assertCount(1, $adminObjs);

        $roleSuperAdmin = new RoleSecurityIdentity('ROLE_SUPER_ADMIN');
        $supAdminObjs   = $agent->findAll($mask1, $roleSuperAdmin);
        $this->assertCount(0, $supAdminObjs);
    }
}
