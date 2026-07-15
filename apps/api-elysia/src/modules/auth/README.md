# Auth Module

Authentication module untuk HK Gold VIP mobile app dengan JWT stateless authentication.

## Endpoints

### POST /api/auth/register
Register user baru (otomatis membuat User + Member).

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123",
  "fullName": "John Doe",
  "phoneNumber": "081234567890"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Registrasi berhasil",
  "data": {
    "accessToken": "...",
    "refreshToken": "...",
    "user": {
      "id": "uuid",
      "email": "user@example.com",
      "fullName": "John Doe",
      "role": "MEMBER",
      "isActive": true
    },
    "member": {
      "id": "uuid",
      "memberNumber": "2606-0001",
      "phoneNumber": "+6281234567890",
      "currentTier": "SILVER",
      "pointBalance": 0,
      "isSuspended": false
    }
  }
}
```

### POST /api/auth/login
Login dengan email, phone number, atau member number.

**Request Body:**
```json
{
  "identifier": "user@example.com",  // atau "081234567890" / "2606-0001"
  "password": "password123"
}
```

`identifier` diterima dalam tiga bentuk:
- **Email** — mengandung `@` (contoh: `user@example.com`)
- **Nomor HP** — `08xxx` atau `+62xxx` (dinormalisasi ke `+62xxx`)
- **Nomor Member** — contoh `2606-0001`

**Response:** Same as register

### POST /api/auth/change-password
Ubah password (memerlukan JWT token).

**Headers:**
```
Authorization: Bearer <accessToken>
```

**Request Body:**
```json
{
  "oldPassword": "password123",
  "newPassword": "newpassword123"
}
```

### POST /api/auth/refresh
Refresh access token.

**Request Body:**
```json
{
  "refreshToken": "..."
}
```

## Business Rules

1. **Member Number Format:** `YYMM-NNNN` (contoh `2606-0001`)
   - `YY` + `MM` = tahun & bulan daftar
   - `NNNN` = increment 4 digit, **reset ke 0001 tiap bulan baru**
   - Kapasitas: 9999 member per bulan

2. **Phone Validation:**
   - Accept 08xxx or +62xxx
   - Normalized to +62xxx in database
   - Must be valid Indonesian format

3. **Password Requirements:**
   - Minimum 8 characters

4. **JWT Tokens:**
   - Access token: 12 hours (configurable)
   - Refresh token: 30 days (configurable)

5. **User States:**
   - `isActive = false`: Force logout, block all access
   - `isSuspended = true`: Can login, but blocked from financial operations

## Tests

Run tests:
```bash
bun test
bun test:watch
```

**Test Coverage:**
- Register: 6 test cases (1 happy path + 5 edge cases)
- Login: 8 test cases (2 happy paths + 6 edge cases)
- Change Password: 7 test cases (1 happy path + 6 edge cases)

## Environment Variables

```env
JWT_SECRET=your-secret-key
JWT_ACCESS_EXPIRES_IN=12h
JWT_REFRESH_EXPIRES_IN=30d
```
