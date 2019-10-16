CREATE TABLE timeframe (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  name varchar(128) NOT NULL COLLATE utf8mb4_unicode_ci,
  title varchar(255) NULL DEFAULT NULL COLLATE utf8mb4_unicode_ci,
  start varchar(255) NOT NULL COLLATE utf8mb4_unicode_ci,
  end varchar(255) NOT NULL COLLATE utf8mb4_unicode_ci,
  ctime bigint(20) unsigned NOT NULL,
  mtime bigint(20) unsigned NOT NULL,
  PRIMARY KEY(id),
  UNIQUE KEY timeframe (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

INSERT INTO timeframe (name, title, start, end, ctime, mtime) VALUES
  ('4 Hours', null, '-4 hours', 'now', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
  ('25 Hours', null, '-25 hours', 'now', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
  ('One Week', null, '-1 week', 'now', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
  ('One Month', null, '-1 month', 'now', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
  ('One Year', null, '-1 year', 'now', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
  ('Current Day', null, 'midnight', 'now', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
  ('Last Day', null, 'yesterday midnight', 'yesterday 23:59:59', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
  ('Current Week', null, 'monday this week midnight', 'sunday this week 23:59:59', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
  ('Last Week', null, 'monday last week midnight', 'sunday last week 23:59:59', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
  ('Current Month', null, 'first day of this month midnight', 'now', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
  ('Last Month', null, 'first day of last month midnight', 'last day of last month 23:59:59', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
  ('Current Year', null, 'first day of January this year midnight', 'now', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
  ('Last Year', null, 'first day of January last year midnight', 'last day of December last year 23:59:59', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000);

CREATE TABLE report (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  timeframe_id int(10) unsigned NOT NULL,
  template_id int(10) unsigned NULL DEFAULT NULL,
  author varchar(255) NOT NULL COLLATE utf8mb4_unicode_ci,
  name varchar(128) NOT NULL COLLATE utf8mb4_unicode_ci,
  ctime bigint(20) unsigned NOT NULL,
  mtime bigint(20) unsigned NOT NULL,
  PRIMARY KEY(id),
  UNIQUE KEY report (name),
  CONSTRAINT report_timeframe FOREIGN KEY (timeframe_id) REFERENCES timeframe (id),
  CONSTRAINT report_template FOREIGN KEY (template_id) REFERENCES template (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE reportlet (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  report_id int(10) unsigned NOT NULL,
  class varchar(255) NOT NULL,
  ctime bigint(20) unsigned NOT NULL,
  mtime bigint(20) unsigned NOT NULL,
  PRIMARY KEY(id),
  CONSTRAINT reportlet_report FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE config (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  reportlet_id int(10) unsigned NOT NULL,
  name varchar(255) NOT NULL COLLATE utf8mb4_unicode_ci,
  value text NULL DEFAULT NULL,
  ctime bigint(20) unsigned NOT NULL,
  mtime bigint(20) unsigned NOT NULL,
  PRIMARY KEY(id),
  CONSTRAINT config_reportlet FOREIGN KEY (reportlet_id) REFERENCES reportlet (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE schedule (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  report_id int(10) unsigned NOT NULL,
  author varchar(255) NOT NULL COLLATE utf8mb4_unicode_ci,
  start bigint(20) unsigned NOT NULL,
  frequency enum('minutely', 'hourly', 'daily', 'weekly', 'monthly'),
  action varchar(255) NOT NULL,
  config text NULL DEFAULT NULL,
  ctime bigint(20) unsigned NOT NULL,
  mtime bigint(20) unsigned NOT NULL,
  PRIMARY KEY(id),
  CONSTRAINT schedule_report FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE template (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  author varchar(255) NOT NULL COLLATE utf8mb4_unicode_ci,
  name varchar(128) NOT NULL COLLATE utf8mb4_unicode_ci,
  settings longblob NOT NULL,
  ctime bigint(20) unsigned NOT NULL,
  mtime bigint(20) unsigned NOT NULL,
  PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- CREATE TABLE share (
--   id int(10) unsigned NOT NULL AUTO_INCREMENT,
--   report_id int(10) unsigned NOT NULL,
--   username varchar(255) NOT NULL COLLATE utf8mb4_unicode_ci,
--   restriction enum('none', 'owner', 'consumer'),
--   ctime bigint(20) unsigned NOT NULL,
--   mtime bigint(20) unsigned NOT NULL,
--   PRIMARY KEY(id),
--   CONSTRAINT share_report FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE ON UPDATE CASCADE
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
