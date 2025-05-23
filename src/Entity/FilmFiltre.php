<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\FilmFiltreRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: FilmFiltreRepository::class)]
#[ORM\Table(name: 'film_filtre')]
#[ORM\Index(name: 'idx_title', columns: ['title'])]
#[ORM\Index(name: 'idx_start_year', columns: ['start_year'])]
#[ORM\Index(name: 'idx_average_rating', columns: ['average_rating'])]
#[ORM\Index(name: 'idx_num_votes', columns: ['num_votes'])]
#[ORM\Index(name: 'idx_title_type', columns: ['title_type'])]
// Vous pouvez aussi créer des index composites si vous filtrez souvent sur plusieurs colonnes en même temps
// #[ORM\Index(name: 'idx_year_rating', columns: ['start_year', 'average_rating'])]
class FilmFiltre
{
    #[ORM\Id]
    #[ORM\Column(length: 15, unique: true)]
    private ?string $tconst = null;

    #[ORM\Column(length: 30)]
    private ?string $titleType = null;

    #[ORM\Column(length: 499, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $synopsis = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $importantCrew = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $actors = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $posterPath = null;

    #[ORM\Column]
    private ?bool $isAdult = null;

    #[ORM\Column(nullable: true)]
    private ?int $startYear = null;

    #[ORM\Column(nullable: true)]
    private ?int $runtimeMinutes = null;


    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 1, nullable: true)]
    private ?string $averageRating = null;

    #[ORM\Column(nullable: true)]
    private ?float $tmdbRating = null;

    #[ORM\Column(nullable: true)]
    private ?int $numVotes = null;

    #[ORM\OneToMany(targetEntity: ListFilm::class, mappedBy: 'tconst', orphanRemoval: true)]
    private Collection $listFilms;
    #[ORM\ManyToMany(targetEntity: Genre::class, inversedBy: 'filmFiltres')]
    #[ORM\JoinTable(name: 'film_genre')] // Nom de la table de jointure
    #[ORM\JoinColumn(name: 'film_tconst', referencedColumnName: 'tconst')] // Clé étrangère vers FilmFiltre
    #[ORM\InverseJoinColumn(name: 'genre_id', referencedColumnName: 'id')] // Clé étrangère vers Genre
    private Collection $genresCollection; // Nouveau nom pour éviter la confusion, ou gardez 'genres'

    public function __construct()
    {
        $this->listFilms = new ArrayCollection();
        $this->genresCollection = new ArrayCollection();
    }

    // ... autres getters/setters ...

    /**
     * @return Collection<int, Genre>
     */
    public function getGenresCollection(): Collection
    {
        return $this->genresCollection;
    }

    public function setGenresCollection(Collection $genresCollection): static
    {
        $this->genresCollection = $genresCollection;
        return $this;
    }

    public function addGenre(Genre $genre): static
    {
        if (!$this->genresCollection->contains($genre)) {
            $this->genresCollection->add($genre);
            $genre->addFilmFiltre($this);
        }
        return $this;
    }


    public function removeGenre(Genre $genre): static
    {
        if ($this->genresCollection->removeElement($genre)) {
            $genre->removeFilmFiltre($this);
        }
        return $this;
    }
    // Supprimez les anciens getGenres/setGenres pour la chaîne de caractères




    public function __toString(): string
    {
        return $this->title ?? 'Film sans titre'; // Retourne le titre du film ou un texte par défaut
    }

    public function getGenresAsString(): ?string
    {
        if ($this->genresCollection->isEmpty()) {
            return null;
        }

        $genreNames = [];
        foreach ($this->genresCollection as $genre) {
            $genreNames[] = $genre->getName();
        }

        return implode(', ', $genreNames);
    }

    /**
     * @return Collection<int, ListFilm>
     */
    public function getListFilms(): Collection
    {
        return $this->listFilms;
    }

    public function addListFilm(ListFilm $listFilm): static
    {
        if (!$this->listFilms->contains($listFilm)) {
            $this->listFilms->add($listFilm);
            $listFilm->setTconst($this);
        }

        return $this;
    }

    public function removeListFilm(ListFilm $listFilm): static
    {
        if ($this->listFilms->removeElement($listFilm)) {
            // set the owning side to null (unless already changed)
            if ($listFilm->getTconst() === $this) {
                $listFilm->setTconst(null);
            }
        }

        return $this;
    }





    public function getTconst(): ?string
    {
        return $this->tconst;
    }

    public function setTconst(string $tconst): static
    {
        $this->tconst = $tconst;

        return $this;
    }





    public function getTitleType(): ?string
    {
        return $this->titleType;
    }

    public function setTitleType(string $titleType): static
    {
        $this->titleType = $titleType;

        return $this;
    }

    public function isAdult(): ?bool
    {
        return $this->isAdult;
    }

    public function setIsAdult(bool $isAdult): static
    {
        $this->isAdult = $isAdult;

        return $this;
    }

    public function getStartYear(): ?int
    {
        return $this->startYear;
    }

    public function setStartYear(int $startYear): static
    {
        $this->startYear = $startYear;

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

    public function getSynopsis(): ?string
    {
        return $this->synopsis;
    }

    public function setSynopsis(?string $synopsis): static
    {
        $this->synopsis = $synopsis;

        return $this;
    }

    public function getImportantCrew(): ?array
    {
        return $this->importantCrew;
    }

    public function setImportantCrew(?array $importantCrew): static
    {
        $this->importantCrew = $importantCrew;

        return $this;
    }

    public function getActors(): ?string
    {
        return $this->actors;
    }

    public function setActors(?string $actors): static
    {
        $this->actors = $actors;

        return $this;
    }

    public function getPosterPath(): ?string
    {
        return $this->posterPath;
    }

    public function setPosterPath(?string $posterPath): static
    {
        $this->posterPath = $posterPath;

        return $this;
    }

    public function getTmdbRating(): ?float
    {
        return $this->tmdbRating;
    }

    public function setTmdbRating(?float $tmdbRating): static
    {
        $this->tmdbRating = $tmdbRating;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getRuntimeMinutes(): ?int
    {
        return $this->runtimeMinutes;
    }

    public function setRuntimeMinutes(?int $runtimeMinutes): static
    {
        $this->runtimeMinutes = $runtimeMinutes;

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
