<?php

namespace App\Entity\Sales;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Picture
 *
 * @ORM\Table(name="picture")
 * @ORM\Entity(repositoryClass="App\Repository\Sales\PictureRepository")
 */
class Picture
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
     * @ORM\Column(name="data", type="blob", length=65535, nullable=false)
     */
    private $data;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Sales\Form", mappedBy="picture")
     */
    private $form;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->form = new ArrayCollection();
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
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @param string $data
     * @return Picture
     */
    public function setData(string $data): Picture
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getForm(): Collection
    {
        return $this->form;
    }

    /**
     * @param Collection $form
     * @return Picture
     */
    public function setForm(Collection $form): Picture
    {
        $this->form = $form;
        return $this;
    }


}
