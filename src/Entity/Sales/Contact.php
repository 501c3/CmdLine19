<?php

namespace App\Entity\Sales;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Contact
 *
 * @ORM\Table(name="contact", indexes={@ORM\Index(name="idx_email", columns={"email"}), @ORM\Index(name="idx_send_elink", columns={"pin"})})
 * @ORM\Entity(repositoryClass="App\Repository\Sales\ContactRepository")
 */
class Contact
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var string|null
     *
     * @ORM\Column(name="elink", type="string", length=120, nullable=true)
     */
    private $elink;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=80, nullable=false)
     */
    private $email;

    /**
     * @var array|null
     *
     * @ORM\Column(name="info", type="json", nullable=true)
     */
    private $info;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=true)
     */
    private $name;

    /**
     * @var int|null
     *
     * @ORM\Column(name="pin", type="integer", nullable=true, options={"unsigned"=true})
     */
    private $pin;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Sales\Workarea", inversedBy="contact")
     * @ORM\JoinTable(name="contact_has_workarea",
     *   joinColumns={
     *     @ORM\JoinColumn(name="contact_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="workarea_id", referencedColumnName="id")
     *   }
     * )
     */
    private $workarea;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->workarea = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Contact
     */
    public function setId(int $id): Contact
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime|null $createdAt
     * @return Contact
     */
    public function setCreatedAt(?\DateTime $createdAt): Contact
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getElink(): ?string
    {
        return $this->elink;
    }

    /**
     * @param string|null $elink
     * @return Contact
     */
    public function setElink(?string $elink): Contact
    {
        $this->elink = $elink;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Contact
     */
    public function setEmail(string $email): Contact
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getInfo(): ?array
    {
        return $this->info;
    }

    /**
     * @param array|null $info
     * @return Contact
     */
    public function setInfo(?array $info): Contact
    {
        $this->info = $info;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return Contact
     */
    public function setName(?string $name): Contact
    {
        $this->name = $name;
        return $this;
    }


    /**
     * @param int|null $pin
     * @return Contact
     */
    public function setPin(?int $pin): Contact
    {
        $this->pin = $pin;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getWorkarea(): Collection
    {
        return $this->workarea;
    }

    /**
     * @param Collection $workarea
     * @return Contact
     */
    public function setWorkarea(Collection $workarea): Contact
    {
        $this->workarea = $workarea;
        return $this;
    }


}
