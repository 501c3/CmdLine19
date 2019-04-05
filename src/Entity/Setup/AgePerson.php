<?php

namespace App\Entity\Setup;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use /** @noinspection PhpUnusedAliasInspection */
    Doctrine\ORM\Mapping as ORM;

/**
 * AgePerson
 *
 * @ORM\Table(name="age_person")
 * @ORM\Entity(repositoryClass="App\Repository\Setup\AgePersonRepository")
 */
class AgePerson
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
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\PrfPerson", inversedBy="agePerson")
     * @ORM\JoinTable(name="age_person_has_prf_person",
     *   joinColumns={
     *     @ORM\JoinColumn(name="age_person_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="prf_person_id", referencedColumnName="id")
     *   }
     * )
     */
    private $prfPerson;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\Value", inversedBy="agePerson")
     * @ORM\JoinTable(name="age_person_has_value",
     *   joinColumns={
     *     @ORM\JoinColumn(name="age_person_id", referencedColumnName="id")
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
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\AgeTeam", mappedBy="agePerson")
     */
    private $ageTeam;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->prfPerson = new ArrayCollection();
        $this->value = new ArrayCollection();
        $this->ageTeam = new ArrayCollection();
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
     * @return AgePerson
     */
    public function setId(int $id): AgePerson
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
     * @return AgePerson
     */
    public function setDescribe(array $describe): AgePerson
    {
        $this->describe = $describe;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getPrfPerson(): Collection
    {
        return $this->prfPerson;
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
    public function getAgeTeam(): Collection
    {
        return $this->ageTeam;
    }

}
