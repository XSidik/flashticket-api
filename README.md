# 🎫 FlashTicket - High Performance Ticketing System

FlashTicket is a state-of-the-art API-based ticketing system built with **Laravel 12**, designed to handle high-concurrency scenarios, specifically optimized for "Ticket War" events.

## 🚀 Key Advantages & Features

### 1. **High Concurrency Optimization (War Mode)**
The system is built to handle thousands of concurrent requests during peak sales periods.
*   **Atomic Redis Operations**: Uses Redis `DECR` and `INCR` for atomic quota management, preventing overselling even under heavy traffic.
*   **Two-Tier Validation**: Quota is first secured in Redis (speed layer) before being persisted in PostgreSQL (consistency layer).

### 2. **Modern Technology Stack**
*   **Laravel 12**: Utilizing the latest features of the PHP framework.
*   **PHP 8 Attributes**: Modern Swagger/OpenAPI documentation using PHP 8 attributes instead of docblock annotations.
*   **PostgreSQL**: Robust and reliable relational database for transaction management.
*   **Redis**: High-speed in-memory data store for real-time quota tracking.
*   **Laravel Octane Ready**: Optimized for ultra-low latency performance.

### 3. **Stateless API Architecture**
*   **Laravel Sanctum**: Secure token-based authentication (Bearer Tokens).
*   **Standardized Responses**: Consistent JSON response format (`status`, `message`, `data`) across all endpoints via a dedicated API Trait.
*   **Global Error Handling**: Forced JSON responses for all API routes, even during auth failures.

### 4. **Professional Documentation**
*   **Integrated Swagger UI**: Full API documentation accessible at `/api/documentation`.
*   **Interactive Testing**: Persisted authentication in Swagger UI for seamless testing.

---

## 🛠 Tech Stack
*   **Backend**: Laravel 12 (PHP 8.2+)
*   **Database**: PostgreSQL
*   **Cache/Real-time**: Redis
*   **Auth**: Laravel Sanctum
*   **Docs**: L5-Swagger (OpenAPI 3.0)

---

## 📦 Installation & Setup

1.  **Clone the repository**
2.  **Install dependencies**
    ```bash
    composer install
    ```
3.  **Environment Configuration**
    ```bash
    cp .env.example .env
    # Configure your DB_CONNECTION=pgsql and REDIS details
    php artisan key:generate
    ```
4.  **Database Migration & Seeding**
    ```bash
    php artisan migrate
    php artisan db:seed
    ```
    *This creates an Admin and a Customer user (Password: `Qwerty123`).*

5.  **Generate API Documentation**
    ```bash
    php artisan l5-swagger:generate
    ```

6.  **Run the Server**
    ```bash
    php artisan serve
    # Or using Octane for maximum performance
    php artisan octane:start
    ```

---

## 🔐 Access Credentials (Seeded)

*   **Admin**: `admin@example.com` | `Qwerty123`
*   **Customer**: `user@example.com` | `Qwerty123`

---

## 📖 API Documentation
Once the server is running, visit:
`http://localhost:8000/api/documentation`


## 🔗 Connect with Me
Don't forget to check my LinkedIn: [Nursidik](https://linkedin.com/in/nur-sidik-13420b165)

## 🛡 License
This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
