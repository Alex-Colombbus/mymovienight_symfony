<?php

namespace App\Entity;

use App\Repository\RatingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RatingRepository::class)]
class Rating
{

    #[ORM\Id]
    #[ORM\Column(length: 15)]
    private ?string $tconst = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 1, nullable: true)]
    private ?string $averageRating = null;

    #[ORM\Column(nullable: true)]
    private ?int $numVotes = null;


    public function getTconst(): ?string
    {
        return $this->tconst;
    }

    public function setTconst(string $tconst): static
    {
        $this->tconst = $tconst;

        return $this;
    }

    public function getAverageRating(): ?string
    {
        return $this->averageRating;
    }

    public function setAverageRating(?string $averageRating): static
    {
        $this->averageRating = $averageRating;

        return $this;
    }

    public function getNumVotes(): ?int
    {
        return $this->numVotes;
    }

    public function setNumVotes(?int $numVotes): static
    {
        $this->numVotes = $numVotes;

        return $this;
    }
}
