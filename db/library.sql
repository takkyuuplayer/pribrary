CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    label TEXT NOT NULL,
    description TEXT NOT NULL,
    stash_data TEXT DEFAULT '{}',
    created_at INTEGER DEFAULT NULL,
    updated_at INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS books (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER NOT NULL,
    number INTEGER NOT NULL,
    author TEXT NOT NULL,
    title TEXT NOT NULL,
    publisher TEXT NOT NULL,
    stash_data TEXT DEFAULT '{}',
    created_at INTEGER DEFAULT NULL,
    updated_at INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS rentals (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    book_id INTEGER NOT NULL,
    user TEXT NOT NULL,
    place TEXT NOT NULL,
    return_flag INTEGER DEFAULT 0,
    stash_data TEXT DEFAULT '{}',
    start_date INTEGER NOT NULL,
    end_date INTEGER NOT NULL,
    created_at INTEGER DEFAULT NULL,
    updated_at INTEGER DEFAULT NULL
);
