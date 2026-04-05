# API Shop - RESTful API with PHP 8.2+

A fully-featured RESTful API built with PHP 8.2+ following PSR standards (PSR-4, PSR-12, PSR-7/15) with JWT authentication and role-based authorization.

## Features

- ✅ **CRUD Operations** for Users, Products, and Orders
- ✅ **Controller-Service-Repository Pattern** for clean architecture
- ✅ **JWT-based Authentication** 
- ✅ **Role-based Authorization** (Admin & User roles)
- ✅ **PSR-4** Autoloading
- ✅ **PSR-12** Coding Style
- ✅ **PSR-7/15** HTTP Interfaces and Middleware
- ✅ **MySQL Database** with PDO prepared statements
- ✅ **Slim Framework 4** for routing

## Directory Structure

```
apishop/
├── config/
│   └── database.sql          # Database schema and sample data
├── public/
│   └── index.php             # Application entry point
├── src/
│   ├── Config/
│   │   └── Database.php      # Database connection
│   ├── Controller/
│   │   ├── AuthController.php     # Authentication endpoints
│   │   ├── OrderController.php    # Order CRUD endpoints
│   │   ├── ProductController.php  # Product CRUD endpoints
│   │   └── UserController.php     # User CRUD endpoints
│   ├── Entity/
│   │   ├── Order.php         # Order entity
│   │   ├── Product.php       # Product entity
│   │   └── User.php          # User entity
│   ├── Middleware/
│   │   ├── AuthenticationMiddleware.php  # JWT verification
│   │   └── AuthorizationMiddleware.php   # Role-based permissions
│   ├── Repository/
│   │   ├── OrderRepository.php    # Order data access
│   │   ├── ProductRepository.php  # Product data access
│   │   └── UserRepository.php     # User data access
│   └── Service/
│       ├── JWTService.php         # JWT token operations
│       ├── OrderService.php       # Order business logic
│       ├── ProductService.php     # Product business logic
│       └── UserService.php        # User business logic
├── .env.example              # Environment variables template
├── .gitignore
├── composer.json             # PSR-compliant dependencies
└── README.md
```

## Requirements

- PHP 8.2 or higher
- MySQL 5.7+ or MariaDB 10.2+
- Composer
- Apache/Nginx with mod_rewrite or equivalent

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/tandrezone/apishop.git
cd apishop
```

### 2. Install dependencies

```bash
composer install
```

### 3. Configure environment

```bash
cp .env.example .env
```

Edit `.env` with your database credentials:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=apishop
DB_USER=root
DB_PASS=your_password

JWT_SECRET=your-secret-key-here-change-in-production
JWT_EXPIRY=3600
```

### 4. Set up the database

```bash
mysql -u root -p < config/database.sql
```

This creates the database schema and inserts sample data including:
- Admin user: `admin@example.com` / `admin123`
- Regular user: `user@example.com` / `user123`
- Sample products and orders

### 5. Configure web server

#### Apache (.htaccess in public/)

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```

#### Nginx

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

Set document root to `public/` directory.

### 6. Start the server

#### Using PHP built-in server (development only)

```bash
php -S localhost:8000 -t public
```

The API will be available at `http://localhost:8000`

## API Endpoints

### Authentication (Public)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/register` | Register a new user |
| POST | `/api/auth/login` | Login and get JWT token |

### Users (Protected)

| Method | Endpoint | Description | Admin | User |
|--------|----------|-------------|-------|------|
| GET | `/api/users` | Get all users | ✅ | ❌ |
| GET | `/api/users/{id}` | Get user by ID | ✅ | ✅ (own only) |
| POST | `/api/users` | Create a user | ✅ | ❌ |
| PUT | `/api/users/{id}` | Update a user | ✅ | ✅ (own only) |
| DELETE | `/api/users/{id}` | Delete a user | ✅ | ❌ |

### Products (Protected)

| Method | Endpoint | Description | Admin | User |
|--------|----------|-------------|-------|------|
| GET | `/api/products` | Get all products | ✅ | ✅ |
| GET | `/api/products/{id}` | Get product by ID | ✅ | ✅ |
| POST | `/api/products` | Create a product | ✅ | ❌ |
| PUT | `/api/products/{id}` | Update a product | ✅ | ❌ |
| DELETE | `/api/products/{id}` | Delete a product | ✅ | ❌ |

