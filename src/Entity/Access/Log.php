<?php

namespace App\Entity\Access;

use Doctrine\ORM\Mapping as ORM;

/**
 * Log
 *
 * @ORM\Table(name="log", indexes={@ORM\Index(name="IDX_activity_action", columns={"action"}), @ORM\Index(name="IDX_activity_controller", columns={"controller"}), @ORM\Index(name="IDX_activity_ip", columns={"ip"}), @ORM\Index(name="fk_log_user1_idx", columns={"user_id"})})
 * @ORM\Entity
 */
class Log
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="application", type="string", length=40, nullable=false)
     */
    private $application;

    /**
     * @var string
     *
     * @ORM\Column(name="bundle", type="string", length=40, nullable=false)
     */
    private $bundle;

    /**
     * @var string
     *
     * @ORM\Column(name="controller", type="string", length=40, nullable=false)
     */
    private $controller;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="string", length=40, nullable=false)
     */
    private $action;

    /**
     * @var string
     *
     * @ORM\Column(name="sid", type="string", length=120, nullable=false, options={"comment"="Session identifier"})
     */
    private $sid;

    /**
     * @var string
     *
     * @ORM\Column(name="ip", type="string", length=20, nullable=false, options={"comment"="Either IPv4 or IPv6"})
     */
    private $ip;

    /**
     * @var string
     *
     * @ORM\Column(name="referrer", type="string", length=255, nullable=false)
     */
    private $referrer;

    /**
     * @var string|null
     *
     * @ORM\Column(name="info", type="text", length=255, nullable=true)
     */
    private $info;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

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
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Log
     */
    public function setId(int $id): Log
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getApplication(): string
    {
        return $this->application;
    }

    /**
     * @param string $application
     * @return Log
     */
    public function setApplication(string $application): Log
    {
        $this->application = $application;
        return $this;
    }

    /**
     * @return string
     */
    public function getBundle(): string
    {
        return $this->bundle;
    }

    /**
     * @param string $bundle
     * @return Log
     */
    public function setBundle(string $bundle): Log
    {
        $this->bundle = $bundle;
        return $this;
    }

    /**
     * @return string
     */
    public function getController(): string
    {
        return $this->controller;
    }

    /**
     * @param string $controller
     * @return Log
     */
    public function setController(string $controller): Log
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     * @return Log
     */
    public function setAction(string $action): Log
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @return string
     */
    public function getSid(): string
    {
        return $this->sid;
    }

    /**
     * @param string $sid
     * @return Log
     */
    public function setSid(string $sid): Log
    {
        $this->sid = $sid;
        return $this;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     * @return Log
     */
    public function setIp(string $ip): Log
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * @return string
     */
    public function getReferrer(): string
    {
        return $this->referrer;
    }

    /**
     * @param string $referrer
     * @return Log
     */
    public function setReferrer(string $referrer): Log
    {
        $this->referrer = $referrer;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getInfo(): ?string
    {
        return $this->info;
    }

    /**
     * @param string|null $info
     * @return Log
     */
    public function setInfo(?string $info): Log
    {
        $this->info = $info;
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
     * @return Log
     */
    public function setCreatedAt(\DateTime $createdAt): Log
    {
        $this->createdAt = $createdAt;
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
     * @return Log
     */
    public function setUser(User $user): Log
    {
        $this->user = $user;
        return $this;
    }




}
