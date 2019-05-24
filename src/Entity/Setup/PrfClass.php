<?php

namespace App\Entity\Setup;

use Doctrine\ORM\Mapping as ORM;

/**
 * PrfClass
 *
 * @ORM\Table(name="prf_class", indexes={@ORM\Index(name="idx_name", columns={"name"})})
 * @ORM\Entity(repositoryClass="App\Repository\Setup\PrfClassRepository")
 */
class PrfClass
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=false)
     */
    private $name;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\AgeClass", inversedBy="prfClass")
     * @ORM\JoinTable(name="prf_class_has_age_class",
     *   joinColumns={
     *     @ORM\JoinColumn(name="prf_class_id", referencedColumnName="id")
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
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\Tss", inversedBy="prfClass")
     * @ORM\JoinTable(name="prf_class_has_tss",
     *   joinColumns={
     *     @ORM\JoinColumn(name="prf_class_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="tss_id", referencedColumnName="id")
     *   }
     * )
     */
    private $tss;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\PrfTeam", mappedBy="prfClass")
     */
    private $prfTeam;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ageClass = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tss = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return PrfClass
     */
    public function setName(string $name): PrfClass
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param Tss $tss
     * @return PrfClass
     */
    public function addTss(Tss $tss): PrfClass
    {
        $id = $tss->getId();
        if(!$this->tss->containsKey($id)) {
            $this->tss->set($id,$tss);
        }
        return $this;
    }

    /**
     * @param AgeClass $ageClass
     * @return PrfClass
     */
    public function addAgeClass(AgeClass $ageClass) : PrfClass
    {
        $id = $ageClass->getId();
        if(!$this->ageClass->containsKey($id)) {
            $this->ageClass->set($id,$ageClass);
        }
        return $this;
    }
}
