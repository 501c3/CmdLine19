<?php

namespace App\Entity\Setup;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tss
 *
 * @ORM\Table(name="tss")
 * @ORM\Entity(repositoryClass="App\Repository\Setup\TssRepository")
 */
class Tss
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var array
     *
     * @ORM\Column(name="`describe`", type="json", nullable=false)
     */
    private $describe;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\AgeClass", mappedBy="tss")
     */
    private $ageClass;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\AgeTeam", mappedBy="tss")
     */
    private $ageTeam;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\PrfClass", mappedBy="tss")
     */
    private $prfClass;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\PrfTeam", mappedBy="tss")
     */
    private $prfTeam;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ageClass = new \Doctrine\Common\Collections\ArrayCollection();
        $this->ageTeam = new \Doctrine\Common\Collections\ArrayCollection();
        $this->prfClass = new \Doctrine\Common\Collections\ArrayCollection();
        $this->prfTeam = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Tss
     */
    public function setId(int $id): Tss
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return array
     */
    public function getDescribe(): array
    {
        return $this->describe;
    }

    /**
     * @param array $describe
     * @return Tss
     */
    public function setDescribe(array $describe): Tss
    {
        $this->describe = $describe;
        return $this;
    }



}
