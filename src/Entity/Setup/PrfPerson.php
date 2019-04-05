<?php

namespace App\Entity\Setup;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use /** @noinspection PhpUnusedAliasInspection */
    Doctrine\ORM\Mapping as ORM;

/**
 * PrfPerson
 *
 * @ORM\Table(name="prf_person")
 * @ORM\Entity(repositoryClass="App\Repository\Setup\PrfPersonRepository")
 */
class PrfPerson
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
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\AgePerson", mappedBy="prfPerson")
     */
    private $agePerson;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\Value", inversedBy="prfPerson")
     * @ORM\JoinTable(name="prf_person_has_value",
     *   joinColumns={
     *     @ORM\JoinColumn(name="prf_person_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="value_id", referencedColumnName="id")
     *   }
     * )
     */
    private $value;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\PrfTeam", mappedBy="prfPerson")
     */
    private $prfTeam;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->agePerson = new ArrayCollection();
        $this->value = new ArrayCollection();
        $this->prfTeam = new ArrayCollection();
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
     * @return PrfPerson
     */
    public function setDescribe(array $describe): PrfPerson
    {
        $this->describe = $describe;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getAgePerson(): Collection
    {
        return $this->agePerson;
    }


    /**
     * @return Collection
     */
    public function getValue(): Collection
    {
        return $this->value;
    }


    /**
     * @return Collection
     */
    public function getPrfTeam(): Collection
    {
        return $this->prfTeam;
    }

}
