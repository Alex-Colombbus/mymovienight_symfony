<?php

namespace App\Entity;

use App\Repository\GenreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GenreRepository::class)]
class Genre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $nom = null;

    #[ORM\ManyToMany(targetEntity: FilmFiltre::class, mappedBy: 'genres')]
    private Collection $filmFiltres;

    public function __construct()
    {
        $this->filmFiltres = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection<int, FilmFiltre>
     */
    public function getFilmFiltres(): Collection
    {
        return $this->filmFiltres;
    }

    public function addFilmFiltre(FilmFiltre $filmFiltre): static
    {
        if (!$this->filmFiltres->contains($filmFiltre)) {
            $this->filmFiltres->add($filmFiltre);
        }

        return $this;
    }

    public function removeFilmFiltre(FilmFiltre $filmFiltre): static
    {
        $this->filmFiltres->removeElement($filmFiltre);

        return $this;
    }
}
