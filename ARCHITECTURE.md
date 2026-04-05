# API Architecture Documentation

## Overview

This RESTful API follows a three-tier architecture pattern (Controller-Service-Repository) with middleware-based authentication and authorization.

## Architecture Layers

### 1. Presentation Layer (Controllers)

**Location**: `src/Controller/`

Controllers handle HTTP requests and responses. They:
- Parse incoming requests
- Validate request data
- Call appropriate service methods
- Format and return responses

**Example: OrderController**
```php
public function show(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
{
    $id = (int) $args['id'];
    $currentUserId = $request->getAttribute('user_id');
    $currentUserRole = $request->getAttribute('user_role');

    $order = $this->orderService->getOrderById($id);

    // Authorization check
    if ($currentUserRole !== 'admin' && $order->getUserId() !== $currentUserId) {
        // Return 403 Forbidden
    }

    return $response->withJson($order->toArray());
}
```

### 2. Business Logic Layer (Services)

**Location**: `src/Service/`

Services contain business logic and orchestrate data flow between controllers and repositories. They:
- Implement business rules
- Transform data between layers
- Handle complex operations
- Coordinate multiple repository calls

**Example: OrderService**
```php
public function createOrder(array $data): Order
{
    $order = new Order(
        (int) $data['user_id'],
        (float) $data['total_amount'],
        $data['status'] ?? 'pending'
    );

    if (isset($data['items'])) {
        $order->setItems($data['items']);
    }

    return $this->orderRepository->create($order);
}
```

### 3. Data Access Layer (Repositories)

**Location**: `src/Repository/`

Repositories handle all database operations using PDO with prepared statements. They:
- Execute SQL queries
- Map database rows to Entity objects
- Provide CRUD operations
- Abstract database details

**Example: OrderRepository**
```php
public function findById(int $id): ?Order
{
    $stmt = $this->db->prepare('SELECT * FROM orders WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $result = $stmt->fetch();

    return $result ? $this->hydrateOrder($result) : null;
}

private function hydrateOrder(array $data): Order
{
    // Map database row to Order entity
}
```

### 4. Entity/Model Layer

**Location**: `src/Entity/`

Entities represent domain objects with:
- Properties and their types
- Getters and setters
- Business methods (e.g., `isAdmin()`)
- Serialization methods (`toArray()`)

### 5. Middleware Layer

**Location**: `src/Middleware/`

Middleware components process requests before they reach controllers:

#### AuthenticationMiddleware
- Extracts JWT token from Authorization header
- Validates token signature and expiration
- Adds user information to request attributes
- Returns 401 Unauthorized for invalid tokens

#### AuthorizationMiddleware
- Checks role-based permissions
- Implements fine-grained access control:
  - Admin: Full access to all resources
  - User: Limited access based on resource and ownership
- Returns 403 Forbidden for unauthorized actions

## Request Flow

```
1. HTTP Request
   ↓
2. Slim Router
   ↓
3. AuthenticationMiddleware (verify JWT)
   ↓
4. AuthorizationMiddleware (check permissions)
   ↓
5. Controller (parse request)
   ↓
6. Service (business logic)
   ↓
7. Repository (database access)
   ↓
8. Entity (data model)
   ↓
9. Repository (return entity)
   ↓
10. Service (transform data)
   ↓
11. Controller (format response)
   ↓
12. HTTP Response
```

## Authorization Rules Matrix

### Admin Role
| Resource | Create | Read | Update | Delete |
|----------|--------|------|--------|--------|
| Users    | ✅     | ✅   | ✅     | ✅     |
| Products | ✅     | ✅   | ✅     | ✅     |
| Orders   | ✅     | ✅   | ✅     | ✅     |

### User Role
| Resource | Create | Read | Update | Delete | Notes |
|----------|--------|------|--------|--------|-------|
| Users    | ❌     | ✅*  | ✅*    | ❌     | *Own profile only |
| Products | ❌     | ✅   | ❌     | ❌     | Read-only access |
| Orders   | ✅     | ✅*  | ✅*    | ❌     | *Own orders only |

## Database Schema

### Users Table
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at DATETIME NOT NULL,
    updated_at DATETIME DEFAULT NULL
);
```

### Products Table
```sql
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME DEFAULT NULL
);
```

### Orders Table
```sql
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    items JSON NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## JWT Authentication Flow

### 1. Login/Register
```
User credentials → AuthController
   ↓
UserService.authenticate()
   ↓
Password verification
   ↓
JWTService.generateToken()
   ↓
Return token + user data
```

### 2. Token Structure
```json
{
  "iat": 1234567890,
  "exp": 1234571490,
  "user_id": 1,
  "email": "user@example.com",
  "role": "user"
}
```

### 3. Protected Request
```
Request with Authorization: Bearer <token>
   ↓
AuthenticationMiddleware.process()
   ↓
JWTService.validateToken()
   ↓
Add user_id, user_email, user_role to request attributes
   ↓
AuthorizationMiddleware.process()
   ↓
Check permissions based on role and resource
   ↓
Controller handles request
```

## Security Considerations

### 1. SQL Injection Prevention
- All database queries use PDO prepared statements
- User input is never directly concatenated into SQL

### 2. Password Security
- Passwords hashed using `password_hash()` with bcrypt
- Never store or return plain text passwords
- Uses password_verify() for authentication

### 3. JWT Token Security
- Signed with secret key (HS256 algorithm)
- Includes expiration time
- Secret key stored in environment variables
- Token validated on every protected request

### 4. Role-Based Access Control
- Dual-layer authorization (middleware + controller)
- Ownership verification for user-specific resources
- Principle of least privilege

### 5. Input Validation
- Required fields validated in controllers
- Type casting for numeric values
- JSON parsing error handling

## PSR Standards

### PSR-4: Autoloading
```json
{
  "autoload": {
    "psr-4": {
      "App\\": "src/"
    }
  }
}
```

### PSR-7: HTTP Messages
- Using `Psr\Http\Message\ServerRequestInterface`
- Using `Psr\Http\Message\ResponseInterface`
- Immutable request/response objects

### PSR-12: Coding Style
- Declared strict types: `declare(strict_types=1);`
- Proper indentation and spacing
- Type declarations for all parameters and returns
- Consistent naming conventions

### PSR-15: HTTP Middleware
```php
interface MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface;
}
```

## Error Handling

### HTTP Status Codes
- **200 OK**: Successful GET, PUT requests
- **201 Created**: Successful POST requests
- **400 Bad Request**: Invalid input data
- **401 Unauthorized**: Missing or invalid token
- **403 Forbidden**: Insufficient permissions
- **404 Not Found**: Resource not found
- **500 Internal Server Error**: Server-side errors

### Error Response Format
```json
{
  "error": "Error Type",
  "message": "Detailed error message"
}
```

## Performance Optimizations

1. **Database Connection Pooling**: Single PDO instance reused
2. **Prepared Statement Caching**: PDO handles statement caching
3. **JSON Response Compression**: Enable in web server
4. **Index Optimization**: Database indexes on frequently queried columns
5. **Lazy Loading**: Entity relationships loaded on demand

## Future Enhancements

- [ ] Rate limiting middleware
- [ ] API versioning (v1, v2, etc.)
- [ ] Request/response logging
- [ ] CORS middleware for browser clients
- [ ] Pagination for list endpoints
- [ ] Advanced filtering and sorting
- [ ] Caching layer (Redis)
- [ ] API documentation (OpenAPI/Swagger)
- [ ] Unit and integration tests
- [ ] Docker containerization
