# Health Module

Health check module untuk monitoring system status.

## Struktur

```
health/
├── interfaces/
│   └── health.interface.ts    # Service contract
├── types/
│   └── health.types.ts        # Response types
├── services/
│   └── health.service.ts      # Business logic
└── routes/
    └── health.routes.ts       # API routes
```

## Endpoints

### GET /api/health

Health check endpoint untuk database connection.

**Response:**
```json
{
  "success": true,
  "message": "System healthy",
  "data": {
    "status": "ok",
    "database": "connected",
    "timestamp": "2026-07-09T02:59:21.637Z"
  }
}
```
