DROP PROCEDURE IF EXISTS migrate_schedule_config;
DELIMITER //
CREATE PROCEDURE migrate_schedule_config()
BEGIN
  DECLARE schedule_id int;
  DECLARE schedule_start bigint;
  DECLARE schedule_frequency enum('minutely', 'hourly', 'daily', 'weekly', 'monthly');
  DECLARE schedule_config text;

  DECLARE frequency_json text;

  DECLARE done int DEFAULT 0;
  DECLARE schedule CURSOR FOR SELECT id, start, frequency, config FROM schedule;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

  OPEN schedule;
  read_loop: LOOP
    FETCH schedule INTO schedule_id, schedule_start, schedule_frequency, schedule_config;
    IF done THEN
      LEAVE read_loop;
    END IF;
    IF NOT INSTR(schedule_config, 'frequencyType') THEN
      SET frequency_json = CONCAT(
        ',"frequencyType":"\\\\ipl\\\\Scheduler\\\\Cron","frequency":"{',
        '\\"expression\\":\\"@', schedule_frequency,
        '\\",\\"start\\":\\"', DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(schedule_start / 1000), @@session.TIME_ZONE, 'UTC'), '%Y-%m-%dT%H:%i:%s.%f UTC'),
        '\\"}"'
      );
      UPDATE schedule SET config = INSERT(schedule_config, LENGTH(schedule_config), 0, frequency_json) WHERE id = schedule_id;
    END IF;
  END LOOP;
  CLOSE schedule;
END //
DELIMITER ;

CALL migrate_schedule_config();
DROP PROCEDURE migrate_schedule_config;

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
