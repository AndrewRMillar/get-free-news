-- Create articles table
CREATE TABLE IF NOT EXISTS articles (
    id INTEGER PRIMARY KEY AUTOINCREMENT
    url varchar(500) NOT NULL UNIQUE,
    title varchar(250) NOT NULL UNIQUE,
    content TEXT NOT NULL,
    publication_date varchar(50)
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
