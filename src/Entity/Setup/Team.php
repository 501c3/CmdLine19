<?php

namespace App\Entity\Setup;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Team
 *
 * @ORM\Table(name="team", indexes={@ORM\Index(name="fk_team_team_class1_idx", columns={"team_class_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Setup\TeamRepository")
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
     * @var array
     *
     * @ORM\Column(name="persons", type="json", nullable=false)
     */
    private $persons;

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
     * @return array
     */
    public function getPersons(): array
    {
        return $this->persons;
    }

    /**
     * @param array $persons
     * @return Team
     */
    public function setPersons(array $persons): Team
    {
        $this->persons = $persons;
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
