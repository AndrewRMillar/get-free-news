# Read the volkskrant articles [out of date]

A news article reader application that fetches, extracts, and serves articles through a GraphQL API. Built with PHP, it provides a clean, distraction-free interface for reading articles from at the moment only de Volkskrant. I'm not sure if other news paper sites can be used in the same way.

LLM generated readme, it's a bit much but I'll leave it as is for now... 

## Features

- **Article Fetching**: Automatically fetch and extract article content from URLs
- **Content Extraction**: Intelligent HTML parsing to extract readable article content
- **GraphQL API**: Query articles via a modern GraphQL interface
- **Persistent Storage**: Store articles in SQLite database for reliable data persistence
- **CSRF Protection**: Secure API endpoints with CSRF token validation
- **Modern UI**: Clean, dark-themed interface built with Tailwind CSS and Alpine.js
- **Reader Mode**: Distraction-free reading experience

## Tech Stack

### Backend
- **PHP 8.1+**: Core application logic
- **GraphQL PHP**: GraphQL implementation for type-safe queries
- **Custom Domain Model**: Clean architecture with Domain, Application, and Infrastructure layers

### Frontend
- **Alpine.js**: Lightweight interactivity
- **Tailwind CSS**: Utility-first styling
- **HTML5**: Semantic markup

### Build Tools
- **Composer**: PHP dependency management
- **Node.js/npm**: Frontend asset building
- **PostCSS**: CSS processing
- **ESLint**: Code quality checking

## Project Structure

```
├── src/
│   ├── Domain/                                 # Core business entities
│   │   └── Model/
│   │       ├── Article.php                     # Article entity
│   │   └── Repository/ 
│   │   │   └── ArticleRepositoryInterface.php  # Repository interface
│   ├── Application/                            # Business logic layer
│   │   ├── ArticleService.php
│   │   ├── ArticleContentExtractor.php
│   │   └── ArticleException.php
│   └── Infrastructure/                         # External integrations
│       ├── ArticleRepository.php               # SQLite implementation
│       └── HttpFetcher.php
├── public/
│   ├── graphql.php                             # GraphQL endpoint
│   └── bootstrap.php                           # Application initialization
├── css/                                        # Stylesheets
├── data/                                       # Persistent storage
│       ├── articles.json                       # Legacy JSON storage
│       └── articles.sqlite                     # SQLite database
├── index.php                                   # Main entry point
└── vendor/                                     # Composer dependencies
```

## Installation

### Prerequisites
- PHP 8.1 or higher
- Node.js and npm
- Composer

### Setup

1. **Install PHP dependencies**:
   ```bash
   composer install
   ```

2. **Install Node dependencies**:
   ```bash
   npm install
   ```

3. **Build CSS**:
   ```bash
   npm run build
   ```

## Usage

### Development

Start the development server with CSS watch mode:
```bash
npm run dev
```

This will:
- Start Tailwind CSS in watch mode
- Monitor for CSS changes and rebuild automatically

### Production

Build optimized CSS for production:
```bash
npm run build
```

This generates minified CSS in `css/app.css`.

### Running the Application

Start a PHP development server:
```bash
php -S localhost:8000
```

Access the application at `http://localhost:8000`

### Using the app

Either past an url from the Volkskrant and add press the button or chose an article from the drop down when eirlier articles were saved to the database  

## API

The application provides a GraphQL API endpoint at `/public/graphql.php`.

### Example Query

```graphql
query {
  articles {
    id
    title
    url
    content
    savedAt
  }
}
```

### Security

- CSRF token validation is required for POST requests
- Sessions are used to manage CSRF tokens
- All input is sanitized for security

## Configuration

### Tailwind CSS
Edit `tailwind.config.js` to customize styling.

### PostCSS
Configure CSS processing in `postcss.config.js`.

### ESLint
JavaScript linting configuration in `eslint.config.mjs`.

## Architecture

### Clean Architecture Approach

The project follows clean architecture principles with proper separation of concerns:

- **Domain Layer**: Contains pure business entities (`Article`) and abstractions (`ArticleRepositoryInterface`) with no external dependencies
- **Application Layer**: Service layer handling use cases (`ArticleService`, `ArticleContentExtractor`) with custom exception handling (`ArticleException`)
- **Infrastructure Layer**: External integrations and data access (`ArticleRepository` using SQLite, `HttpFetcher`)

This separation ensures:
- Easy testing and maintainability
- Independence from frameworks and libraries
- Clear responsibility boundaries
- Proper error handling through typed exceptions

### Repository Pattern

The project implements the Repository pattern through `ArticleRepositoryInterface` and `ArticleRepository`, allowing for:
- Abstraction of data storage details
- Easy switching between storage backends (JSON, SQLite, etc.)
- Better testability with mock implementations

## Data Storage

Articles are persisted in a SQLite database located at `data/articles.sqlite`. The database provides reliable, queryable storage with support for transactions and complex queries. A legacy JSON storage option remains available at `data/articles.json`.

## Development Scripts

```bash
npm run dev      # Watch and rebuild CSS in development
npm run build    # Build minified CSS for production
```

## License

ISC

## Author

Andrew
