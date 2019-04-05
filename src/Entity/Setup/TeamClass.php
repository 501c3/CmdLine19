<?php

namespace App\Entity\Setup;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use /** @noinspection PhpUnusedAliasInspection */
    Doctrine\ORM\Mapping as ORM;

/**
 * TeamClass
 *
 * @ORM\Table(name="team_class", indexes={@ORM\Index(name="fk_team_class_prf_team_class1_idx", columns={"prf_team_class_id"}), @ORM\Index(name="fk_team_class_age_team_class1_idx", columns={"age_team_class_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Setup\TeamClassRepository")
 */
class TeamClass
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
     * @var \App\Entity\Setup\AgeTeamClass
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Setup\AgeTeamClass")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="age_team_class_id", referencedColumnName="id")
     * })
     */
    private $ageTeamClass;

    /**
     * @var \App\Entity\Setup\PrfTeamClass
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Setup\PrfTeamClass")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="prf_team_class_id", referencedColumnName="id")
     * })
     */
    private $prfTeamClass;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\Event", mappedBy="teamClass")
     */
    private $event;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->event = new ArrayCollection();
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
     * @return TeamClass
     */
    public function setId(int $id): TeamClass
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
     * @return TeamClass
     */
    public function setDescribe(array $describe): TeamClass
    {
        $this->describe = $describe;
        return $this;
    }

    /**
     * @return AgeTeamClass
     */
    public function getAgeTeamClass(): AgeTeamClass
    {
        return $this->ageTeamClass;
    }

    /**
     * @param AgeTeamClass $ageTeamClass
     * @return TeamClass
     */
    public function setAgeTeamClass(AgeTeamClass $ageTeamClass): TeamClass
    {
        $this->ageTeamClass = $ageTeamClass;
        return $this;
    }

    /**
     * @return PrfTeamClass
     */
    public function getPrfTeamClass(): PrfTeamClass
    {
        return $this->prfTeamClass;
    }

    /**
     * @param PrfTeamClass $prfTeamClass
     * @return TeamClass
     */
    public function setPrfTeamClass(PrfTeamClass $prfTeamClass): TeamClass
    {
        $this->prfTeamClass = $prfTeamClass;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getEvent(): Collection
    {
        return $this->event;
    }
}
