CREATE TABLE IF NOT EXISTS timeframe (
  id BIGSERIAL NOT NULL PRIMARY KEY,
  name varchar(255) NOT NULL UNIQUE,
  title varchar(255) NULL DEFAULT NULL,
  start_time varchar(255) NOT NULL,
  end_time varchar(255) NOT NULL,
  ctime TIMESTAMP WITH TIME ZONE NOT NULL,
  mtime TIMESTAMP WITH TIME ZONE NOT NULL
) ;

INSERT INTO timeframe (name, title, start_time, end_time, ctime, mtime) VALUES
  ('4 Hours', null, '-4 hours', 'now', current_timestamp, current_timestamp),
  ('25 Hours', null, '-25 hours', 'now', current_timestamp, current_timestamp),
  ('One Week', null, '-1 week', 'now', current_timestamp, current_timestamp),
  ('One Month', null, '-1 month', 'now', current_timestamp, current_timestamp),
  ('One Year', null, '-1 year', 'now', current_timestamp, current_timestamp),
  ('Current Day', null, 'midnight', 'now', current_timestamp, current_timestamp),
  ('Last Day', null, 'yesterday midnight', 'yesterday 23:59:59', current_timestamp, current_timestamp),
  ('Current Week', null, 'monday this week midnight', 'sunday this week 23:59:59', current_timestamp, current_timestamp),
  ('Last Week', null, 'monday last week midnight', 'sunday last week 23:59:59', current_timestamp, current_timestamp),
  ('Current Month', null, 'first day of this month midnight', 'now', current_timestamp, current_timestamp),
  ('Last Month', null, 'first day of last month midnight', 'last day of last month 23:59:59', current_timestamp, current_timestamp),
  ('Current Year', null, 'first day of this year midnight', 'now', current_timestamp, current_timestamp),
  ('Last Year', null, 'first day of last year midnight', 'last day of December last year 23:59:59', current_timestamp, current_timestamp);

CREATE TABLE IF NOT EXISTS report (
  id BIGSERIAL NOT NULL PRIMARY KEY,
  timeframe_id BIGINT NOT NULL,
  author varchar(255) NOT NULL,
  name varchar(255) NOT NULL UNIQUE,
  ctime TIMESTAMP WITH TIME ZONE NOT NULL,
  mtime TIMESTAMP WITH TIME ZONE NOT NULL,
  CONSTRAINT report_timeframe FOREIGN KEY (timeframe_id) REFERENCES timeframe (id)
); 

CREATE TABLE IF NOT EXISTS reportlet (
  id BIGSERIAL NOT NULL PRIMARY KEY,
  report_id BIGINT NOT NULL,
  class varchar(255) NOT NULL,
  ctime TIMESTAMP WITH TIME ZONE NOT NULL,
  mtime TIMESTAMP WITH TIME ZONE NOT NULL,
  CONSTRAINT reportlet_report FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS config (
  id BIGSERIAL NOT NULL PRIMARY KEY,
  reportlet_id BIGINT NOT NULL,
  name varchar(255) NOT NULL,
  value text NULL DEFAULT NULL,
  ctime TIMESTAMP WITH TIME ZONE NOT NULL,
  mtime TIMESTAMP WITH TIME ZONE NOT NULL,
  CONSTRAINT config_reportlet FOREIGN KEY (reportlet_id) REFERENCES reportlet (id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TYPE IF NOT EXISTS frequency AS ENUM ('minutely', 'hourly', 'daily', 'weekly', 'monthly');

CREATE TABLE IF NOT EXISTS schedule (
  id BIGSERIAL NOT NULL PRIMARY KEY,
  report_id BIGINT NOT NULL,
  author varchar(255) NOT NULL,
  start NUMERIC(20) NOT NULL,
  frequency frequency,
  action varchar(255) NOT NULL,
  config text NULL DEFAULT NULL,
  ctime TIMESTAMP WITH TIME ZONE NOT NULL,
  mtime TIMESTAMP WITH TIME ZONE NOT NULL,
  CONSTRAINT schedule_report FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE ON UPDATE CASCADE
) ;

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
