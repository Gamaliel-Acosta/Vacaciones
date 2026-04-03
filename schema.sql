CREATE TABLE IF NOT EXISTS day_entries (
  id BIGSERIAL PRIMARY KEY,
  entry_date DATE NOT NULL UNIQUE,
  title VARCHAR(120) NOT NULL DEFAULT '',
  description TEXT NOT NULL,
  image_data BYTEA NULL,
  image_mime VARCHAR(100) NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_day_entries_entry_date ON day_entries (entry_date);

CREATE TABLE IF NOT EXISTS app_settings (
  id SMALLINT PRIMARY KEY,
  home_description TEXT NOT NULL,
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

INSERT INTO app_settings (id, home_description)
VALUES (1, '')
ON CONFLICT (id) DO NOTHING;