# Movie Recommendation System

Aplikacja do rekomendacji filmów zbudowana w frameworku Symfony 5.4.

# Struktura projektu
├── app/ \
├── bin/ \
├── config/ \
├── data/ # miejsce dla pliku movies.php \
├── public/ \
├── src/ \
│ ├── Controller/ \
│ ├── Service/ \
│ ├── Traits/ \
│ ├── Utility/ \
│ └── Kernel.php \
├── tests/ \
├── docker-compose.yml \
├── Dockerfile \
└── composer.json

# Technologie

- **Framework**: Symfony 5.4.45
- **PHP**: 8.3
- **Testing**: PHPUnit 12
- **Logging**: Monolog
- **Containeryzacja**: Docker

# Instalacja i uruchomienie

## 1. Klonowanie repozytorium

```bash 
git clone https://github.com/Kobietakoala/movie-recommendation 
cd movie-recommendation
```

## 2. Konfiguracja środowiska

Skopiuj plik konfiguracyjny:

```bash
cp .env.example .env
```

## 3. Uruchomienie 
### Docker

```
# Zbudowanie i uruchomienie kontenerów
docker-compose up -d --build

# Instalacja zależności Composer
docker-compose exec app composer install
```

Aplikacja będzie dostępna pod adresem: http://localhost:8000

### Bez Docker
```
composer install
symfony server:start
```

# Testy
```
# W kontenerze Docker
docker-compose exec app ./vendor/bin/phpunit

# Lokalnie
./vendor/bin/phpunit
```

# Dokumentacja api
Dokumentacja znajduje się w pliku api.json i można ją bezproblemowo importować do np. Postman