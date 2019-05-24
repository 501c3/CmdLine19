<?php

namespace App\Entity\Setup;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * TeamClass
 *
 * @ORM\Table(name="team_class")
 * @ORM\Entity(repositoryClass="App\Repository\Setup\TeamClassRepository")
 */
class TeamClass
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
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\Event", mappedBy="teamClass")
     */
    private $event;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->event = new ArrayCollection();
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
     * @return TeamClass
     */
    public function setDescribe(array $describe): TeamClass
    {
        $this->describe = $describe;
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
     * @return TeamClass
     */
    public function setEvent(\Doctrine\Common\Collections\Collection $event): TeamClass
    {
        $this->event = $event;
        return $this;
    }



}
