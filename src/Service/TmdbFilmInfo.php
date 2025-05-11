<?php

namespace App\Service;

use App\Repository\FilmFiltreRepository;
use App\Repository\FilmRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface; // For logging issues

class TmdbFilmInfo
{
    private HttpClientInterface $client;
    private FilmFiltreRepository $filmFiltreRepository;
    private FilmRepository $filmRepository;
    private string $token;
    private LoggerInterface $logger; // Inject logger

    private ?string $tconst = null;
    private ?int $id = null;
    private ?string $title = null;
    private ?string $duration = null;
    private ?string $synopsis = null;
    private ?string $posterPath = null;
    private ?bool $isAdult = null;
    private ?string $genres = null; // Default to empty array
    private ?string $releaseDate = null;
    private ?float $imdbRating = null;
    private ?float $tmdbRating = null;
    private ?array $actors = []; // Default to empty array
    private ?array $importantCrew = []; // Default to empty array
    private bool $isValid = false; // Flag to indicate if data was successfully fetched

    public function __construct(
        HttpClientInterface $client,
        string $tokenKey,
        FilmFiltreRepository $filmFiltreRepository,
        FilmRepository $filmRepository,
        LoggerInterface $logger // Add logger
    ) {
        $this->client = $client;
        $this->token = $tokenKey;
        $this->filmRepository = $filmRepository;
        $this->filmFiltreRepository = $filmFiltreRepository;
        $this->logger = $logger; // Store logger
    }

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

    public function setFilmInfos(): void
    {
        // Reset state for reuse
        $this->resetProperties();
        $this->isValid = false;

        if (!$this->tconst) {
            $this->logger->warning('TmdbFilmInfo: tconst not set before calling setFilmInfos.');
            return;
        }

        try {
            $response = $this->client->request('GET', 'https://api.themoviedb.org/3/find/' . $this->tconst . '?external_source=imdb_id&language=fr', [ // Removed append_to_response=credits here for simplicity, will get credits in separate call
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                    'accept' => 'application/json',
                ],
            ]);

            $data = $response->toArray();

            if (!isset($data['movie_results']) || empty($data['movie_results']) || !isset($data['movie_results'][0])) {
                $this->logger->warning('TmdbFilmInfo: No movie_results found for tconst: ' . $this->tconst, ['api_response' => $data]);
                return; // Exit if no movie found
            }

            $mainFilmInfo = $data['movie_results'][0];

            $this->id = $mainFilmInfo['id'] ?? null;
            if (!$this->id) {
                $this->logger->warning('TmdbFilmInfo: Movie found but no ID for tconst: ' . $this->tconst, ['mainFilmInfo' => $mainFilmInfo]);
                return; // Exit if no TMDB ID
            }

            $this->title = $mainFilmInfo['title'] ?? null;

            if ($mainFilmInfo['overview'] == "") {

                $response2 = $this->client->request('GET', 'https://api.themoviedb.org/3/find/' . $this->tconst . '?external_source=imdb_id&language=en', [ // Removed append_to_response=credits here for simplicity, will get credits in separate call
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
            if (!empty($mainFilmInfo['release_date'])) {
                try {
                    $this->releaseDate = substr($mainFilmInfo['release_date'], 0, 4);
                } catch (\Exception $e) {
                    $this->logger->error('TmdbFilmInfo: Could not parse release_date: ' . $mainFilmInfo['release_date'], ['exception' => $e]);
                    $this->releaseDate = null;
                }
            }
            $this->tmdbRating = isset($mainFilmInfo['vote_average']) ? (float)$mainFilmInfo['vote_average'] : null;

            // Fetch from local BDD
            $filmBDD = $this->filmRepository->find($this->tconst);
            if ($filmBDD) {
                $minutes = $filmBDD->getRuntimeMinutes();
                $hours = floor($minutes / 60); // Nombre d'heures
                $remainingMinutes = $minutes % 60;
                $this->duration = ($hours > 0 ? $hours . 'h' . $remainingMinutes : $minutes);
            } else {
                $this->logger->info('TmdbFilmInfo: Film not found in local FilmRepository for tconst: ' . $this->tconst);
            }

            $filmFiltreBDD = $this->filmFiltreRepository->find($this->tconst);
            if ($filmFiltreBDD) {
                $this->genres = $filmFiltreBDD->getGenres(); // Assuming this returns an array
                $this->imdbRating = $filmFiltreBDD->getAverageRating();
            } else {
                $this->logger->info('TmdbFilmInfo: Film not found in local FilmFiltreRepository for tconst: ' . $this->tconst);
            }


            // Fetch credits
            $creditsResponse = $this->client->request('GET', 'https://api.themoviedb.org/3/movie/' . $this->id . '/credits?language=fr-FR', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                    'accept' => 'application/json',
                ],
            ]);

            $creditsData = $creditsResponse->toArray();

            $creditsFilmInfoCast = $creditsData['cast'] ?? [];
            $creditsFilmInfoCast = array_slice($creditsFilmInfoCast, 0, 3);
            foreach ($creditsFilmInfoCast as $castMember) {
                $this->actors[] = $castMember['name'];
            }

            $creditsFilmInfoCrew = $creditsData['crew'] ?? [];
            $importantCrewMembers = array_filter($creditsFilmInfoCrew, function ($value) {
                return isset($value['job']) && in_array($value['job'], ['Director', 'Screenplay', 'Novel', 'Story']);
            });

            $jobsByName = [];
            foreach ($importantCrewMembers as $crewMember) {
                $name = $crewMember['name'];
                $job = $crewMember['job'];
                if (!isset($jobsByName[$name])) {
                    $jobsByName[$name] = [];
                }
                $jobsByName[$name][] = $job;
            }

            //Convertit les jobs de tableau à chaine de caractere
            foreach ($jobsByName as $name => $jobs) {
                $jobsByName[$name] = implode(', ', $jobs); // Join jobs with a comma
            }

            $this->importantCrew = $jobsByName;
            $this->isValid = true; // Mark as valid if we reached here

        } catch (\Symfony\Contracts\HttpClient\Exception\ExceptionInterface $e) {
            $this->logger->error('TmdbFilmInfo: HTTP Client Exception for tconst: ' . $this->tconst, [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('TmdbFilmInfo: Generic Exception for tconst: ' . $this->tconst, [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    // Helper to reset properties, useful if the service instance is reused
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

    public function isValid(): bool
    {
        return $this->isValid;
    }

    // Getters and Setters (keep them as they are, ensure types are nullable where appropriate)

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
}
