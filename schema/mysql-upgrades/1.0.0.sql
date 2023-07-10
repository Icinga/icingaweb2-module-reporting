ALTER TABLE schedule
  DROP COLUMN start,
  DROP COLUMN frequency;

CREATE TABLE reporting_schema (
  id int unsigned NOT NULL AUTO_INCREMENT,
  version varchar(64) NOT NULL,
  timestamp bigint unsigned NOT NULL,
  success enum ('n', 'y') DEFAULT NULL,
  reason text DEFAULT NULL,

  PRIMARY KEY (id),
  CONSTRAINT idx_reporting_schema_version UNIQUE (version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin ROW_FORMAT=DYNAMIC;

INSERT INTO reporting_schema (version, timestamp, success, reason)
  VALUES ('1.0.0', UNIX_TIMESTAMP() * 1000, 'y', NULL);
