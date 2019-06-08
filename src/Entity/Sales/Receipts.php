<?php

namespace App\Entity\Sales;

use Doctrine\ORM\Mapping as ORM;

/**
 * Receipts
 *
 * @ORM\Table(name="receipts", indexes={@ORM\Index(name="fk_receipts_workarea1_idx", columns={"workarea_id"}), @ORM\Index(name="fk_receipts_processor1_idx", columns={"processor_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Sales\ReceiptsRepository")
 */
class Receipts
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
     * @ORM\Column(name="name", type="string", length=80, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="amount", type="decimal", precision=7, scale=2, nullable=false)
     */
    private $amount;

    /**
     * @var json
     *
     * @ORM\Column(name="data", type="json", nullable=false)
     */
    private $data;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var \App\Entity\Sales\Processor
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Sales\Processor")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="processor_id", referencedColumnName="id")
     * })
     */
    private $processor;

    /**
     * @var \App\Entity\Sales\Workarea
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Sales\Workarea")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="workarea_id", referencedColumnName="id")
     * })
     */
    private $workarea;

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
     * @return Receipts
     */
    public function setName(string $name): Receipts
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getAmount(): string
    {
        return $this->amount;
    }

    /**
     * @param string $amount
     * @return Receipts
     */
    public function setAmount(string $amount): Receipts
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return json
     */
    public function getData(): json
    {
        return $this->data;
    }

    /**
     * @param json $data
     * @return Receipts
     */
    public function setData(json $data): Receipts
    {
        $this->data = $data;
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
     * @return Receipts
     */
    public function setCreatedAt(?\DateTime $createdAt): Receipts
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return Processor
     */
    public function getProcessor(): Processor
    {
        return $this->processor;
    }

    /**
     * @param Processor $processor
     * @return Receipts
     */
    public function setProcessor(Processor $processor): Receipts
    {
        $this->processor = $processor;
        return $this;
    }

    /**
     * @return Workarea
     */
    public function getWorkarea(): Workarea
    {
        return $this->workarea;
    }

    /**
     * @param Workarea $workarea
     * @return Receipts
     */
    public function setWorkarea(Workarea $workarea): Receipts
    {
        $this->workarea = $workarea;
        return $this;
    }




}
