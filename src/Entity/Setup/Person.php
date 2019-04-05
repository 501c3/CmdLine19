<?php

namespace App\Entity\Setup;

use /** @noinspection PhpUnusedAliasInspection */
    Doctrine\ORM\Mapping as ORM;

/**
 * Person
 *
 * @ORM\Table(name="person", indexes={@ORM\Index(name="fk_person_prf_person1_idx", columns={"prf_person_id"}), @ORM\Index(name="fk_person_age_person1_idx", columns={"age_person_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Setup\PersonRepository")
 */
class Person
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
     * @ORM\Column(name="`describe`", type="json", nullable=false)
     */
    private $describe;

    /**
     * @var \App\Entity\Setup\AgePerson
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Setup\AgePerson")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="age_person_id", referencedColumnName="id")
     * })
     */
    private $agePerson;

    /**
     * @var \App\Entity\Setup\PrfPerson
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Setup\PrfPerson")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="prf_person_id", referencedColumnName="id")
     * })
     */
    private $prfPerson;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\Team", mappedBy="person")
     */
    private $team;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->team = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Person
     */
    public function setId(int $id): Person
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
     * @return Person
     */
    public function setDescribe(array $describe): Person
    {
        $this->describe = $describe;
        return $this;
    }

    /**
     * @return AgePerson
     */
    public function getAgePerson(): AgePerson
    {
        return $this->agePerson;
    }

    /**
     * @param AgePerson $agePerson
     * @return Person
     */
    public function setAgePerson(AgePerson $agePerson): Person
    {
        $this->agePerson = $agePerson;
        return $this;
    }

    /**
     * @return PrfPerson
     */
    public function getPrfPerson(): PrfPerson
    {
        return $this->prfPerson;
    }

    /**
     * @param PrfPerson $prfPerson
     * @return Person
     */
    public function setPrfPerson(PrfPerson $prfPerson): Person
    {
        $this->prfPerson = $prfPerson;
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTeam(): \Doctrine\Common\Collections\Collection
    {
        return $this->team;
    }



}
