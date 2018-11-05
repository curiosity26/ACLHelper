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
     * @return array
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