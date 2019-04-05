<?php

namespace App\Entity\Setup;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use /** @noinspection PhpUnusedAliasInspection */
    Doctrine\ORM\Mapping as ORM;

/**
 * PrfTeamClass
 *
 * @ORM\Table(name="prf_team_class")
 * @ORM\Entity(repositoryClass="App\Repository\Setup\PrfTeamClassRepository")
 */
class PrfTeamClass
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="smallint", nullable=false)
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
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\AgeTeamClass", mappedBy="prfTeamClass")
     */
    private $ageTeamClass;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\Value", inversedBy="prfTeamClass")
     * @ORM\JoinTable(name="prf_team_class_has_value",
     *   joinColumns={
     *     @ORM\JoinColumn(name="prf_team_class_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="value_id", referencedColumnName="id")
     *   }
     * )
     */
    private $value;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ageTeamClass = new ArrayCollection();
        $this->value = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
     * @return PrfTeamClass
     */
    public function setDescribe(array $describe): PrfTeamClass
    {
        $this->describe = $describe;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getAgeTeamClass(): Collection
    {
        return $this->ageTeamClass;
    }


    /**
     * @return Collection
     */
    public function getValue(): Collection
    {
        return $this->value;
    }



}
