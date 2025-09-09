<?php

namespace App\Entity;

use App\Enum\DrawStatus;
use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Mime\Address;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Assert\NotBlank]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive]
    private ?float $budget = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $drawn_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(length: 255)]
    #[Assert\Type(Address::class)]
    private ?string $adminEmail = null;

    #[ORM\Column(enumType: DrawStatus::class)]
    private ?DrawStatus $status = null;

    /**
     * @var Collection<int, Participant>
     */
    #[ORM\OneToMany(targetEntity: Participant::class, mappedBy: 'event', orphanRemoval: true)]
    private Collection $participants;

    /**
     * @var Collection<int, Draw>
     */
    #[ORM\OneToMany(targetEntity: Draw::class, mappedBy: 'event', orphanRemoval: true)]
    private Collection $draws;

    #[ORM\Column(length: 64, unique: true)]
    private ?string $adminAccessToken = null;

    #[ORM\Column(length: 16, unique: true)]
    private ?string $publicJoinToken = null;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->draws = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getBudget(): ?float
    {
        return $this->budget;
    }

    public function setBudget(?float $budget): static
    {
        $this->budget = $budget;

        return $this;
    }

    public function getDrawnAt(): ?\DateTimeImmutable
    {
        return $this->drawn_at;
    }

    public function setDrawnAt(?\DateTimeImmutable $drawn_at): static
    {
        $this->drawn_at = $drawn_at;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getAdminEmail(): ?string
    {
        return $this->adminEmail;
    }

    public function setAdminEmail(string $adminEmail): static
    {
        $this->adminEmail = $adminEmail;

        return $this;
    }

    public function getStatus(): ?DrawStatus
    {
        return $this->status;
    }

    public function setStatus(DrawStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Participant>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Participant $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
            $participant->setEvent($this);
        }

        return $this;
    }

    public function removeParticipant(Participant $participant): static
    {
        if ($this->participants->removeElement($participant)) {
            // set the owning side to null (unless already changed)
            if ($participant->getEvent() === $this) {
                $participant->setEvent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Draw>
     */
    public function getDraws(): Collection
    {
        return $this->draws;
    }

    public function addDraw(Draw $draw): static
    {
        if (!$this->draws->contains($draw)) {
            $this->draws->add($draw);
            $draw->setEvent($this);
        }

        return $this;
    }

    public function removeDraw(Draw $draw): static
    {
        if ($this->draws->removeElement($draw)) {
            // set the owning side to null (unless already changed)
            if ($draw->getEvent() === $this) {
                $draw->setEvent(null);
            }
        }

        return $this;
    }

    public function getAdminAccessToken(): ?string
    {
        return $this->adminAccessToken;
    }

    public function setAdminAccessToken(string $adminAccessToken): static
    {
        $this->adminAccessToken = $adminAccessToken;

        return $this;
    }

    public function getPublicJoinToken(): ?string
    {
        return $this->publicJoinToken;
    }

    public function setPublicJoinToken(string $publicJoinToken): static
    {
        $this->publicJoinToken = $publicJoinToken;

        return $this;
    }
}
