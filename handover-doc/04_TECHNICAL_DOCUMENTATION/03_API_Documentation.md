# Dokumentasi API

## Overview
[Jika aplikasi memiliki API endpoints]

## Base URL
```
Production: https://[your-domain.com]/api
Development: http://localhost/api
```

## Authentication
**Method**: [Bearer Token/Session/etc]

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

## Endpoints

### Authentication

#### POST /api/login
Login ke sistem.

**Request**:
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response (200)**:
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "User Name",
      "email": "user@example.com"
    },
    "token": "eyJ0eXAiOiJKV1..."
  }
}
```

**Response (401)**:
```json
{
  "success": false,
  "message": "Kredensial tidak valid"
}
```

---

#### POST /api/logout
Logout dari sistem.

**Headers**: Authorization required

**Response (200)**:
```json
{
  "success": true,
  "message": "Berhasil logout"
}
```

---

### [Resource Name]

#### GET /api/[resource]
[Deskripsi endpoint]

#### POST /api/[resource]
[Deskripsi endpoint]

#### PUT /api/[resource]/{id}
[Deskripsi endpoint]

#### DELETE /api/[resource]/{id}
[Deskripsi endpoint]

---

## Error Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Internal Server Error |

## Rate Limiting
[Jika ada rate limiting]

## Pagination
[Format pagination response]

## Versioning
[API versioning strategy jika ada]

## Testing
Untuk testing API, gunakan:
- Postman Collection: [Link]
- API Tests: `tests/Feature/Api/`


