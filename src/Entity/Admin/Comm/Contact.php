<?php

namespace App\Entity\Admin\Comm;

use App\Entity\Admin\Security\Employed;
//use App\Entity\Gestapp\Property;
use App\Repository\Admin\Comm\ContactRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\Column(length: 14, nullable: true)]
    private ?string $phoneHome = null;

    #[ORM\Column(length: 14, nullable: true)]
    private ?string $phoneGsm = null;

    #[ORM\Column]
    private ?bool $isRGPD = false;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\Column(length: 10)]
    private ?string $contactBy = null;

    #[ORM\ManyToOne(inversedBy: 'contacts')]
    private ?Employed $forEmployed = null;

    #[ORM\ManyToOne(inversedBy: 'fromEmployeds')]
    private ?Employed $FromEmployed = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $fromApp = null;

    //#[ORM\ManyToOne(inversedBy: 'contacts')]
    //private ?Property $property = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getPhoneHome(): ?string
    {
        return $this->phoneHome;
    }

    public function setPhoneHome(?string $phoneHome): self
    {
        $this->phoneHome = $phoneHome;

        return $this;
    }

    public function getPhoneGsm(): ?string
    {
        return $this->phoneGsm;
    }

    public function setPhoneGsm(?string $phoneGsm): self
    {
        $this->phoneGsm = $phoneGsm;

        return $this;
    }

    public function isIsRGPD(): ?bool
    {
        return $this->isRGPD;
    }

    public function setIsRGPD(bool $isRGPD): self
    {
        $this->isRGPD = $isRGPD;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): self
    {
        $this->createdAt = new \DateTime('now');

        return $this;
    }

    public function getContactBy(): ?string
    {
        return $this->contactBy;
    }

    public function setContactBy(string $contactBy): self
    {
        $this->contactBy = $contactBy;

        return $this;
    }

    public function getForEmployed(): ?Employed
    {
        return $this->forEmployed;
    }

    public function setForEmployed(?Employed $forEmployed): self
    {
        $this->forEmployed = $forEmployed;

        return $this;
    }

    public function getFromEmployed(): ?Employed
    {
        return $this->FromEmployed;
    }

    public function setFromEmployed(?Employed $FromEmployed): static
    {
        $this->FromEmployed = $FromEmployed;

        return $this;
    }

    public function getFromApp(): ?string
    {
        return $this->fromApp;
    }

    public function setFromApp(?string $fromApp): static
    {
        $this->fromApp = $fromApp;

        return $this;
    }

    //public function getProperty(): ?Property
    //{
    //    return $this->property;
    //}

    //public function setProperty(?Property $property): self
    //{
    //    $this->property = $property;
    //
    //    return $this;
    //}
}
