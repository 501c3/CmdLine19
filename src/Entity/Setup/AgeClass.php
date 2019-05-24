<?php

namespace App\Entity\Setup;

use Doctrine\ORM\Mapping as ORM;

/**
 * AgeClass
 *
 * @ORM\Table(name="age_class", indexes={@ORM\Index(name="idx_name", columns={"name"})})
 * @ORM\Entity(repositoryClass="App\Repository\Setup\AgeClassRepository")
 */
class AgeClass
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
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\Tss", inversedBy="ageClass")
     * @ORM\JoinTable(name="age_class_has_tss",
     *   joinColumns={
     *     @ORM\JoinColumn(name="age_class_id", referencedColumnName="id")
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
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\AgeTeam", mappedBy="ageClass")
     */
    private $ageTeam;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\PrfClass", mappedBy="ageClass")
     */
    private $prfClass;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tss = new \Doctrine\Common\Collections\ArrayCollection();
        $this->ageTeam = new \Doctrine\Common\Collections\ArrayCollection();
        $this->prfClass = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return AgeClass
     */
    public function setId(int $id): AgeClass
    {
        $this->id = $id;
        return $this;
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
     * @return AgeClass
     */
    public function setName(string $name): AgeClass
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param Tss $tss
     * @return AgeClass
     */
    public function addTss(Tss $tss): AgeClass
    {
        $id=$tss->getId();
        if(!$this->tss->containsKey($id)) {
            $this->tss->set($id,$tss);
        }
        return $this;
    }

    /**
     * @param PrfClass $prfClass
     * @return AgeClass
     */
    public function addPrfClass(PrfClass $prfClass): AgeClass
    {
        $id=$prfClass->getId();
        if(!$this->prfClass->containsKey($id)) {
            $this->prfClass->set($id,$prfClass);
        }
        return $this;
    }


}
