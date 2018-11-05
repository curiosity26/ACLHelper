# Acl Helper Bundle

This bundle is created for the purpose of applying ACL's when querying entities; preventing the need for
post-query iteration. This helps with things like pagination and handling security on multiple records
simultaneously.

Querying does not apply to associations. It is recommended that you handle your own querying for any deep
associations that may require ACL's be applied to them.

Field level security is also not taken into account at query time. It is up to you and your application
to handle field level security.

## Example

In this example, let's pretend we have an entity which is owned by `user1` and those with `ROLE_ADMIN` can
edit, delete and view, `ROLE_USER` users can just view.

```php
<?php

namespace App\Controller;

use Curiosity26\AclHelperBundle\Helper\AclHelper;
use Curiosity26\AclHelperBundle\Tests\Entity\TestObject;
use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;

class MyController extends FOSRestController implements ClassResourceInterface {
    
    /**
     * @var AclHelper
     */
    private $aclHelper;
    
    public function __construct(AclHelper $aclHelper)
    {
        $this->aclHelper = $aclHelper;
    }
    
    /**
     * @Rest\View()
     * @return TestObject[]
     */
    public function cgetAction()
    {
        // Get all of the TestObjects this user can view
        $agent   = $this->aclHelper->createAgent(TestObject::class);
        $permMap = new BasicPermissionMap();
        $builder = $permMap->getMaskBuilder();
        $masks   = $permMap->getMasks('VIEW', null);
        
        foreach ($masks as $mask) {
            $builder->add($mask);
        }
        
        return $agent->findAll($builder->get(), $this->getUser());
    }
}

```

## ACL Manager

To make it easier to build ACLs, the ACL Manager was created. It's pretty much just a chain wrapper
that allows the ACL to be found/created and ACEs to be inserted, updated or deleted.

### Example

```php
<?php

namespace App\Controller;

use Curiosity26\AclHelperBundle\Helper\AclHelper;
use Curiosity26\AclHelperBundle\Tests\Entity\TestObject;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

class MyController extends FOSRestController implements ClassResourceInterface {
    
    /**
     * @var AclHelper
     */
    private $aclHelper;
    
    public function __construct(AclHelper $aclHelper)
    {
        $this->aclHelper = $aclHelper;
    }
    
    public function postAction(TestObject $object)
    {
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($object);
        
        $aclManager = $this->aclHelper->createAclManager();
        
        // The current user needs to be the owner
        // The ROLE_ADMIN must have view, edit, delete permissions
        // ROLE_USER users should be able to view
        $aclManager->aclFor($object)
            ->insertObjectAce(UserSecurityIdentity::fromAccount($this->getUser()), MaskBuilder::MASK_OWNER)
            ->insertObjectAce(
                new RoleSecurityIdentity('ROLE_ADMIN'),
                MaskBuilder::MASK_VIEW | MaskBuilder::MASK_EDIT | MaskBuilder::MASK_DELETE
            )
            ->insertObjectAce(new RoleSecurityIdentity('ROLE_USER'), MaskBuilder::MASK_VIEW)
            ->save()
        ;
        
        return $this->view(null, 201);
    }
}
```