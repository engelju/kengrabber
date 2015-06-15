CREATE TABLE options (
  key TEXT PRIMARY KEY UNIQUE NOT NULL,
  value TEXT
);

CREATE TABLE videos (
  id TEXT PRIMARY KEY UNIQUE NOT NULL,
  title TEXT NOT NULL,
  description TEXT NOT NULL,
  published DATE NOT NULL,
  downloaded INTEGER NOT NULL
);

INSERT INTO options VALUES ('db.schema','1.0');