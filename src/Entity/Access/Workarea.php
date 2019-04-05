<?php

namespace App\Entity\Access;

use Doctrine\ORM\Mapping as ORM;

/**
 * Workarea
 *
 * @ORM\Table(name="workarea")
 * @ORM\Entity
 */
class Workarea
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
     * @ORM\Column(name="note", type="text", length=255, nullable=false)
     */
    private $note;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Access\User", mappedBy="workarea")
     */
    private $user;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->user = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Workarea
     */
    public function setId(int $id): Workarea
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getNote(): string
    {
        return $this->note;
    }

    /**
     * @param string $note
     * @return Workarea
     */
    public function setNote(string $note): Workarea
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return Workarea
     */
    public function setCreatedAt(\DateTime $createdAt): Workarea
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUser(): \Doctrine\Common\Collections\Collection
    {
        return $this->user;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $user
     * @return Workarea
     */
    public function setUser(\Doctrine\Common\Collections\Collection $user): Workarea
    {
        $this->user = $user;
        return $this;
    }

    public function __toString()
    {
        return (string) $this->id;
    }

}
