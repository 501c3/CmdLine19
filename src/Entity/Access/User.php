<?php

namespace App\Entity\Access;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="App\Repository\Access\UserRepository")
 */
class User implements UserInterface
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
     * @ORM\Column(name="username", type="string", length=80, nullable=false)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=180, nullable=false)
     */
    private $password;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     */
    private $enabled = '0';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="expire_at", type="datetime", nullable=true)
     */
    private $expireAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var string|null
     *
     * @ORM\Column(name="authenticator", type="string", length=20, nullable=true)
     */
    private $authenticator;

    /**
     * @var string|null
     *
     * @ORM\Column(name="authenticator_id", type="string", length=80, nullable=true)
     */
    private $authenticatorId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="confirm_token", type="string", length=120, nullable=true)
     */
    private $confirmToken;

    /**
     * @var string|null
     *
     * @ORM\Column(name="access_token", type="string", length=120, nullable=true)
     */
    private $accessToken;

    /**
     * @var string|null
     *
     * @ORM\Column(name="refresh_token", type="string", length=120, nullable=true)
     */
    private $refreshToken;

    /**
     * @var array
     *
     * @ORM\Column(name="roles", type="json", nullable=true)
     */
    private $roles;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Access\Channel", inversedBy="user")
     * @ORM\JoinTable(name="user_has_channel",
     *   joinColumns={
     *     @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="channel_id", referencedColumnName="id")
     *   }
     * )
     */
    private $channel;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Access\Controller", inversedBy="user")
     * @ORM\JoinTable(name="user_has_controller",
     *   joinColumns={
     *     @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="controller_id", referencedColumnName="id")
     *   }
     * )
     */
    private $controller;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Access\Workarea", inversedBy="user")
     * @ORM\JoinTable(name="user_has_workarea",
     *   joinColumns={
     *     @ORM\JoinColumn(name="user_id", referencedColumnName="id")
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
        $this->channel = new \Doctrine\Common\Collections\ArrayCollection();
        $this->controller = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return User
     */
    public function setUsername(string $username): User
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return User
     */
    public function setPassword(string $password): User
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return User
     */
    public function setEnabled(bool $enabled): User
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getExpireAt(): ?\DateTime
    {
        return $this->expireAt;
    }

    /**
     * @param \DateTime|null $expireAt
     * @return User
     */
    public function setExpireAt(?\DateTime $expireAt): User
    {
        $this->expireAt = $expireAt;
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
     * @return User
     */
    public function setCreatedAt(?\DateTime $createdAt): User
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAuthenticator(): ?string
    {
        return $this->authenticator;
    }

    /**
     * @param string|null $authenticator
     * @return User
     */
    public function setAuthenticator(?string $authenticator): User
    {
        $this->authenticator = $authenticator;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAuthenticatorId(): ?string
    {
        return $this->authenticatorId;
    }

    /**
     * @param string|null $authenticatorId
     * @return User
     */
    public function setAuthenticatorId(?string $authenticatorId): User
    {
        $this->authenticatorId = $authenticatorId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getConfirmToken(): ?string
    {
        return $this->confirmToken;
    }

    /**
     * @param string|null $confirmToken
     * @return User
     */
    public function setConfirmToken(?string $confirmToken): User
    {
        $this->confirmToken = $confirmToken;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * @param string|null $accessToken
     * @return User
     */
    public function setAccessToken(?string $accessToken): User
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    /**
     * @param string|null $refreshToken
     * @return User
     */
    public function setRefreshToken(?string $refreshToken): User
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     * @return User
     */
    public function setRoles(array $roles): User
    {
        $this->roles = $roles;
        return $this;
    }

    public function getSalt()
    {
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChannel(): \Doctrine\Common\Collections\Collection
    {
        return $this->channel;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $channel
     * @return User
     */
    public function setChannel(\Doctrine\Common\Collections\Collection $channel): User
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getController(): \Doctrine\Common\Collections\Collection
    {
        return $this->controller;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $controller
     * @return User
     */
    public function setController(\Doctrine\Common\Collections\Collection $controller): User
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getWorkarea(): \Doctrine\Common\Collections\Collection
    {
        return $this->workarea;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $workarea
     * @return User
     */
    public function setWorkarea(\Doctrine\Common\Collections\Collection $workarea): User
    {
        $this->workarea = $workarea;
        return $this;
    }




     public function __toString()
     {
         return $this->username;
     }
}
