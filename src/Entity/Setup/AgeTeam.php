<?php

namespace App\Entity\Setup;

use Doctrine\ORM\Mapping as ORM;

/**
 * AgeTeam
 *
 * @ORM\Table(name="age_team")
 * @ORM\Entity(repositoryClass="App\Repository\Setup\AgeTeamRepository")
 */
class AgeTeam
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
     * @ORM\Column(name="years", type="json", nullable=false)
     */
    private $years;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\AgeClass", inversedBy="ageTeam")
     * @ORM\JoinTable(name="age_team_has_age_class",
     *   joinColumns={
     *     @ORM\JoinColumn(name="age_team_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="age_class_id", referencedColumnName="id")
     *   }
     * )
     */
    private $ageClass;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\Tss", inversedBy="ageTeam")
     * @ORM\JoinTable(name="age_team_has_tss",
     *   joinColumns={
     *     @ORM\JoinColumn(name="age_team_id", referencedColumnName="id")
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
        $this->ageClass = new \Doctrine\Common\Collections\ArrayCollection();
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
    public function getYears(): array
    {
        return $this->years;
    }

    /**
     * @param array $years
     * @return AgeTeam
     */
    public function setYears(array $years): AgeTeam
    {
        $this->years = $years;
        return $this;
    }

    /**
     * @param AgeClass $ageClass
     * @return AgeTeam
     */
    public function addAgeClass(AgeClass $ageClass): AgeTeam
    {
        $id=$ageClass->getId();
        if(!$this->ageClass->containsKey($id)) {
            $this->ageClass->set($id,$ageClass);
        }
        return $this;
    }

    /**
     * @param Tss $tss
     * @return AgeTeam
     */
    public function addTss(Tss $tss): AgeTeam
    {
        $id = $tss->getId();
        if(!$this->tss->containsKey($id))
        {
            $this->tss->set($id,$tss);
        }
        return $this;
    }
}
