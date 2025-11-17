<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Random\RandomException;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
class Participant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $wishlist = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $exclusions = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToOne(mappedBy: 'giver', cascade: ['persist', 'remove'])]
    private ?Draw $draw = null;

    #[ORM\OneToOne(mappedBy: 'receiver', cascade: ['persist', 'remove'])]
    private ?Draw $drawnBy = null;

    #[ORM\Column(length: 64)]
    private ?string $eventAccessToken = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $accessTokenExpireAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

        return $this;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getWishlist(): ?string
    {
        return $this->wishlist;
    }

    public function setWishlist(?string $wishlist): static
    {
        $this->wishlist = $wishlist;

        return $this;
    }

    public function getExclusions(): ?array
    {
        return $this->exclusions;
    }

    public function setExclusions(?array $exclusions): static
    {
        $this->exclusions = $exclusions;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDraw(): ?Draw
    {
        return $this->draw;
    }

    public function setDraw(Draw $draw): static
    {
        // set the owning side of the relation if necessary
        if ($draw->getGiver() !== $this) {
            $draw->setGiver($this);
        }

        $this->draw = $draw;

        return $this;
    }

    public function getEventAccessToken(): ?string
    {
        return $this->eventAccessToken;
    }

    public function setEventAccessToken(string $eventAccessToken): static
    {
        $this->eventAccessToken = $eventAccessToken;

        return $this;
    }

    public function getAccessTokenExpireAt(): ?\DateTimeImmutable
    {
        return $this->accessTokenExpireAt;
    }

    public function setAccessTokenExpireAt(\DateTimeImmutable $accessTokenExpireAt): static
    {
        $this->accessTokenExpireAt = $accessTokenExpireAt;

        return $this;
    }

    /**
     * @throws RandomException
     */
    public function generateEventAccessToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function getDrawnBy(): ?Draw
    {
        return $this->drawnBy;
    }

    public function setDrawnBy(?Draw $drawnBy): void
    {
        $this->drawnBy = $drawnBy;
    }
}
