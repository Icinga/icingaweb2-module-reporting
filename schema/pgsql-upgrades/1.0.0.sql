ALTER TABLE schedule
  DROP COLUMN start,
  DROP COLUMN frequency;

CREATE TYPE boolenum AS ENUM ('n', 'y');

CREATE TABLE reporting_schema (
  id serial,
  version varchar(64) NOT NULL,
  timestamp bigint NOT NULL,
  success boolenum DEFAULT NULL,
  reason text DEFAULT NULL,

  CONSTRAINT pk_reporting_schema PRIMARY KEY (id),
  CONSTRAINT idx_reporting_schema_version UNIQUE (version)
);

INSERT INTO reporting_schema (version, timestamp, success, reason)
  VALUES ('1.0.0', unix_timestamp() * 1000, 'y', NULL);
