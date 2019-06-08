<?php

namespace App\Entity\Sales;

use Doctrine\ORM\Mapping as ORM;

/**
 * Processor
 *
 * @ORM\Table(name="processor", indexes={@ORM\Index(name="fk_processor_channel1_idx", columns={"channel_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Sales\ProcessorRepository")
 */
class Processor
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
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=true)
     */
    private $name;

    /**
     * @var json|null
     *
     * @ORM\Column(name="live", type="json", nullable=true)
     */
    private $live;

    /**
     * @var array|null
     *
     * @ORM\Column(name="test", type="json", nullable=true)
     */
    private $test;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="is_live", type="boolean", nullable=true)
     */
    private $isLive;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var \App\Entity\Sales\Channel
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Sales\Channel")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="channel_id", referencedColumnName="id")
     * })
     */
    private $channel;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
     * @return Processor
     */
    public function setName(?string $name): Processor
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return json|null
     */
    public function getLive(): ?json
    {
        return $this->live;
    }

    /**
     * @param json|null $live
     * @return Processor
     */
    public function setLive(?json $live): Processor
    {
        $this->live = $live;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getTest(): ?array
    {
        return $this->test;
    }

    /**
     * @param array|null $test
     * @return Processor
     */
    public function setTest(?array $test): Processor
    {
        $this->test = $test;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getisLive(): ?bool
    {
        return $this->isLive;
    }

    /**
     * @param bool|null $isLive
     * @return Processor
     */
    public function setIsLive(?bool $isLive): Processor
    {
        $this->isLive = $isLive;
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
     * @return Processor
     */
    public function setCreatedAt(?\DateTime $createdAt): Processor
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return Channel
     */
    public function getChannel(): Channel
    {
        return $this->channel;
    }

    /**
     * @param Channel $channel
     * @return Processor
     */
    public function setChannel(Channel $channel): Processor
    {
        $this->channel = $channel;
        return $this;
    }



}
