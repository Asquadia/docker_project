# Dockerized Web Application with Apache, PHP, MySQL, and Traefik

## Table of Contents

- [Introduction](#introduction)
- [Prerequisites](#prerequisites)
- [Project Overview](#project-overview)
- [Getting Started](#getting-started)
  - [Clone the Repository](#clone-the-repository)
  - [Configure Hosts File](#configure-hosts-file)
  - [Create Docker Network](#create-the-network)
  - [Start the Application](#start-the-application)
      - [Build the Docker Images](#build)
      - [Run the Application](#up)
      - [Check Application Health](#health)
      - [Access the Application](#access)
      - [Default Credentials](#default-credentials)
- [Application Architecture](#application-architecture)
  - [Services](#services)
    - [Web Service](#web-service)
    - [Database Service](#database-service)
    - [Traefik Reverse Proxy](#traefik-reverse-proxy)
    - [phpMyAdmin](#phpmyadmin)
  - [Network](#network)
  - [Volumes and Secrets](#volumes-and-secrets)
- [Technical Choices](#technical-choices)
  - [Web Server and Language](#web-server-and-language)
  - [Database](#database)
  - [Database Management](#database-management)
- [Traefik Configuration](#traefik-configuration)
  - [`traefik/traefik.yml`](#traefiktraefikyml)
  - [`traefik/dynamic.yml`](#traefikdynamicyml)
- [Troubleshooting](#troubleshooting)

---

## Introduction

This document provides instructions on setting up and running a web application composed of PHP, MySQL, Apache web server, Traefik reverse proxy, and phpMyAdmin for database management, all orchestrated using Docker Compose.

---

## Prerequisites

Before starting, ensure the following are installed on your system:

-   **Traefik** (version 3.0 or later)
-   **Docker Engine** (version 20.10 or later)
-   **Docker Compose** (version 1.29 or later)
-   **Administrative privileges** (to modify the hosts file)

---

## Project Overview

This project demonstrates a containerized web application stack. The main components are:

-   **Web Application**: A PHP application served by an Apache web server.
-   **Database**: A MySQL 8.0 database server.
-   **Traefik Reverse Proxy**:  A modern reverse proxy and load balancer that routes traffic to the web application and phpMyAdmin.
-   **phpMyAdmin**: A web-based tool for managing the MySQL database.

---

## Getting Started

### Clone the Repository

```bash
git clone https://github.com/Asquadia/docker_project.git
cd docker_project
```

### Configure Hosts File

Add an entry to your hosts file to resolve `web.local` to your local machine.

```bash
# For Linux/macOS
sudo sh -c 'echo "127.0.0.1 web.local traefik.local" >> /etc/hosts'

# For Windows (run as administrator):
# notepad C:\Windows\System32\drivers\etc\hosts
# Add the line: 127.0.0.1 web.local traefik.local
```

### Create Docker Network

Create a Docker network to allow communication between containers.

```bash
docker network create webnet
```

### Start the Application

#### Build

Build the Docker images for each service.

```bash
docker compose build
```

#### Up

Start the application stack.

```bash
docker compose up
```

#### Health

Check the status and health of the services:

```bash
docker compose ps
```

#### Access

After the services have started (this may take a few seconds at least 30s), you can access the web application by navigating to `web.local` in your browser.

#### Default Credentials

The default credentials for the web page are:

- **phpMyAdmin Login**:
    -   **ID**:  `test`
    -   **Password**:  `groot`

---

## Application Architecture

### Services

#### Web Service

The web service consists of an Apache server running a PHP application. It is configured to communicate with the database service and is accessible through the Traefik reverse proxy.

#### Database Service

MySQL is used as the database management system. It was chosen due to its popularity and ease of use. The database data is persisted in a Docker volume to prevent data loss on container restarts.

#### Traefik Reverse Proxy

Traefik v3.0 acts as the reverse proxy and entry point for the application. It routes incoming requests to the appropriate service (web application or phpMyAdmin) based on the hostname. It's configured to use SSL certificates for secure communication.

#### phpMyAdmin

phpMyAdmin is included to provide a web interface for managing the MySQL database. It is also accessible through the Traefik reverse proxy.

### Network

The containers communicate with each other through a Docker bridge network named `webnet`. This network is defined in the `docker-compose.yml` file.

```yaml
networks:
  webnet:
    driver: bridge
    external: true
```

Each service is then configured to connect to this network. Example from the web application service:

```yaml
networks:
  - webnet
```

### Volumes and Secrets

#### Volumes

Data persistence is achieved using Docker volumes. For example, the MySQL database data is stored in a volume to survive container restarts:

```yaml
volumes:
  - db_data:/var/lib/mysql
  - ./db-init:/docker-entrypoint-initdb.d
```

#### Secrets

Sensitive data such as database credentials and SSL certificates are managed as Docker secrets. This is more secure than storing them directly in the `docker-compose.yml` file or in the image.

Example of using secrets for the database service:

```yaml
environment:
  MYSQL_ROOT_PASSWORD_FILE: /run/secrets/db_root_password
  MYSQL_DATABASE: exampledb
  MYSQL_USER_FILE: /run/secrets/db_user
  MYSQL_PASSWORD_FILE: /run/secrets/db_password
secrets:
  - db_root_password
  - db_user
  - db_password
```

The secrets are defined at the end of the `docker-compose.yml` file, linking the secret name to the file containing the secret value:

```yaml
secrets:
  db_root_password:
    file: ./secrets/db_root_password.txt
  db_user:
    file: ./secrets/db_user.txt
  db_password:
    file: ./secrets/db_password.txt
  localhost_crt:
    file: ./secrets/certs/localhost.crt
  localhost_key:
    file: ./secrets/certs/localhost.key
```

---

## Technical Choices

### Web Server and Language

Apache and PHP were chosen as the web server and language, respectively. This was primarily due to the project requirements. They also represent a widely used and well-supported stack for web development.

### Database

MySQL was selected as the database system. It was a choice made after seeing it be the first example set in the project example and description of the database part.

### Database Management

phpMyAdmin was included to provide a convenient web-based interface for managing the MySQL database.

---

## Traefik Configuration

Traefik's configuration is split into two files:


### `traefik/traefik.yml` - (the Static Configuration)


*   **API and Dashboard:**
    *   Enables the Traefik dashboard for monitoring and management.
    *   `insecure: true`  This makes the dashboard accessible without authentication not good for security but as a shcool project it's ok.

*   **Entry Points:**
    *   `web`: Listens on port 80 (HTTP) and redirects all traffic to `websecure` (HTTPS). This ensures all communication is encrypted with TLS.
    *   `websecure`: Listens on port 443 (HTTPS) for secure connections.

*   **Providers:**
    *   `docker`:  Automatically discovers and configures routes for Docker containers.
        *   `exposedByDefault: false`  means containers won't be exposed unless explicitly configured.
    *   `file`:  Loads dynamic configuration from  `/dynamic.yml`  and watches for changes, allowing updates without restarting Traefik (depending also from the system and many other things).

*   **Certificate Resolvers:**
    *   `myresolver`: Defines how Traefik obtains SSL/TLS certificates. In this case, it loads certificates from local files (`/run/secrets/localhost_crt` and `/run/secrets/localhost_key`).


### `traefik/dynamic.yml` - Dynamic Configuration

This file contains the routing rules and service definitions that can change without restarting Traefik:

*   **HTTP Routers:** Define how incoming requests are routed to services based on hostnames and other criteria.
    *   `web-router`: Routes traffic for  `web.local`  to the  `web-service`. It also enforces HTTPS using `websecure` entrypoint and applies the `csrf` middleware, and the `myresolver` for TLS certificate.
    *   `traefik-dashboard`: Makes the Traefik dashboard accessible at  `traefik.local`  over HTTPS.
    *   `phpmyadmin-router`: Routes traffic for `pma.web.local` to `phpmyadmin-service` and applies the `myresolver` for TLS certificate.

*   **Middlewares:** Modifies requests and responses.
    *   `csrf`:  Adds several security-related HTTP headers to protect against common web attacks like clickjacking, XSS, and others. it doesn't implement CSRF protection, it implements other security headers

*   **Services:** Define the actual backend applications where the traffic is forwarded.
    *   `web-service`:  Load balances traffic to the  `apache-php-app`  container running on port 80.
    *   `phpmyadmin-service`:  Load balances traffic to the  `phpmyadmin`  container running on port 80.

*   **TLS Options:**
    *   `default`: Sets the default minimum TLS version to 1.3.

In this file you will find most of the Traefik setting, the certificate and the required version of TLS, the load balancer the middle ware (just for fun), and all the routing for the webapp, dashboard and other 

## Troubleshooting

Many things. I never used a reverse proxy and had no knowledge of networking. It was a good experience, but from my point of view (that could be wrong, I'm human after all) it was way too rough. I passed most of my time reading the Traefik forum for how to implement a certificate or dynamic configuration, and most of the answers were in the 2.0 version, so most of the times it worked, but not always. These posts were some of the most helpful :

https://community.traefik.io/t/traefik-not-serving-custom-ssl-certificate/19865/6
https://community.traefik.io/t/using-traefik-as-a-reverse-proxy-with-certificates-signed-by-my-own-pki/23178/7

Thanks to the Docker course and the little exercise, I had enough basics to make the docker-compose and Dockerfile without any big trouble (just some minor errors, but nothing major). The real trouble I had was with Traefik; the documentation was really not good enough. I had really many difficulties because of that. My post on SO was removed for the reason "it's not linked to programming", which is not wrong but not right either, anyway. A course on Traefik would have been good and on networking just the basic 2 hour would have been enough.
