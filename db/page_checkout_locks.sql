CREATE TABLE IF NOT EXISTS /*$wgDBprefix*/page_checkout_locks (
    `pcl_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `pcl_page_id` INT NOT NULL,
    `pcl_user_id` INT NOT NULL,
    `pcl_payload` BLOB NULL
) /*$wgDBTableOptions*/;
