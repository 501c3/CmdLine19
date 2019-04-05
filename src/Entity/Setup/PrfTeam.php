<?php

namespace App\Entity\Setup;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use /** @noinspection PhpUnusedAliasInspection */
    Doctrine\ORM\Mapping as ORM;

/**
 * PrfTeam
 *
 * @ORM\Table(name="prf_team", indexes={@ORM\Index(name="fk_prf_team_prf_team_class1_idx", columns={"prf_team_class_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Setup\PrfTeamRepository")
 */
class PrfTeam
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
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\AgeTeam", mappedBy="prfTeam")
     */
    private $ageTeam;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\PrfPerson", inversedBy="prfTeam")
     * @ORM\JoinTable(name="prf_team_has_prf_person",
     *   joinColumns={
     *     @ORM\JoinColumn(name="prf_team_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="prf_person_id", referencedColumnName="id")
     *   }
     * )
     */
    private $prfPerson;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ageTeam = new ArrayCollection();
        $this->prfPerson = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
     * @return PrfTeam
     */
    public function setPrfTeamClass(PrfTeamClass $prfTeamClass): PrfTeam
    {
        $this->prfTeamClass = $prfTeamClass;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getAgeTeam(): Collection
    {
        return $this->ageTeam;
    }


    /**
     * @return Collection
     */
    public function getPrfPerson(): Collection
    {
        return $this->prfPerson;
    }


}
