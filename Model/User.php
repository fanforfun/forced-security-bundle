<?php

namespace Fanforfun\ForcedSecurityBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContext;

/**
 * @ORM\Table(name = "users")
 * @ORM\Entity
 *
 * @Assert\Callback(methods={"isResponseAreaCorrect"})
 */
class User extends BaseUser
{

    /**
     * @var string
     *
     * @ORM\Column(name = "session_id", type = "string", nullable = true)
     */
    private $sessionId;

    public function __construct()
    {
        parent::__construct();

        $this->setEnabled(true);
    }


    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }
}
