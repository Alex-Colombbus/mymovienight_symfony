<?php

namespace App\Service;

use App\Repository\FilmFiltreRepository;
use App\Repository\FilmRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface; // Pour la journalisation

class TmdbFilmInfo
{
    // Déclaration des propriétés privées pour stocker les dépendances et les informations du film
    private HttpClientInterface $client;
    private FilmFiltreRepository $filmFiltreRepository;
    private string $token;
    private LoggerInterface $logger;

    // Propriétés pour stocker les informations du film
    private ?string $tconst = null;
    private ?int $id = null;
    private ?string $title = null;
    private ?string $duration = null;
    private ?string $synopsis = null;
    private ?string $posterPath = null;
    private ?bool $isAdult = null;
    private ?string $genres = null;
    private ?string $releaseDate = null;
    private ?float $imdbRating = null;
    private ?float $tmdbRating = null;
    private ?array $actors = [];
    private ?array $importantCrew = [];
    private bool $isValid = false; // Indique si les données ont été récupérées avec succès

    // Constructeur : injection des dépendances (client HTTP, token, repository, logger)
    public function __construct(
        HttpClientInterface $client,
        string $tokenKey,
        FilmFiltreRepository $filmFiltreRepository,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->token = $tokenKey;
        $this->filmFiltreRepository = $filmFiltreRepository;
        $this->logger = $logger;
    }

    // Sérialise les informations du film sous forme de tableau
    public function serialize(): array
    {
        return [
            'tconst' => $this->tconst,
            'id' => $this->id,
            'title' => $this->title,
            'duration' => $this->duration,
            'synopsis' => $this->synopsis,
            'posterPath' => $this->posterPath,
            'isAdult' => $this->isAdult,
            'genres' => $this->genres,
            'releaseDate' => $this->releaseDate,
            'imdbRating' => $this->imdbRating,
            'tmdbRating' => $this->tmdbRating,
            'actors' => $this->actors,
            'importantCrew' => $this->importantCrew
        ];
    }

    // Récupère et remplit les informations du film à partir de l'API TMDB et de la base locale
    public function setFilmInfos(): void
    {
        // Réinitialise les propriétés pour une éventuelle réutilisation
        $this->resetProperties();
        $this->isValid = false;

        // Vérifie que le tconst est bien défini
        if (!$this->tconst) {
            $this->logger->warning('TmdbFilmInfo: tconst non défini avant appel à setFilmInfos.');
            return;
        }

        try {
            // Appel à l'API TMDB pour récupérer les infos du film via l'identifiant IMDB
            $response = $this->client->request('GET', 'https://api.themoviedb.org/3/find/' . $this->tconst . '?external_source=imdb_id&language=fr', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                    'accept' => 'application/json',
                ],
            ]);

            $data = $response->toArray();

            // Vérifie que le film a bien été trouvé
            if (!isset($data['movie_results']) || empty($data['movie_results']) || !isset($data['movie_results'][0])) {
                $this->logger->warning('TmdbFilmInfo: Aucun résultat trouvé pour tconst: ' . $this->tconst, ['api_response' => $data]);
                return;
            }

            $mainFilmInfo = $data['movie_results'][0];

            $this->id = $mainFilmInfo['id'] ?? null;
            if (!$this->id) {
                $this->logger->warning('TmdbFilmInfo: Film trouvé mais pas d\'ID pour tconst: ' . $this->tconst, ['mainFilmInfo' => $mainFilmInfo]);
                return;
            }

            $this->title = $mainFilmInfo['title'] ?? null;

