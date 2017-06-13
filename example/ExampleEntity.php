<?php

namespace Example;

use Doctrine\ORM\Mapping as ORM;
use DoctrineEncrypt\Annotation\Encrypted;

/**
 * @ORM\Entity()
 * @ORM\Table(name="example")
 */
class ExampleEntity
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(name="id", type="integer")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="unencrypted", type="string")
     */
    protected $unencrypted;

    /**
     * @var string
     *
     * @ORM\Column(name="encrypted", type="encrypted")
     */
    protected $encrypted;

    /**
     * @return string
     */
    public function getUnencrypted()
    {
        return $this->unencrypted;
    }

    /**
     * @param string $unencrypted
     */
    public function setUnencrypted($unencrypted)
    {
        $this->unencrypted = $unencrypted;
    }

    /**
     * @return string
     */
    public function getEncrypted()
    {
        return $this->encrypted;
    }

    /**
     * @param string $encrypted
     */
    public function setEncrypted($encrypted)
    {
        $this->encrypted = $encrypted;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}