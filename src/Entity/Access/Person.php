<?php

namespace App\Entity\Access;

use Doctrine\ORM\Mapping as ORM;

/**
 * Person
 *
 * @ORM\Table(name="person", indexes={@ORM\Index(name="postal_IDX", columns={"postal", "last", "first"}), @ORM\Index(name="first_IDX", columns={"first", "last"}), @ORM\Index(name="fk_person_user", columns={"user_id"}), @ORM\Index(name="last_IDX", columns={"last", "first"}), @ORM\Index(name="country_IDX", columns={"country", "state", "last", "first"})})
 * @ORM\Entity(repositoryClass="App\Repository\Access\PersonRepository")
 */
class Person
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
     * @var string|null
     *
     * @ORM\Column(name="title", type="string", length=4, nullable=true)
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(name="first", type="string", length=20, nullable=true)
     */
    private $first;

    /**
     * @var string|null
     *
     * @ORM\Column(name="middle", type="string", length=20, nullable=true)
     */
    private $middle;

    /**
     * @var string
     *
     * @ORM\Column(name="last", type="string", length=40, nullable=false)
     */
    private $last;

    /**
     * @var string|null
     *
     * @ORM\Column(name="suffix", type="string", length=4, nullable=true)
     */
    private $suffix;

    /**
     * @var string|null
     *
     * @ORM\Column(name="street", type="string", length=80, nullable=true)
     */
    private $street;

    /**
     * @var string|null
     *
     * @ORM\Column(name="address", type="string", length=80, nullable=true)
     */
    private $address;

    /**
     * @var string|null
     *
     * @ORM\Column(name="country", type="string", length=4, nullable=true)
     */
    private $country;

    /**
     * @var string|null
     *
     * @ORM\Column(name="city", type="string", length=40, nullable=true)
     */
    private $city;

    /**
     * @var string|null
     *
     * @ORM\Column(name="state", type="string", length=20, nullable=true)
     */
    private $state;

    /**
     * @var string|null
     *
     * @ORM\Column(name="postal", type="string", length=45, nullable=true)
     */
    private $postal;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=80, nullable=false)
     */
    private $email;

    /**
     * @var string|null
     *
     * @ORM\Column(name="home", type="string", length=16, nullable=true)
     */
    private $home;

    /**
     * @var string|null
     *
     * @ORM\Column(name="work", type="string", length=16, nullable=true)
     */
    private $work;

    /**
     * @var string|null
     *
     * @ORM\Column(name="mobile", type="string", length=16, nullable=true)
     */
    private $mobile;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="agree_terms", type="datetime", nullable=true)
     */
    private $agreeTerms;

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
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     * @return Person
     */
    public function setTitle(?string $title): Person
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFirst(): ?string
    {
        return $this->first;
    }

    /**
     * @param string|null $first
     * @return Person
     */
    public function setFirst(?string $first): Person
    {
        $this->first = $first;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMiddle(): ?string
    {
        return $this->middle;
    }

    /**
     * @param string|null $middle
     * @return Person
     */
    public function setMiddle(?string $middle): Person
    {
        $this->middle = $middle;
        return $this;
    }

    /**
     * @return string
     */
    public function getLast(): string
    {
        return $this->last;
    }

    /**
     * @param string $last
     * @return Person
     */
    public function setLast(string $last): Person
    {
        $this->last = $last;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    /**
     * @param string|null $suffix
     * @return Person
     */
    public function setSuffix(?string $suffix): Person
    {
        $this->suffix = $suffix;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStreet(): ?string
    {
        return $this->street;
    }

    /**
     * @param string|null $street
     * @return Person
     */
    public function setStreet(?string $street): Person
    {
        $this->street = $street;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string|null $address
     * @return Person
     */
    public function setAddress(?string $address): Person
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string|null $country
     * @return Person
     */
    public function setCountry(?string $country): Person
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     * @return Person
     */
    public function setCity(?string $city): Person
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param string|null $state
     * @return Person
     */
    public function setState(?string $state): Person
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPostal(): ?string
    {
        return $this->postal;
    }

    /**
     * @param string|null $postal
     * @return Person
     */
    public function setPostal(?string $postal): Person
    {
        $this->postal = $postal;
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
     * @return Person
     */
    public function setEmail(string $email): Person
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHome(): ?string
    {
        return $this->home;
    }

    /**
     * @param string|null $home
     * @return Person
     */
    public function setHome(?string $home): Person
    {
        $this->home = $home;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getWork(): ?string
    {
        return $this->work;
    }

    /**
     * @param string|null $work
     * @return Person
     */
    public function setWork(?string $work): Person
    {
        $this->work = $work;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    /**
     * @param string|null $mobile
     * @return Person
     */
    public function setMobile(?string $mobile): Person
    {
        $this->mobile = $mobile;
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
     * @return Person
     */
    public function setCreatedAt(?\DateTime $createdAt): Person
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getAgreeTerms(): ?\DateTime
    {
        return $this->agreeTerms;
    }

    /**
     * @param \DateTime|null $agreeTerms
     * @return Person
     */
    public function setAgreeTerms(?\DateTime $agreeTerms): Person
    {
        $this->agreeTerms = $agreeTerms;
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
     * @return Person
     */
    public function setUser(User $user): Person
    {
        $this->user = $user;
        return $this;
    }


}
