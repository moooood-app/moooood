<?php

namespace App\Entity\Awards;

use App\Entity\User;
use App\Repository\Awards\AwardProgressRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[ORM\Entity(repositoryClass: AwardProgressRepository::class)]
#[ORM\Table(name: 'awards_progress')]
#[UniqueConstraint(name: 'unique_award_user', columns: ['award_id', 'user_id'])]
class AwardProgress
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Award $award;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $progress;

    public function getId(): int
    {
        return $this->id;
    }

    public function getAward(): Award
    {
        return $this->award;
    }

    public function setAward(Award $award): static
    {
        $this->award = $award;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getProgress(): int
    {
        return $this->progress;
    }

    public function setProgress(int $progress): static
    {
        $this->progress = $progress;

        return $this;
    }
}
