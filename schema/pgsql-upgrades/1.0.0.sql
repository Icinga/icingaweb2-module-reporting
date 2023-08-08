CREATE OR REPLACE PROCEDURE migrate_schedule_config()
  LANGUAGE plpgsql
  AS $$
  DECLARE
    row record;
    frequency_json text;
  BEGIN
    FOR row IN (SELECT id, start, frequency, config FROM schedule)
      LOOP
        IF NOT CAST(POSITION('frequencyType' IN row.config) AS bool) THEN
          frequency_json = CONCAT(
              ',"frequencyType":"\\ipl\\Scheduler\\Cron","frequency":"{',
              '\"expression\":\"@', row.frequency,
              '\",\"start\":\"', TO_CHAR(TO_TIMESTAMP(row.start / 1000) AT TIME ZONE 'UTC', 'YYYY-MM-DD"T"HH24:MI:SS.US UTC'),
              '\"}"'
            );
          UPDATE schedule SET config = OVERLAY(row.config PLACING frequency_json FROM LENGTH(row.config) FOR 0) WHERE id = row.id;
        END IF;
      END LOOP;
  END;
  $$;

CALL migrate_schedule_config();
DROP PROCEDURE migrate_schedule_config;

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
