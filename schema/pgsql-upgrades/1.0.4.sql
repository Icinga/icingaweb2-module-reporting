UPDATE timeframe SET "end" = 'now' WHERE name = 'Current Week';

INSERT INTO reporting_schema (version, timestamp, success, reason)
  VALUES ('1.0.4', unix_timestamp() * 1000, 'y', NULL);
