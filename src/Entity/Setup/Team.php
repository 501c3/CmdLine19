<?php

namespace App\Entity\Setup;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use /** @noinspection PhpUnusedAliasInspection */
    Doctrine\ORM\Mapping as ORM;

/**
 * Team
 *
 * @ORM\Table(name="team", indexes={@ORM\Index(name="fk_team_prf_team1_idx", columns={"prf_team_id"}), @ORM\Index(name="fk_team_team_class1_idx", columns={"team_class_id"}), @ORM\Index(name="fk_team_age_team1_idx", columns={"age_team_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Setup\TeamClassRepository")
 */
class Team
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
     * @var \App\Entity\Setup\AgeTeam
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Setup\AgeTeam")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="age_team_id", referencedColumnName="id")
     * })
     */
    private $ageTeam;

    /**
     * @var \App\Entity\Setup\PrfTeam
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Setup\PrfTeam")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="prf_team_id", referencedColumnName="id")
     * })
     */
    private $prfTeam;

    /**
     * @var \App\Entity\Setup\TeamClass
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Setup\TeamClass")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="team_class_id", referencedColumnName="id")
     * })
     */
    private $teamClass;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\Person", inversedBy="team")
     * @ORM\JoinTable(name="team_has_person",
     *   joinColumns={
     *     @ORM\JoinColumn(name="team_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     *   }
     * )
     */
    private $person;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->person = new ArrayCollection();
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
     * @return Team
     */
    public function setId(int $id): Team
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return AgeTeam
     */
    public function getAgeTeam(): AgeTeam
    {
        return $this->ageTeam;
    }

    /**
     * @param AgeTeam $ageTeam
     * @return Team
     */
    public function setAgeTeam(AgeTeam $ageTeam): Team
    {
        $this->ageTeam = $ageTeam;
        return $this;
    }

    /**
     * @return PrfTeam
     */
    public function getPrfTeam(): PrfTeam
    {
        return $this->prfTeam;
    }

    /**
     * @param PrfTeam $prfTeam
     * @return Team
     */
    public function setPrfTeam(PrfTeam $prfTeam): Team
    {
        $this->prfTeam = $prfTeam;
        return $this;
    }

    /**
     * @return TeamClass
     */
    public function getTeamClass(): TeamClass
    {
        return $this->teamClass;
    }

    /**
     * @param TeamClass $teamClass
     * @return Team
     */
    public function setTeamClass(TeamClass $teamClass): Team
    {
        $this->teamClass = $teamClass;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getPerson(): Collection
    {
        return $this->person;
    }

    /**
     * @param Collection $person
     * @return Team
     */
    public function setPerson(Collection $person): Team
    {
        $this->person = $person;
        return $this;
    }

}
