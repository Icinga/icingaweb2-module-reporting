UPDATE timeframe SET "end" = 'now' WHERE name = 'Current Week';

INSERT INTO reporting_schema (version, timestamp, success, reason)
  VALUES ('1.0.3', unix_timestamp() * 1000, 'y', NULL)
  ON CONFLICT ON CONSTRAINT idx_reporting_schema_version DO UPDATE SET success   = EXCLUDED.success,
                                                                       reason    = EXCLUDED.reason,
                                                                       timestamp = EXCLUDED.timestamp;
