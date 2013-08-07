## Current Version

TODO correct model inheritance

TODO layout with checkbox

TODO correct default page redirect in LoginSuccessHandler

TODO check force login

## Installation

### Add bundle to your composer.json file

Set needed version and branch in `version` and `reference` of package.

``` js
// composer.json

{
    "require": {
        // ...
        "friendsofsymfony/user-bundle": "~2.0.*@dev"
		// ...
        "fanforfun/forced-security-bundle": "dev-master"
    }
}
```

### Add bundle to your application kernel

``` php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new FOS\UserBundle\FOSUserBundle(),
        // ...
        new Fanforfun\ForcedSecurityBundle\FanforfunForcedSecurityBundle(),
        // ...
    );
}
```

### Download the bundle using Composer

``` bash
$ php composer.phar update fanforfun/forced-security-bundle
```

## Usage


<?php

``` yaml
# app/config/config.yml
fos_user:
    db_driver: orm # other valid values are 'mongodb', 'couchdb' and 'propel'
    firewall_name: main
    user_class: Acme\UserBundle\Entity\User
```


``` php
// src/Acme/UserBundle/Entity/User.php

namespace Acme\UserBundle\Entity;

use Fanforfun\ForcedSecurityBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }
}
```

## Licenses

Refer to the source code of the included files for license information

## References

1. http://symfony.com/

2. https://github.com/FriendsOfSymfony/FOSUserBundle