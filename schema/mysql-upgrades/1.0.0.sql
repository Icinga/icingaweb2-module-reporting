ALTER TABLE schedule
    MODIFY COLUMN start bigint(20) unsigned DEFAULT NULL,
    MODIFY COLUMN frequency enum('minutely', 'hourly', 'daily', 'weekly', 'monthly') DEFAULT NULL;
