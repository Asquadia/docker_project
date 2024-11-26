# README.md

## Table of Contents

- [Introduction](#introduction)
- [Prerequisites](#prerequisites)
- [Project Overview](#project-overview)
- [Getting Started](#getting-started)
  - [Clone the Repository](#clone-the-repository)
  - [Configure Hosts File](#configure-hosts-file)
  - [Generate SSL Certificates](#generate-ssl-certificates)
  - [Create Docker Secrets](#create-docker-secrets)
  - [Start the Application](#start-the-application)
- [Application Architecture](#application-architecture)
  - [Services](#services)
    - [Web Service](#web-service)
    - [Database Service](#database-service)
    - [Traefik Reverse Proxy](#traefik-reverse-proxy)
    - [phpMyAdmin](#phpmyadmin)
  - [Networks and Volumes](#networks-and-volumes)
- [Technical Choices](#technical-choices)
  - [Operating System: Debian](#operating-system-debian)
  - [Web Server and Language: Apache and PHP](#web-server-and-language-apache-and-php)
  - [Database: MySQL](#database-mysql)
  - [Reverse Proxy: Traefik v3.0](#reverse-proxy-traefik-v30)
  - [Database Management: phpMyAdmin](#database-management-phpmyadmin)
- [Traefik Configuration](#traefik-configuration)
  - [`traefik/traefik.yml`](#traefiktraefikyml)
  - [`traefik/dynamic.yml`](#traefikdynamicyml)
- [Docker Compose File](#docker-compose-file)
- [Dockerfile](#dockerfile)
- [Running the Application](#running-the-application)

---

## Introduction

This documentation provides detailed instructions on how to set up and run a web application using Docker Compose and Traefik as a reverse Proxy. The application consists of a PHP web application, a MySQL database, a Traefik reverse proxy, and phpMyAdmin for database management.

---

## Prerequisites

Before proceeding, ensure you have the following installed on your system:
- **Traefik** (version 3.0)
- **Docker Engine** (version 20.10 or later)
- **Docker Compose** (version 1.29 or later)
- **OpenSSL** (for generating SSL certificates)
- **Administrative privileges** (for modifying the hosts file)

---

## Project Overview

The project consists of the following components:

- **Web Application**: A PHP application running on Apache server.
- **Database**: A MySQL 8.0 database server.
- **Traefik Reverse Proxy**: Acts as a gateway and reverse proxy.
- **phpMyAdmin**: A web interface for managing the MySQL database.

---

## Getting Started

### Clone the Repository

Clone the project repository to your local machine:

```bash
git clone https://github.com/Asquadia/docker_project.git
cd docker_project.git
```

### Configure Hosts File

You need to edit your hosts file to enable the name redirection when accesing web.local

```bash
nano /etc/hosts
# now add this line
127.0.0.1 web.local
```

### Create the network

```bash
docker network create webnet
```

### Start the Application

#### Build 

```bash
docker compose build
```

#### Up

```bash
docker compose up
```

you can also check the health of the system with 

```bash
docker compose ps
```

after a few seconde you can then open your browser (tested with firefox)

and go to ```bash web.local```

the default user name for root is :

ID : test
Password : groot

