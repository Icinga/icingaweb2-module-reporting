CREATE TYPE boolenum AS ENUM ('n', 'y');

CREATE OR REPLACE FUNCTION unix_timestamp(timestamp with time zone DEFAULT NOW()) RETURNS bigint
  AS 'SELECT EXTRACT(EPOCH FROM $1)::bigint'
  LANGUAGE SQL;

CREATE TABLE template (
  id serial PRIMARY KEY,
  author varchar(255) NOT NULL,
  name varchar(128) NOT NULL,
  settings text NOT NULL,
  ctime bigint NOT NULL,
  mtime bigint NOT NULL
);

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
  ('Current Week', null, 'monday this week midnight', 'now'),
  ('Last Week', null, 'monday last week midnight', 'sunday last week 23:59:59'),
  ('Current Month', null, 'first day of this month midnight', 'now'),
  ('Last Month', null, 'first day of last month midnight', 'last day of last month 23:59:59'),
  ('Current Year', null, 'first day of January this year midnight', 'now'),
  ('Last Year', null, 'first day of January last year midnight', 'last day of December last year 23:59:59');

CREATE TABLE report (
  id serial PRIMARY KEY,
  timeframe_id int NOT NULL,
  template_id int NULL DEFAULT NULL,
  author varchar(255) NOT NULL,
  name varchar(128) NOT NULL UNIQUE,
  ctime bigint NOT NULL DEFAULT unix_timestamp() * 1000,
  mtime bigint NOT NULL DEFAULT unix_timestamp() * 1000,
  CONSTRAINT report_timeframe FOREIGN KEY (timeframe_id) REFERENCES timeframe (id),
  CONSTRAINT report_template FOREIGN KEY (template_id) REFERENCES template (id)
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
  action varchar(255) NOT NULL,
  config text DEFAULT NULL,
  ctime bigint NOT NULL DEFAULT unix_timestamp() * 1000,
  mtime bigint NOT NULL DEFAULT unix_timestamp() * 1000,
  CONSTRAINT schedule_report FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE reporting_schema (
  id serial,
  version varchar(64) NOT NULL,
  timestamp bigint NOT NULL,
  success boolenum DEFAULT NULL,
  reason text DEFAULT NULL,

  CONSTRAINT pk_reporting_schema PRIMARY KEY (id),
  CONSTRAINT idx_reporting_schema_version UNIQUE (version)
);

INSERT INTO reporting_schema (version, timestamp, success)
  VALUES ('1.0.3', UNIX_TIMESTAMP() * 1000, 'y');
