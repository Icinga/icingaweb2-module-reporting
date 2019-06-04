UPDATE timeframe SET start = 'first day of January this year midnight', mtime = CURRENT_TIMESTAMP() * 1000 WHERE name = 'Current Year';
UPDATE timeframe SET start = 'first day of January last year midnight', mtime = CURRENT_TIMESTAMP() * 1000 WHERE name = 'Last Year';

ALTER TABLE timeframe MODIFY COLUMN name varchar(128) NOT NULL COLLATE utf8mb4_unicode_ci;
ALTER TABLE timeframe ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin ROW_FORMAT=default;

ALTER TABLE report MODIFY COLUMN name varchar(128) NOT NULL COLLATE utf8mb4_unicode_ci;
ALTER TABLE report ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin ROW_FORMAT=default;
