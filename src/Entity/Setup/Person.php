<?php

namespace App\Entity\Setup;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Person
 *
 * @ORM\Table(name="person", indexes={@ORM\Index(name="idx_years", columns={"years"})})
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
     * @var int
     *
     * @ORM\Column(name="years", type="smallint", nullable=false)
     */
    private $years;

    /**
     * @var array
     *
     * @ORM\Column(name="`describe`", type="json", nullable=false)
     */
    private $describe;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\Value", inversedBy="person")
     * @ORM\JoinTable(name="person_has_value",
     *   joinColumns={
     *     @ORM\JoinColumn(name="person_id", referencedColumnName="id")
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
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\Team", mappedBy="person")
     */
    private $team;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->value = new ArrayCollection();
        $this->team = new ArrayCollection();
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
     * @return int
     */
    public function getYears(): int
    {
        return $this->years;
    }

    /**
     * @param int $years
     * @return Person
     */
    public function setYears(int $years): Person
    {
        $this->years = $years;
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
     * @return Collection
     */
    public function getValue(): Collection
    {
        return $this->value;
    }

    /**
     * @param Collection $value
     * @return Person
     */
    public function setValue(Collection $value): Person
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getTeam(): Collection
    {
        return $this->team;
    }

    /**
     * @param Collection $team
     * @return Person
     */
    public function setTeam(Collection $team): Person
    {
        $this->team = $team;
        return $this;
    }


}
