

# New Plan Zut

> Schedule for ZUT students built with PHP, HTML, CSS, and TypeScript."

## Table of Contents

- [New Plan Zut](#new-plan-zut)
  - [Table of Contents](#table-of-contents)
  - [Technologies](#technologies)
  - [Requirements](#requirements)
  - [Installation](#installation)
    - [1. Clone the Repository](#1-clone-the-repository)
    - [2. Install PHP Dependencies](#2-install-php-dependencies)
    - [3. Set Up the Database](#3-set-up-the-database)
    - [4. Install TypeScript Globally](#4-install-typescript-globally)
  - [Running the Application](#running-the-application)
    - [1. Start the Web Server](#1-start-the-web-server)
    - [2. Access the Application](#2-access-the-application)
    - [3. Compile TypeScript into Javascript](#3-compile-typescript-into-javascript)
    - [4. Run front-end using `live server` or opening `index.html`](#4-run-front-end-using-live-server-or-opening-indexhtml)
  - [Demo](#demo)

## Technologies

- **Backend**: PHP
- **Frontend**: HTML5, CSS3, TypeScript
- **Database**: SQLite

## Requirements

Before you begin, ensure you have met the following requirements:

- **PHP**: Version 8.0 or higher
- **Composer**: For managing PHP dependencies
- **Node.js and npm**: For managing frontend dependencies and building TypeScript

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/Dragodui/new-plan-zut.git
cd new-plan-zut
```

### 2. Install PHP Dependencies

Ensure you have Composer installed. Then run:

```bash
cd backend
composer install
```

### 3. Set Up the Database

You can setup your `.sqlite` database using [this script]("https://github.com/Dragodui/repo-will-be-soon") *(will be soon)*

### 4. Install TypeScript Globally

```bash
npm install -g typescript
```


## Running the Application

### 1. Start the Web Server

You can use PHP's built-in server for development:

```bash
cd backend
php composer.phar start
```

### 2. Access the Application

Open your browser and navigate to [http://localhost:8000](http://localhost:8000).

### 3. Compile TypeScript into Javascript 

```bash
cd frontend
tsc
```

### 4. Run front-end using `live server` or opening `index.html`

## Demo
![изображение](https://github.com/user-attachments/assets/971cf760-469b-43f6-9bf2-6dd64ca15222)

