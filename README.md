# Translation Management API

This Laravel API provides endpoints to manage translations, locales, and tags.

## Endpoints

| Method | Endpoint | Description |
|--------|-----------|--------------|
| GET | `/api/translations/search?q=term` | Search translations by key/content |
| GET | `/api/translations/{id}` | Show single translation |
| POST | `/api/translations` | Create translation |
| PUT | `/api/translations/{id}` | Update translation |
| DELETE | `/api/translations/{id}` | Delete translation |

