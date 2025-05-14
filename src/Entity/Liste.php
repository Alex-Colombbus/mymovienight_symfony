<?php

namespace App\Entity;

use App\Repository\ListeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ListeRepository::class)]
class Liste
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'listes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $name_liste = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    /**
     * @var Collection<int, ListFilm>
     */
    #[ORM\OneToMany(targetEntity: ListFilm::class, mappedBy: 'liste', orphanRemoval: true)]
    private Collection $listFilms;

    public function __construct()
    {
        $this->listFilms = new ArrayCollection();
        $this->created_at = new \DateTimeImmutable();
    }
    public function __toString(): string
    {
        return $this->name_liste ?? 'Liste sans nom'; // Retourne le nom de la liste ou un texte par défaut
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getNameListe(): ?string
    {
        return $this->name_liste;
    }

    public function setNameListe(string $name_liste): static
    {
        $this->name_liste = $name_liste;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
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
            $listFilm->setListe($this);
        }

        return $this;
    }

    public function removeListFilm(ListFilm $listFilm): static
    {
        if ($this->listFilms->removeElement($listFilm)) {
            // set the owning side to null (unless already changed)
            if ($listFilm->getListe() === $this) {
                $listFilm->setListe(null);
            }
        }

        return $this;
    }
}