            // Si le synopsis en français est vide, on tente de le récupérer en anglais
            if ($mainFilmInfo['overview'] == "") {
                $response2 = $this->client->request('GET', 'https://api.themoviedb.org/3/find/' . $this->tconst . '?external_source=imdb_id&language=en', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->token,
                        'accept' => 'application/json',
                    ],
                ]);
                $data2 = $response2->toArray();
                $mainFilmInfo2 = $data2['movie_results'][0];
                $this->synopsis = $mainFilmInfo2['overview'];
            } else {
                $this->synopsis = $mainFilmInfo['overview'] ?? null;
            }

            $this->posterPath = $mainFilmInfo['poster_path'] ?? null;
            $this->isAdult = $mainFilmInfo['adult'] ?? null;

            // Récupère l'année de sortie
            if (!empty($mainFilmInfo['release_date'])) {
                try {
                    $this->releaseDate = substr($mainFilmInfo['release_date'], 0, 4);
                } catch (\Exception $e) {
                    $this->logger->error('TmdbFilmInfo: Impossible de parser la date de sortie: ' . $mainFilmInfo['release_date'], ['exception' => $e]);
                    $this->releaseDate = null;
                }
            }
            $this->tmdbRating = isset($mainFilmInfo['vote_average']) ? (float)$mainFilmInfo['vote_average'] : null;

            // Récupère la durée et les genres depuis la base locale si disponible
            $filmBDD = $this->filmFiltreRepository->find($this->tconst);
            if ($filmBDD) {
                $minutes = $filmBDD->getRuntimeMinutes();
                $hours = floor($minutes / 60);
                $remainingMinutes = $minutes % 60;
                $this->duration = ($hours > 0 ? $hours . 'h ' . $remainingMinutes . 'min' : $minutes . 'min');
            } else {
                $this->logger->info('TmdbFilmInfo: Film non trouvé dans la base locale pour tconst: ' . $this->tconst);
            }


            if ($filmBDD) {
                // Récupération des genres via la nouvelle relation ManyToMany
                $genresCollection = $filmBDD->getGenresCollection();
                if (!$genresCollection->isEmpty()) {
                    $genreNames = [];
                    foreach ($genresCollection as $genre) {
                        $genreNames[] = $genre->getName();
                    }
                    $this->genres = implode(', ', $genreNames);
                } else {
                    $this->genres = null;
                }

                $this->imdbRating = $filmBDD->getAverageRating();
            } else {
                $this->logger->info('TmdbFilmInfo: Film non trouvé dans FilmFiltreRepository pour tconst: ' . $this->tconst);
            }

            // Récupère les crédits (acteurs et équipe importante) via l'API TMDB
            $creditsResponse = $this->client->request('GET', 'https://api.themoviedb.org/3/movie/' . $this->id . '/credits?language=fr-FR', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                    'accept' => 'application/json',
                ],
            ]);

            $creditsData = $creditsResponse->toArray();

            // Récupère les 3 premiers acteurs
            $creditsFilmInfoCast = $creditsData['cast'] ?? [];
            $creditsFilmInfoCast = array_slice($creditsFilmInfoCast, 0, 3);
            foreach ($creditsFilmInfoCast as $castMember) {
                $this->actors[] = $castMember['name'];
            }

            // Récupère les membres importants de l'équipe (réalisateur, scénariste, etc.)
            $creditsFilmInfoCrew = $creditsData['crew'] ?? [];
            $importantCrewMembers = array_filter($creditsFilmInfoCrew, function ($value) {
                return isset($value['job']) && in_array($value['job'], ['Director', 'Screenplay', 'Novel', 'Story']);
            });

            $jobsByName = [];
            // Regroupe les jobs par nom
            foreach ($importantCrewMembers as $crewMember) {
                $name = $crewMember['name'];
                $job = $crewMember['job'];
                if (!isset($jobsByName[$name])) {
                    $jobsByName[$name] = [];
                }
                $jobsByName[$name][] = $job;
            }

            // Transforme les jobs en chaîne de caractères
            foreach ($jobsByName as $name => $jobs) {
                $jobsByName[$name] = implode(', ', $jobs);
            }
            // Stocke les membres importants de l'équipe
            $this->importantCrew = $jobsByName;
            $this->isValid = true; // Marque comme valide si tout s'est bien passé

        } catch (\Symfony\Contracts\HttpClient\Exception\ExceptionInterface $e) {
            $this->logger->error('TmdbFilmInfo: Exception HTTP Client pour tconst: ' . $this->tconst, [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('TmdbFilmInfo: Exception générique pour tconst: ' . $this->tconst, [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    // Réinitialise toutes les propriétés du film
    private function resetProperties(): void
    {
        $this->id = null;
        $this->title = null;
        $this->duration = null;
        $this->synopsis = null;
        $this->posterPath = null;
        $this->isAdult = null;
        $this->genres = null;
        $this->releaseDate = null;
        $this->imdbRating = null;
        $this->tmdbRating = null;
        $this->actors = [];
        $this->importantCrew = [];
    }

    // Indique si les informations du film sont valides
    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function getClient(): HttpClientInterface
    {
        return $this->client;
    }

    public function setClient(HttpClientInterface $client): self
    {
        $this->client = $client;
        return $this;
    }

    public function getTconst(): ?string
    {
        return $this->tconst;
    }

    public function setTconst(?string $tconst): self
    {
        $this->tconst = $tconst;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }


    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(?string $duration): self
    {
        $this->duration = $duration;
        return $this;
    }

    public function getSynopsis(): ?string
    {
        return $this->synopsis;
    }

    public function setSynopsis(?string $synopsis): self
    {
        $this->synopsis = $synopsis;
        return $this;
    }

    public function getPosterPath(): ?string
    {
        return $this->posterPath;
    }

    public function setPosterPath(?string $posterPath): self
    {
        $this->posterPath = $posterPath;
        return $this;
    }

    public function getIsAdult(): ?bool
    {
        return $this->isAdult;
    }

    public function setIsAdult(?bool $isAdult): self
    {
        $this->isAdult = $isAdult;
        return $this;
    }

    public function getGenres(): ?string
    {
        return $this->genres;
    }

    public function setGenres(?string $genres): self
    {
        $this->genres = $genres;
        return $this;
    }

    public function getReleaseDate(): ?string // Keep DateTimeInterface
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(?string $releaseDate): self // Keep DateTimeInterface
    {
        $this->releaseDate = $releaseDate;
        return $this;
    }

    public function getImdbRating(): ?float
    {
        return $this->imdbRating;
    }

    public function setImdbRating(?float $imdbRating): self
    {
        $this->imdbRating = $imdbRating;
        return $this;
    }

    public function getTmdbRating(): ?float
    {
        return $this->tmdbRating;
    }

    public function setTmdbRating(?float $tmdbRating): self
    {
        $this->tmdbRating = $tmdbRating;
        return $this;
    }

    public function getActors(): ?array
    {
        return $this->actors;
    }

    public function setActors(?array $actors): self
    {
        $this->actors = $actors;
        return $this;
    }

    public function getImportantCrew(): ?array
    {
        return $this->importantCrew;
    }

    public function setImportantCrew(?array $importantCrew): self
    {
        $this->importantCrew = $importantCrew;
        return $this;
    }
    // Getters et setters pour chaque propriété (permettent d'accéder et de modifier les valeurs)
    // ... (les getters et setters sont déjà bien commentés par leur nom)
}
