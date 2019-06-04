UPDATE timeframe SET start = 'first day of January this year midnight', mtime = CURRENT_TIMESTAMP() * 1000 WHERE name = 'Current Year';
UPDATE timeframe SET start = 'first day of January last year midnight', mtime = CURRENT_TIMESTAMP() * 1000 WHERE name = 'Last Year';
