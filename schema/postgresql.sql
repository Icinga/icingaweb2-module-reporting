CREATE TYPE frequency AS ENUM ('minutely', 'hourly', 'daily', 'weekly', 'monthly');

CREATE TABLE timeframe (
  id BIGSERIAL NOT NULL PRIMARY KEY,
  name varchar(255) NOT NULL UNIQUE,
  title varchar(255) NULL DEFAULT NULL,
  start_time varchar(255) NOT NULL,
  end_time varchar(255) NOT NULL,
  ctime NUMERIC(20) NOT NULL,
  mtime NUMERIC(20) NOT NULL
);

INSERT INTO timeframe (name, title, start_time, end_time, ctime, mtime) VALUES
  ('4 Hours', null, '-4 hours', 'now', to_char(current_timestamp, 'YYYYMMDDHH24MISS')::NUMERIC(20)*1000, to_char(current_timestamp, 'YYYYMMDDHH24MISS')::NUMERIC(20)*1000),
  ('25 Hours', null, '-25 hours', 'now', to_char(current_timestamp, 'YYYYMMDDHH24MISS')::NUMERIC(20)*1000, to_char(current_timestamp, 'YYYYMMDDHH24MISS')::NUMERIC(20)*1000),
  ('One Week', null, '-1 week', 'now', to_char(current_timestamp, 'YYYYMMDDHH24MISS')::NUMERIC(20)*1000, to_char(current_timestamp, 'YYYYMMDDHH24MISS')::NUMERIC(20)*1000),
  ('One Month', null, '-1 month', 'now', to_char(current_timestamp, 'YYYYMMDDHH24MISS')::NUMERIC(20)*1000, to_char(current_timestamp, 'YYYYMMDDHH24MISS')::NUMERIC(20)*1000),
  ('One Year', null, '-1 year', 'now', to_char(current_timestamp, 'YYYYMMDDHH24MISS')::NUMERIC(20)*1000, to_char(current_timestamp, 'YYYYMMDDHH24MISS')::NUMERIC(20)*1000),
  ('Current Day', null, 'midnight', 'now', to_char(current_timestamp, 'YYYYMMDDHH24MISS')::NUMERIC(20)*1000, to_char(current_timestamp, 'YYYYMMDDHH24MISS')::NUMERIC(20)*1000),
  ('Last Day', null, 'yesterday midnight', 'yesterday 23:59:59', to_char(current_timestamp, 'YYYYMMDDHH24MISS')::NUMERIC(20)*1000, to_char(current_timestamp, 'YYYYMMDDHH24MISS')::NUMERIC(20)*1000),
  ('Current Week', null, 'monday this week midnight', 'sunday this week 23:59:59', to_char(current_timestamp, 'YYYYMMDDHH24MISS')::NUMERIC(20)*1000, to_char(current_timestamp, 'YYYYMMDDHH24MISS')::NUMERIC(20)*1000),
  ('Last Week', null, 'monday last week midnight', 'sunday last week 23:59:59', to_char(current_timestamp, 'YYYYMMDDHH24MISS')::NUMERIC(20)*1000, to_char(current_timestamp, 'YYYYMMDDHH24MISS')::NUMERIC(20)*1000),
  ('Current Month', null, 'first day of this month midnight', 'now', to_char(current_timestamp, 'YYYYMMDDHH24MISS')::NUMERIC(20)*1000, to_char(current_timestamp, 'YYYYMMDDHH24MISS')::NUMERIC(20)*1000),
  ('Last Month', null, 'first day of last month midnight', 'last day of last month 23:59:59', to_char(current_timestamp, 'YYYYMMDDHH24MISS')::NUMERIC(20)*1000, to_char(current_timestamp, 'YYYYMMDDHH24MISS')::NUMERIC(20)*1000),
  ('Current Year', null, 'first day of this year midnight', 'now', to_char(current_timestamp, 'YYYYMMDDHH24MISS')::NUMERIC(20)*1000, to_char(current_timestamp, 'YYYYMMDDHH24MISS')::NUMERIC(20)*1000),
  ('Last Year', null, 'first day of last year midnight', 'last day of December last year 23:59:59', to_char(current_timestamp, 'YYYYMMDDHH24MISS')::NUMERIC(20)*1000, to_char(current_timestamp, 'YYYYMMDDHH24MISS')::NUMERIC(20)*1000);

CREATE TABLE report (
  id BIGSERIAL NOT NULL PRIMARY KEY,
  timeframe_id BIGINT NOT NULL,
  author varchar(255) NOT NULL,
  name varchar(255) NOT NULL UNIQUE,
  ctime NUMERIC(20) NOT NULL,
  mtime NUMERIC(20) NOT NULL,
  CONSTRAINT report_timeframe FOREIGN KEY (timeframe_id) REFERENCES timeframe (id)
);

CREATE TABLE reportlet (
  id BIGSERIAL NOT NULL PRIMARY KEY,
  report_id BIGINT NOT NULL,
  class varchar(255) NOT NULL,
  ctime NUMERIC(20) NOT NULL,
  mtime NUMERIC(20) NOT NULL,
  CONSTRAINT reportlet_report FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE config (
  id BIGSERIAL NOT NULL PRIMARY KEY,
  reportlet_id BIGINT NOT NULL,
  name varchar(255) NOT NULL,
  value text NULL DEFAULT NULL,
  ctime NUMERIC(20) NOT NULL,
  mtime NUMERIC(20) NOT NULL,
  CONSTRAINT config_reportlet FOREIGN KEY (reportlet_id) REFERENCES reportlet (id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE schedule (
  id BIGSERIAL NOT NULL PRIMARY KEY,
  report_id BIGINT NOT NULL,
  author varchar(255) NOT NULL,
  start NUMERIC(20) NOT NULL,
  frequency frequency,
  action varchar(255) NOT NULL,
  config text NULL DEFAULT NULL,
  ctime NUMERIC(20) NOT NULL,
  mtime NUMERIC(20) NOT NULL,
  CONSTRAINT schedule_report FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE ON UPDATE CASCADE
);