### Orders (Protected)

| Method | Endpoint | Description | Admin | User |
|--------|----------|-------------|-------|------|
| GET | `/api/orders` | Get all orders | ✅ (all) | ✅ (own only) |
| GET | `/api/orders/{id}` | Get order by ID | ✅ | ✅ (own only) |
| POST | `/api/orders` | Create an order | ✅ | ✅ |
| PUT | `/api/orders/{id}` | Update an order | ✅ | ✅ (own only) |
| DELETE | `/api/orders/{id}` | Delete an order | ✅ | ❌ |

## Usage Examples

### 1. Register a new user

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123",
    "name": "John Doe"
  }'
```

### 2. Login

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "admin123"
  }'
```

Response:
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "email": "admin@example.com",
    "name": "Admin User",
    "role": "admin"
  }
}
```

### 3. Get all products (with JWT token)

```bash
curl -X GET http://localhost:8000/api/products \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

### 4. Create a new product (Admin only)

```bash
curl -X POST http://localhost:8000/api/products \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Tablet",
    "description": "10-inch tablet with stylus",
    "price": 499.99,
    "stock": 25
  }'
```

### 5. Create an order

```bash
curl -X POST http://localhost:8000/api/orders \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "total_amount": 699.99,
    "items": [
      {"product_id": 2, "quantity": 1, "price": 699.99}
    ]
  }'
```

## Authorization Rules

### Admin Role
- **Full access** to all resources (Create, Read, Update, Delete)
- Can manage all users, products, and orders

### User Role

#### Products
- ✅ **Read only** (GET endpoints)
- ❌ Cannot create, update, or delete products

#### Users
- ✅ Can **Read and Update** their own user profile
- ❌ Cannot view other users' profiles
- ❌ Cannot create or delete users

#### Orders
- ✅ Can **Create** orders
- ✅ Can **Read and Update** their own orders only
- ❌ Cannot view other users' orders
- ❌ Cannot delete orders

## Architecture

### Controller-Service-Repository Pattern

1. **Controllers** (`src/Controller/`)
   - Handle HTTP requests and responses
   - Validate input data
   - Delegate business logic to Services
   - Return JSON responses

2. **Services** (`src/Service/`)
   - Contain business logic
   - Coordinate between Controllers and Repositories
   - Handle data transformation

3. **Repositories** (`src/Repository/`)
   - Handle database operations
   - Use PDO with prepared statements
   - Abstract data access layer

4. **Entities** (`src/Entity/`)
   - Represent domain models
   - Contain getters/setters
   - Data transfer objects

5. **Middleware** (`src/Middleware/`)
   - Authentication: Verify JWT tokens
   - Authorization: Check role-based permissions

## Security Features

- ✅ **Password Hashing** using PHP's `password_hash()` (bcrypt)
- ✅ **JWT Tokens** for stateless authentication
- ✅ **Prepared Statements** to prevent SQL injection
- ✅ **Role-based Access Control** (RBAC)
- ✅ **Input Validation** at controller level
- ✅ **HTTPS Ready** (configure in web server)

## Development

### Run code style check

```bash
composer check-style
```

### Auto-fix code style issues

```bash
composer fix-style
```

### Run tests (if implemented)

```bash
composer test
```

## PSR Compliance

- **PSR-4**: Autoloading standard for namespaces and classes
- **PSR-12**: Extended coding style guide
- **PSR-7**: HTTP message interfaces
- **PSR-15**: HTTP server request handlers and middleware

## Dependencies

- **slim/slim**: ^4.12 - Micro framework for routing
- **slim/psr7**: ^1.6 - PSR-7 implementation
- **php-di/php-di**: ^7.0 - Dependency injection container
- **firebase/php-jwt**: ^6.8 - JWT encoding/decoding
- **vlucas/phpdotenv**: ^5.5 - Environment variable loader

## License

This project is open-source and available under the MIT License.

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## Support

For issues and questions, please open an issue on GitHub.
