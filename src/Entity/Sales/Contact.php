<?php

namespace App\Entity\Sales;

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
     * @var json|null
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
     * @var \Doctrine\Common\Collections\Collection
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

}
