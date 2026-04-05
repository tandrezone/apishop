# API Quick Reference Guide

## Base URL
```
http://localhost:8000/api
```

## Authentication

All endpoints except `/api/auth/login` and `/api/auth/register` require authentication.

### Header Format
```
Authorization: Bearer <your-jwt-token>
```

---

## Endpoints Summary

### 🔓 Public Endpoints

#### Register User
```http
POST /api/auth/register
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123",
  "name": "John Doe"
}

Response: 201 Created
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "email": "user@example.com",
    "name": "John Doe",
    "role": "user",
    "created_at": "2024-01-01 12:00:00"
  }
}
```

#### Login
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "admin123"
}

Response: 200 OK
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "email": "admin@example.com",
    "name": "Admin User",
    "role": "admin",
    "created_at": "2024-01-01 12:00:00"
  }
}
```

---

### 🔒 Protected Endpoints

#### Users

**List all users** (Admin only)
```http
GET /api/users
Authorization: Bearer <token>

Response: 200 OK
[
  {
    "id": 1,
    "email": "admin@example.com",
    "name": "Admin User",
    "role": "admin",
    "created_at": "2024-01-01 12:00:00"
  },
  ...
]
```

**Get user by ID** (Admin or own profile)
```http
GET /api/users/1
Authorization: Bearer <token>

Response: 200 OK
{
  "id": 1,
  "email": "admin@example.com",
  "name": "Admin User",
  "role": "admin",
  "created_at": "2024-01-01 12:00:00"
}
```

**Create user** (Admin only)
```http
POST /api/users
Authorization: Bearer <token>
Content-Type: application/json

{
  "email": "newuser@example.com",
  "password": "password123",
  "name": "New User",
  "role": "user"
}

Response: 201 Created
```

**Update user** (Admin or own profile)
```http
PUT /api/users/1
Authorization: Bearer <token>
Content-Type: application/json

{
  "name": "Updated Name",
  "email": "updated@example.com"
}

Response: 200 OK
```

**Delete user** (Admin only)
```http
DELETE /api/users/1
Authorization: Bearer <token>

Response: 200 OK
{
  "message": "User deleted successfully"
}
```

---

#### Products

**List all products** (All authenticated users)
```http
GET /api/products
Authorization: Bearer <token>

Response: 200 OK
[
  {
    "id": 1,
    "name": "Laptop",
    "description": "High-performance laptop with 16GB RAM",
    "price": 999.99,
    "stock": 50,
    "created_at": "2024-01-01 12:00:00"
  },
  ...
]
```

**Get product by ID** (All authenticated users)
```http
GET /api/products/1
Authorization: Bearer <token>

Response: 200 OK
{
  "id": 1,
  "name": "Laptop",
  "description": "High-performance laptop with 16GB RAM",
  "price": 999.99,
  "stock": 50,
  "created_at": "2024-01-01 12:00:00"
}
```

**Create product** (Admin only)
```http
POST /api/products
Authorization: Bearer <token>
Content-Type: application/json

{
  "name": "Tablet",
  "description": "10-inch tablet with stylus",
  "price": 499.99,
  "stock": 25
}

Response: 201 Created
```

**Update product** (Admin only)
```http
PUT /api/products/1
Authorization: Bearer <token>
Content-Type: application/json

{
  "name": "Gaming Laptop",
  "price": 1299.99,
  "stock": 45
}

Response: 200 OK
```

**Delete product** (Admin only)
```http
DELETE /api/products/1
Authorization: Bearer <token>

Response: 200 OK
{
  "message": "Product deleted successfully"
}
```

---

#### Orders

**List orders** (Admin: all orders, User: own orders only)
```http
GET /api/orders
Authorization: Bearer <token>

Response: 200 OK
[
  {
    "id": 1,
    "user_id": 2,
    "total_amount": 1199.98,
    "status": "completed",
    "items": [
      {"product_id": 1, "quantity": 1, "price": 999.99},
      {"product_id": 5, "quantity": 4, "price": 49.99}
    ],
    "created_at": "2024-01-01 12:00:00"
  },
  ...
]
```

**Get order by ID** (Admin or order owner)
```http
GET /api/orders/1
Authorization: Bearer <token>

