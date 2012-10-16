CREATE TABLE paste
(
    hash      CHARACTER(8) PRIMARY KEY NOT NULL,
    language  VARCHAR(32) NOT NULL DEFAULT "txt",
    private   VARCHAR(5) NOT NULL CHECK (private IN ('true', 'false')),
    content   TEXT NOT NULL,
    timestamp INTEGER NOT NULL,
    timezone  VARCHAR(32) NOT NULL DEFAULT "UTC"
);

CREATE INDEX paste_public_sorted ON paste(private, timestamp DESC);
