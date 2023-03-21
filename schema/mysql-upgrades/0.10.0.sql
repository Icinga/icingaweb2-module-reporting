CREATE TABLE template (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  author varchar(255) NOT NULL COLLATE utf8mb4_unicode_ci,
  name varchar(128) NOT NULL COLLATE utf8mb4_unicode_ci,
  settings longblob NOT NULL,
  ctime bigint(20) unsigned NOT NULL,
  mtime bigint(20) unsigned NOT NULL,
  PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

ALTER TABLE report ADD COLUMN template_id int(10) unsigned NULL DEFAULT NULL AFTER timeframe_id;
ALTER TABLE report ADD CONSTRAINT report_template FOREIGN KEY (template_id) REFERENCES template (id);
