# Uruchomienie kontenerów
docker-compose -f docker-compose.yml up -d --build

# Zatrzymanie kontenerów
docker-compose down

# Przeglądanie logów
docker-compose logs -f

# Wejście do kontenera aplikacji
docker-compose exec app bash

# Wyczyszczenie cache Symfony
docker-compose exec app php bin/console cache:clear
