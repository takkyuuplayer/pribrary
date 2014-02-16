CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    label TEXT NOT NULL,
    description TEXT NOT NULL,
    stash_data TEXT,
    created_at INTEGER,
    updated_at INTEGER
);

CREATE TABLE IF NOT EXISTS books (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER NOT NULL,
    author TEXT NOT NULL,
    title TEXT NOT NULL,
    publisher TEXT NOT NULL,
    stash_data TEXT,
    created_at INTEGER,
    updated_at INTEGER
);

CREATE TABLE IF NOT EXISTS rentals (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    book_id INTEGER NOT NULL,
    user TEXT NOT NULL,
    place TEXT NOT NULL,
    return_flag INTEGER DEFAULT 0,
    stash_data TEXT,
    start_date INTEGER,
    end_date INTEGER,
    created_at INTEGER,
    updated_at INTEGER
);
