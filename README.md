# Read the news articles

A news article reader application that fetches, extracts, and serves articles through a GraphQL API. Built with PHP, it provides a clean, distraction-free interface for reading articles from at the moment only de one Dutch news paper. I'm not sure if other news paper sites can be used in the same way.

# Read the news articles

Small PHP app that fetches and extracts news articles (from a single Dutch newspaper) and exposes them via a GraphQL API. The project also includes a minimal frontend and utilities for scraping and extracting article content.

## Quick Overview
- Backend: PHP (PSR-4 autoloading, small clean-architecture layout)
- API: GraphQL endpoint at `/public/graphql.php`
- Storage: SQLite database at `data/articles.sqlite` (schema provided in `data/schema.sql`)
- Frontend: Static HTML with Alpine.js and Tailwind CSS

## Project layout (relevant files)

```
├── src/                       # Application source (Domain / Application / Infrastructure)
├── public/                    # HTTP entrypoints (graphql.php, bootstrap.php)
├── css/                       # Tailwind input/output
├── data/                      # Database schema and sample DB/backups
│   ├── schema.sql
│   └── articles.sqlite
├── index.php                  # Frontend page
├── scrape-links.php           # CLI script for a cron
└── NewsPaperScraper.php # Link scraper class used by `scrape-links.php`
```

## Requirements

- PHP 8.1+
- Composer
- Node.js & npm (for building Tailwind CSS)

## Setup

1. Install PHP dependencies:

```bash
composer install
```

2. Install Node.js dependencies (only needed to build CSS):

```bash
npm install
```

3. Build CSS:

```bash
npm run dev   # watch and rebuild CSS
npm run build # build/minify css
```

## Initialize the database

The schema is available at `data/schema.sql`. To create or update the SQLite database run the included helper script:

```bash
./data/init.sh data/articles.sqlite data/schema.sql
```

This will create `data/articles.sqlite` (or apply the schema to an existing file).

Alternatively run:

```bash
sqlite3 data/articles.sqlite < data/schema.sql
```

## Run the application (development)

Start a PHP built-in server from the project root and open `http://localhost:8000`:

```bash
php -S localhost:8000
```

The GraphQL endpoint is available at `/public/graphql.php`.

## Scrapers and utilities

- `scrape-links.php` and `NewsPaperScraper.php` are legacy scripts for scraping — they can be used standalone from the project root.
- Core scraping and extraction logic lives under `src/Application` and `src/Infrastructure` (`ArticleContentExtractor`, `HttpFetcher`, `ArticleRepository`, etc.).

## Composer autoload

PSR-4 autoloading is configured in `composer.json` — run `composer dump-autoload` after making changes to class files/namespaces.

## NPM scripts

The `package.json` includes these useful scripts:

```bash
npm run dev   # tailwind watch (development)
npm run build # basically only build/minify css
```

## Tips

- Logs are written to `logs/` (see `src/Config/Paths.php` for configured locations).
- If you see missing classes after removing files, run `composer dump-autoload`.

## Disclaimer

The code in this repository is provided "as is", without warranty of any kind. The author (Andrew) is not responsible or liable for any damages, losses, or legal issues that may arise from using, modifying, or distributing this software. It is your responsibility to ensure that your use of this code complies with applicable laws and the terms of service of any third-party websites or services you interact with. Do not use this project to infringe copyright or access content illegally. This is a personal hobby project with no commercial intentions.

## License

ISC

## Author

Andrew
