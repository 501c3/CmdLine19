<?php

namespace App\Entity\Setup;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Value
 *
 * @ORM\Table(name="value", indexes={@ORM\Index(name="fk_value_domain1_idx", columns={"domain_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Setup\ValueRepository")
 */
class Value
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="abbr", type="string", length=6, nullable=false)
     */
    private $abbr;

    /**
     * @var \App\Entity\Setup\Domain
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Setup\Domain")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="domain_id", referencedColumnName="id")
     * })
     */
    private $domain;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\Event", mappedBy="value")
     */
    private $event;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\Model", mappedBy="value")
     */
    private $model;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\Person", mappedBy="value")
     */
    private $person;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->event = new ArrayCollection();
        $this->model = new ArrayCollection();
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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Value
     */
    public function setName(string $name): Value
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getAbbr(): string
    {
        return $this->abbr;
    }

    /**
     * @param string $abbr
     * @return Value
     */
    public function setAbbr(string $abbr): Value
    {
        $this->abbr = $abbr;
        return $this;
    }

    /**
     * @return Domain
     */
    public function getDomain(): Domain
    {
        return $this->domain;
    }

    /**
     * @param Domain $domain
     * @return Value
     */
    public function setDomain(Domain $domain): Value
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEvent(): \Doctrine\Common\Collections\Collection
    {
        return $this->event;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $event
     * @return Value
     */
    public function setEvent(\Doctrine\Common\Collections\Collection $event): Value
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getModel(): \Doctrine\Common\Collections\Collection
    {
        return $this->model;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $model
     * @return Value
     */
    public function setModel(\Doctrine\Common\Collections\Collection $model): Value
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPerson(): \Doctrine\Common\Collections\Collection
    {
        return $this->person;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $person
     * @return Value
     */
    public function setPerson(\Doctrine\Common\Collections\Collection $person): Value
    {
        $this->person = $person;
        return $this;
    }



}
