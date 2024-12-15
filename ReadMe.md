# Project ReadMe

## Overview
This project is a Symfony-based application that includes API Platform for building RESTful APIs. The project also uses Docker for containerized development and includes automated tests to ensure functionality.
Project description: ProjectDescription.md

---

## Prerequisites
Before setting up the project, ensure the following are installed on your system:

- **Docker**: [Install Docker](https://docs.docker.com/get-docker/)
- **Docker Compose**: [Install Docker Compose](https://docs.docker.com/compose/install/)

---

## Setup Steps

### 1. Clone the Repository
```bash
git clone https://github.com/Ghorbannezhad/api-platform.git
cd api-platform
```
### 2. Install Dependencies
Run the following commands to install PHP and JavaScript dependencies:
```bash
composer install
docker-compose run --rm node-service yarn install
docker-compose run --rm node-service yarn dev
```

### 3. Configure Environment Variables
Ensure the following variables are correctly set in `.env`:
- **APP_ENV**: `dev` or `prod`
- **DATABASE_URL**: Update with your database credentials if not using Docker.

### 4. Start Docker Containers
Run the following command to build and start the containers:
```bash
docker-compose up -d

docker exec -it php82-container bash
```
This will start services for the database, web server, and Mailcatcher (for email testing).

### 5. Apply Database Migrations
Create and migrate the database:
```bash
php bin/console doctrine:database:create

php bin/console doctrine:migrations:migrate
```

### 6. Load Fixtures (Optional)
If you want to load sample data into the database:
```bash
symfony console hautelook:fixtures:load
```

---

## Running the Application
Access the application in your browser:
- API Documentation: [http://localhost:8090/api](http://localhost:8090/api)

---

## Tests

### 1. Create Test Database
Create and migrate the database:
```bash
php bin/console --env=test doctrine:database:create

php bin/console --env=test doctrine:migrations:migrate
```

### 2. Load Fixtures (Optional)
If you want to load sample data into the database:
```bash
symfony console --env=test hautelook:fixtures:load
```

### 3. Run tests
Run the following command to execute the test suite:
```bash
php bin/phpunit tests

```
---

## Troubleshooting
- **Permission Issues**:
  If you encounter permission issues, ensure that the current user has write access to shared volumes by running:
  ```bash
  sudo chown -R $USER:$USER ./var
  ```
- **Database Errors**:
  Ensure the database container is running and the credentials in `.env` are correct.
- **Assets Not Found**:
  Rebuild frontend assets:
  ```bash
  npm run build
  ```
- **Test Errors**:
    If you encounter error for framework.test configuration during test, ensure that env is correct:
    ```bash
    docker exec -it php82-container bash

    EXPORT APP_ENV=test
    ```
---

---

## License
This project is licensed under the MIT License.


    