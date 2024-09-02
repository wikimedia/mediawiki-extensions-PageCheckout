-- This file is automatically generated using maintenance/generateSchemaSql.php.
-- Source: db/page_checkout_event.json
-- Do not modify this file directly.
-- See https://www.mediawiki.org/wiki/Manual:Schema_changes
CREATE TABLE page_checkout_event (
  pce_action TEXT NOT NULL, pce_actor_id INT NOT NULL,
  pce_page_id INT NOT NULL, pce_revision_id INT NOT NULL,
  pce_timestamp TIMESTAMPTZ DEFAULT NULL,
  pce_comment TEXT DEFAULT NULL, pce_lock TEXT DEFAULT NULL
);