<?php

namespace App\Entity\Access;

use Doctrine\ORM\Mapping as ORM;

/**
 * Sessions
 *
 * @ORM\Table(name="sessions", indexes={@ORM\Index(name="fk_sessions_user1_idx", columns={"user_id"})})
 * @ORM\Entity
 */
class Sessions
{
    /**
     * @var binary
     *
     * @ORM\Column(name="sess_id", type="binary", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $sessId;

    /**
     * @var string
     *
     * @ORM\Column(name="sess_data", type="blob", length=65535, nullable=false)
     */
    private $sessData;

    /**
     * @var int
     *
     * @ORM\Column(name="sess_time", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $sessTime;

    /**
     * @var int
     *
     * @ORM\Column(name="sess_lifetime", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $sessLifetime;

    /**
     * @var \App\Entity\Access\User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Access\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * @return binary
     */
    public function getSessId(): binary
    {
        return $this->sessId;
    }

    /**
     * @param binary $sessId
     * @return Sessions
     */
    public function setSessId(binary $sessId): Sessions
    {
        $this->sessId = $sessId;
        return $this;
    }

    /**
     * @return string
     */
    public function getSessData(): string
    {
        return $this->sessData;
    }

    /**
     * @param string $sessData
     * @return Sessions
     */
    public function setSessData(string $sessData): Sessions
    {
        $this->sessData = $sessData;
        return $this;
    }

    /**
     * @return int
     */
    public function getSessTime(): int
    {
        return $this->sessTime;
    }

    /**
     * @param int $sessTime
     * @return Sessions
     */
    public function setSessTime(int $sessTime): Sessions
    {
        $this->sessTime = $sessTime;
        return $this;
    }

    /**
     * @return int
     */
    public function getSessLifetime(): int
    {
        return $this->sessLifetime;
    }

    /**
     * @param int $sessLifetime
     * @return Sessions
     */
    public function setSessLifetime(int $sessLifetime): Sessions
    {
        $this->sessLifetime = $sessLifetime;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return Sessions
     */
    public function setUser(User $user): Sessions
    {
        $this->user = $user;
        return $this;
    }

    public function __string()
    {
        return $this->sessId;
    }


}
