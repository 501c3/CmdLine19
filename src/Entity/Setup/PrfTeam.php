<?php

namespace App\Entity\Setup;

use Doctrine\ORM\Mapping as ORM;

/**
 * PrfTeam
 *
 * @ORM\Table(name="prf_team")
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
     * @var array
     *
     * @ORM\Column(name="proficiencies", type="json", nullable=false)
     */
    private $proficiencies;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\PrfClass", inversedBy="prfTeam")
     * @ORM\JoinTable(name="prf_team_has_prf_class",
     *   joinColumns={
     *     @ORM\JoinColumn(name="prf_team_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="prf_class_id", referencedColumnName="id")
     *   }
     * )
     */
    private $prfClass;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\Tss", inversedBy="prfTeam")
     * @ORM\JoinTable(name="prf_team_has_tss",
     *   joinColumns={
     *     @ORM\JoinColumn(name="prf_team_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="tss_id", referencedColumnName="id")
     *   }
     * )
     */
    private $tss;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->prfClass = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tss = new \Doctrine\Common\Collections\ArrayCollection();
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
    public function getProficiencies(): array
    {
        return $this->proficiencies;
    }

    /**
     * @param array $proficiencies
     * @return PrfTeam
     */
    public function setProficiencies(array $proficiencies): PrfTeam
    {
        $this->proficiencies = $proficiencies;
        return $this;
    }

    public function addPrfClass(PrfClass $prfClass)
    {
        $id=$prfClass->getId();
        if(!$this->prfClass->containsKey($id)) {
            $this->prfClass->set($id,$prfClass);
        }
        return $this;
    }

    public function addTss(Tss $tss)
    {
        $id = $tss->getId();
        if(!$this->tss->containsKey($id)) {
            $this->tss->set($id,$tss);
        }
        return $this;
    }
}
