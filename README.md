# Booking Engine — System Rezerwacji Zasobów

MVP systemu rezerwacji apartamentów z zabezpieczeniem przed double-bookingiem i race conditions.


## Stack

| Warstwa | Technologie |
|---|---|
| Backend | PHP 8.4, Laravel 13, MySQL |
| Frontend | Nuxt 4, Tailwind CSS v4, Composition API |
| Testy | PHPUnit 12 (feature tests) |
| Środowisko | Docker / Laravel Sail |

---

## Wymagania

- Docker Desktop
- Node.js `>=22.12.0` (zalecane: `22.16.0` via nvm)

---

## Uruchomienie

### 1. Klonowanie repozytorium

```bash
git clone <repo-url>
cd booking
```

### 2. Backend (Laravel + Sail)

```bash
cd src/api

# Skopiuj zmienne środowiskowe
cp .env.example .env

# Zainstaluj zależności PHP (bootstrap bez Dockera)
docker run --rm -v "$(pwd):/app" -w /app composer:2 install --no-interaction

# Uruchom kontenery
vendor/bin/sail up -d

# Wygeneruj klucz aplikacji, uruchom migracje i seed
vendor/bin/sail artisan key:generate
vendor/bin/sail artisan migrate
vendor/bin/sail artisan db:seed
```

API dostępne pod: **http://localhost/api/v1**

### 3. Frontend (Nuxt)

```bash
cd src/web

# Ustaw wymaganą wersję Node (jeśli używasz nvm)
nvm use

# Zainstaluj zależności
npm install

# Tryb deweloperski
npm run dev
```

Frontend dostępny pod: **http://localhost:3000**

> Nuxt automatycznie proxy-uje `/api` → `http://localhost/api` — CORS nie wymaga dodatkowej konfiguracji.

---

## Testy

```bash
cd src/api

# Tylko testy rezerwacji
vendor/bin/sail artisan test --compact tests/Feature/Booking/

# Pełny suite
vendor/bin/sail artisan test --compact
```

### Pokrycie testów

| Plik | Zakres |
|---|---|
| `BookingStoreTest` | Poprawna rezerwacja (201), walidacja pól, nieistniejący zasób, błędna kolejność dat |
| `BookingOverlapTest` | Start w trakcie, koniec w trakcie, pełne pokrycie, rezerwacja wewnętrzna, sąsiadujące (2×), inny zasób |
| `BookingConcurrencyTest` | 5 równoczesnych prób na ten sam slot → dokładnie 1 rekord w bazie |

---

## Endpointy API

### `GET /api/v1/resources`

Zwraca listę dostępnych zasobów.

```json
{
  "data": [
    { "id": 1, "name": "Apartament 101", "description": "...", "capacity": 4 }
  ]
}
```

### `POST /api/v1/bookings`

Tworzy rezerwację.

**Body:**
```json
{
  "resource_id": 1,
  "start_at": "2026-06-01 14:00:00",
  "end_at": "2026-06-05 12:00:00",
  "customer_name": "Jan Kowalski"
}
```

**Odpowiedzi:**
- `201` — rezerwacja utworzona
- `422` — błąd walidacji lub konflikt terminu (`"message": "The selected time slot is already booked."`)

---

## Architektura

### Backend

```
app/
├── Http/
│   ├── Controllers/Api/V1/
│   │   ├── BookingController.php   # POST /bookings
│   │   └── ResourceController.php  # GET /resources
│   ├── Requests/StoreBookingRequest.php
│   └── Resources/
│       ├── BookingResource.php
│       └── ResourceResource.php
├── Models/
│   ├── Booking.php
│   └── Resource.php
├── Services/
│   └── BookingService.php          # logika biznesowa + lockForUpdate
└── Exceptions/
    └── BookingConflictException.php
```

### Obsługa race conditions

`BookingService::book()` wykonuje sprawdzenie konfliktu i INSERT wewnątrz `DB::transaction()` z pesymistycznym lockiem `lockForUpdate()`. Gwarantuje to, że przy wielu równoczesnych żądaniach tylko jedno przejdzie przez sekcję krytyczną — pozostałe otrzymają `BookingConflictException` (HTTP 422).

```php
DB::transaction(function () use ($data) {
    $conflict = Booking::where('resource_id', $data['resource_id'])
        ->where('start_at', '<', $data['end_at'])
        ->where('end_at', '>', $data['start_at'])
        ->lockForUpdate()   // blokuje wiersze na czas transakcji
        ->exists();

    if ($conflict) {
        throw new BookingConflictException();
    }

    return Booking::create($data);
});
```

### Frontend

```
src/web/app/
├── pages/index.vue
├── components/booking/
│   ├── BookingForm.vue      # formularz z obsługą błędów inline
│   └── BookingResult.vue   # ekran potwierdzenia
├── composables/useBooking.ts
└── types/booking.ts
```

---

## Zatrzymanie środowiska

```bash
cd src/api && vendor/bin/sail stop
```
