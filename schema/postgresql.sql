CREATE OR REPLACE FUNCTION unix_timestamp(timestamp with time zone DEFAULT NOW()) RETURNS bigint
  AS 'SELECT EXTRACT(EPOCH FROM $1)::bigint'
  LANGUAGE SQL;

CREATE TYPE frequency AS ENUM ('minutely', 'hourly', 'daily', 'weekly', 'monthly');

CREATE TABLE timeframe (
  id serial PRIMARY KEY,
  name varchar(128) NOT NULL UNIQUE,
  title varchar(255) DEFAULT NULL,
  start varchar(255) NOT NULL,
  "end" varchar(255) NOT NULL,
  ctime bigint NOT NULL DEFAULT unix_timestamp() * 1000,
  mtime bigint NOT NULL DEFAULT unix_timestamp() * 1000
);

INSERT INTO timeframe (name, title, start, "end") VALUES
  ('4 Hours', null, '-4 hours', 'now'),
  ('25 Hours', null, '-25 hours', 'now'),
  ('One Week', null, '-1 week', 'now'),
  ('One Month', null, '-1 month', 'now'),
  ('One Year', null, '-1 year', 'now'),
  ('Current Day', null, 'midnight', 'now'),
  ('Last Day', null, 'yesterday midnight', 'yesterday 23:59:59'),
  ('Current Week', null, 'monday this week midnight', 'sunday this week 23:59:59'),
  ('Last Week', null, 'monday last week midnight', 'sunday last week 23:59:59'),
  ('Current Month', null, 'first day of this month midnight', 'now'),
  ('Last Month', null, 'first day of last month midnight', 'last day of last month 23:59:59'),
  ('Current Year', null, 'first day of January this year midnight', 'now'),
  ('Last Year', null, 'first day of January last year midnight', 'last day of December last year 23:59:59');

CREATE TABLE report (
  id serial PRIMARY KEY,
  timeframe_id int NOT NULL,
  author varchar(255) NOT NULL,
  name varchar(128) NOT NULL UNIQUE,
  ctime bigint NOT NULL DEFAULT unix_timestamp() * 1000,
  mtime bigint NOT NULL DEFAULT unix_timestamp() * 1000,
  CONSTRAINT report_timeframe FOREIGN KEY (timeframe_id) REFERENCES timeframe (id)
);

CREATE TABLE reportlet (
  id serial PRIMARY KEY,
  report_id int NOT NULL,
  class varchar(255) NOT NULL,
  ctime bigint NOT NULL DEFAULT unix_timestamp() * 1000,
  mtime bigint NOT NULL DEFAULT unix_timestamp() * 1000,
  CONSTRAINT reportlet_report FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE config (
  id serial PRIMARY KEY,
  reportlet_id int NOT NULL,
  name varchar(255) NOT NULL,
  value text DEFAULT NULL,
  ctime bigint NOT NULL DEFAULT unix_timestamp() * 1000,
  mtime bigint NOT NULL DEFAULT unix_timestamp() * 1000,
  CONSTRAINT config_reportlet FOREIGN KEY (reportlet_id) REFERENCES reportlet (id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE schedule (
  id serial PRIMARY KEY,
  report_id int NOT NULL,
  author varchar(255) NOT NULL,
  start bigint NOT NULL,
  frequency frequency,
  action varchar(255) NOT NULL,
  config text DEFAULT NULL,
  ctime bigint NOT NULL DEFAULT unix_timestamp() * 1000,
  mtime bigint NOT NULL DEFAULT unix_timestamp() * 1000,
  CONSTRAINT schedule_report FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE ON UPDATE CASCADE
);
