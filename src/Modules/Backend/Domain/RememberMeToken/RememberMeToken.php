<?php

namespace ForkCMS\Modules\Backend\Domain\RememberMeToken;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="rememberme_token")
 */
class RememberMeToken
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=88, options={"fixed" = true})
     */
    private string $series;

    /**
     * @ORM\Column(type="string", length=88, options={"fixed" = true})
     */
    private string $value;

    /**
     * @ORM\Column(type="datetime")
     */
    private DateTime $lastUsed;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private string $class;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private string $username;
}
