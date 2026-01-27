# Get Free News

A news article reader application that fetches, extracts, and serves articles through a GraphQL API. Built with PHP, it provides a clean, distraction-free interface for reading articles from at the moment only de Volkskrant.

## Features

- **Article Fetching**: Automatically fetch and extract article content from URLs
- **Content Extraction**: Intelligent HTML parsing to extract readable article content
- **GraphQL API**: Query articles via a modern GraphQL interface
- **Persistent Storage**: Store articles locally in JSON format (sqlite in the planning)
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
│   ├── Domain/              # Core business entities
│   │   └── Article.php      # Article entity
│   ├── Application/         # Business logic layer
│   │   ├── ArticleService.php
│   │   └── ArticleContentExtractor.php
│   └── Infrastructure/      # External integrations
│       ├── ArticleRepository.php
│       └── HttpFetcher.php
├── public/
│   ├── graphql.php         # GraphQL endpoint
│   └── bootstrap.php       # Application initialization
├── css/                    # Stylesheets
├── data/                   # Persistent storage (JSON)
├── index.php               # Main entry point
└── vendor/                # Composer dependencies
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

The project follows clean architecture principles:

- **Domain Layer**: Contains pure business entities (`Article`) with no external dependencies
- **Application Layer**: Service layer handling use cases (`ArticleService`, `ArticleContentExtractor`)
- **Infrastructure Layer**: External integrations and data access (`ArticleRepository`, `HttpFetcher`)

This separation ensures:
- Easy testing
- Independence from frameworks and libraries
- Clear responsibility boundaries

## File Storage

Articles are stored in `data/articles.json` in JSON format for simplicity and portability.

## Development Scripts

```bash
npm run dev      # Watch and rebuild CSS in development
npm run build    # Build minified CSS for production
```

## License

ISC

## Author

Andrew
