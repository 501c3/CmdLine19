<?php

namespace App\Entity\Setup;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use /** @noinspection PhpUnusedAliasInspection */
    Doctrine\ORM\Mapping as ORM;

/**
 * AgeTeamClass
 *
 * @ORM\Table(name="age_team_class")
 * @ORM\Entity(repositoryClass="App\Repository\Setup\AgeTeamClassRepository")
 */
class AgeTeamClass
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
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\PrfTeamClass", inversedBy="ageTeamClass")
     * @ORM\JoinTable(name="age_team_class_has_prf_team_class",
     *   joinColumns={
     *     @ORM\JoinColumn(name="age_team_class_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="prf_team_class_id", referencedColumnName="id")
     *   }
     * )
     */
    private $prfTeamClass;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\Value", inversedBy="ageTeamClass")
     * @ORM\JoinTable(name="age_team_class_has_value",
     *   joinColumns={
     *     @ORM\JoinColumn(name="age_team_class_id", referencedColumnName="id")
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
        $this->prfTeamClass = new ArrayCollection();
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
     * @param int $id
     * @return AgeTeamClass
     */
    public function setId(int $id): AgeTeamClass
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
     * @return AgeTeamClass
     */
    public function setDescribe(array $describe): AgeTeamClass
    {
        $this->describe = $describe;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getPrfTeamClass(): Collection
    {
        return $this->prfTeamClass;
    }

    /**
     * @param Collection $prfTeamClass
     * @return AgeTeamClass
     */
    public function setPrfTeamClass(Collection $prfTeamClass): AgeTeamClass
    {
        $this->prfTeamClass = $prfTeamClass;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getValue(): Collection
    {
        return $this->value;
    }
}
