
CREATE TABLE `glpi_plugin_mobile_options` (
`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
`users_id` INTEGER NOT NULL,
`cols_limit` INTEGER,
`rows_limit` INTEGER,
`edit_mode` INTEGER DEFAULT 1,
`native_select` INTEGER DEFAULT 1,
PRIMARY KEY (`id`)
)
ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `glpi_plugin_mobile_profiles` (
`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
`profiles_id` VARCHAR(45) NOT NULL,
`mobile_user` CHAR(1),
PRIMARY KEY (`id`)
)
ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