Response: 200 OK
{
  "id": 1,
  "user_id": 2,
  "total_amount": 1199.98,
  "status": "completed",
  "items": [...],
  "created_at": "2024-01-01 12:00:00"
}
```

**Create order** (All authenticated users)
```http
POST /api/orders
Authorization: Bearer <token>
Content-Type: application/json

{
  "total_amount": 699.99,
  "status": "pending",
  "items": [
    {
      "product_id": 2,
      "quantity": 1,
      "price": 699.99
    }
  ]
}

Response: 201 Created
```

**Update order** (Admin or order owner)
```http
PUT /api/orders/1
Authorization: Bearer <token>
Content-Type: application/json

{
  "status": "processing"
}

Response: 200 OK
```

**Delete order** (Admin only)
```http
DELETE /api/orders/1
Authorization: Bearer <token>

Response: 200 OK
{
  "message": "Order deleted successfully"
}
```

---

## Error Responses

### 400 Bad Request
```json
{
  "error": "Bad Request",
  "message": "Missing required fields: email, password, name"
}
```

### 401 Unauthorized
```json
{
  "error": "Unauthorized",
  "message": "Invalid or expired token"
}
```

### 403 Forbidden
```json
{
  "error": "Forbidden",
  "message": "You do not have permission to perform this action"
}
```

### 404 Not Found
```json
{
  "error": "Not Found",
  "message": "Resource not found"
}
```

### 500 Internal Server Error
```json
{
  "error": "Server Error",
  "message": "Detailed error message"
}
```

---

## Testing with cURL

### 1. Login and get token
```bash
TOKEN=$(curl -s -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"admin123"}' | jq -r '.token')

echo $TOKEN
```

### 2. Use token to access protected endpoint
```bash
curl -X GET http://localhost:8000/api/products \
  -H "Authorization: Bearer $TOKEN"
```

### 3. Create a new order
```bash
curl -X POST http://localhost:8000/api/orders \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "total_amount": 699.99,
    "items": [{"product_id": 2, "quantity": 1, "price": 699.99}]
  }'
```

---

## Permission Matrix

| Endpoint | Method | Admin | User |
|----------|--------|-------|------|
| `/api/auth/register` | POST | ✅ | ✅ |
| `/api/auth/login` | POST | ✅ | ✅ |
| `/api/users` | GET | ✅ | ❌ |
| `/api/users/{id}` | GET | ✅ | ✅ (own) |
| `/api/users` | POST | ✅ | ❌ |
| `/api/users/{id}` | PUT | ✅ | ✅ (own) |
| `/api/users/{id}` | DELETE | ✅ | ❌ |
| `/api/products` | GET | ✅ | ✅ |
| `/api/products/{id}` | GET | ✅ | ✅ |
| `/api/products` | POST | ✅ | ❌ |
| `/api/products/{id}` | PUT | ✅ | ❌ |
| `/api/products/{id}` | DELETE | ✅ | ❌ |
| `/api/orders` | GET | ✅ (all) | ✅ (own) |
| `/api/orders/{id}` | GET | ✅ | ✅ (own) |
| `/api/orders` | POST | ✅ | ✅ |
| `/api/orders/{id}` | PUT | ✅ | ✅ (own) |
| `/api/orders/{id}` | DELETE | ✅ | ❌ |

---

## Sample Data

### Default Users (from database.sql)

**Admin User:**
- Email: `admin@example.com`
- Password: `admin123`
- Role: `admin`

**Regular User:**
- Email: `user@example.com`
- Password: `user123`
- Role: `user`

### Sample Products
1. Laptop - $999.99
2. Smartphone - $699.99
3. Headphones - $199.99
4. Keyboard - $129.99
5. Mouse - $49.99

---

## Notes

- All timestamps are in format: `YYYY-MM-DD HH:MM:SS`
- JWT tokens expire after 3600 seconds (1 hour) by default
- User role is automatically set to 'user' during registration
- Order items are stored as JSON array
- Decimal prices have 2 decimal places
- Users can only see/modify their own resources (except admins)
