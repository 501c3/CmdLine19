<?php

namespace App\Entity\Setup;

use Doctrine\Common\Collections\Collection;
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
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Setup\Event", mappedBy="teamClass")
     */
    private $event;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->event = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Collection
     */
    public function getEvent(): Collection
    {
        return $this->event;
    }

    /**
     * @param Collection $event
     * @return TeamClass
     */
    public function setEvent(Collection $event): TeamClass
    {
        $this->event = $event;
        return $this;
    }



}
