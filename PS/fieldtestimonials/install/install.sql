CREATE TABLE IF NOT EXISTS `ps_fieldtestimonials` (
    `id_fieldtestimonials` int(11) NOT NULL AUTO_INCREMENT,
    `name_post` varchar(100) NOT NULL,
    `email` varchar(100) NOT NULL,
    `company` varchar(255) DEFAULT NULL,
    `address` varchar(500) NOT NULL,
    `media_link` varchar(500) DEFAULT NULL,
    `media_link_id` varchar(20) DEFAULT NULL,
    `button_link` varchar(500) DEFAULT NULL,
    `media` varchar(255) DEFAULT NULL,
    `media_type` varchar(25) DEFAULT NULL,
    `content` text NOT NULL,
    `date_add` datetime DEFAULT NULL,
    `active` tinyint(1) DEFAULT NULL,
    `position` int(11) DEFAULT NULL,
    PRIMARY KEY (`id_fieldtestimonials`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

INSERT INTO `ps_fieldtestimonials` VALUES (1, 'Martin Frank', 'CEO Fieldthemes', 'Online Marketer', 'Online Marketer', '', '', '#', '630-client-3.png', 'png', '', '2014-10-21 05:51:42', 1, 0);

INSERT INTO `ps_fieldtestimonials` VALUES (2, 'Reeves', 'CorneliusReeves@gmai.com', 'Project Manager', 'Project Manager', '', '', '#', '993-client-6.png', 'png', '', '2014-10-21 05:55:43', 1, 1);

INSERT INTO `ps_fieldtestimonials` VALUES (3, 'Rechsteiner', 'JochenRechsteiner@gmai.com', 'Stylish Manager', 'Stylish Manager', '', '', '#', '58-client-8.png', 'png', '', '2014-10-21 05:57:44', 1, 2);

CREATE TABLE IF NOT EXISTS `ps_fieldtestimonials_lang` (
    `id_fieldtestimonials` int(10) unsigned NOT NULL,
    `id_lang` int(10) unsigned,
    `content` text NOT NULL,
    PRIMARY KEY (`id_fieldtestimonials`,`id_lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO `ps_fieldtestimonials_lang` VALUES ('1', '1', '<p>Sed mauris Pellentesque elit Aliquam at lacus interdum nascetur elit ipsum. Enim ipsum hendrerit Suspendisse turpis laoreet  Suspendisse turpis laoreet fames. fames.</p>');
INSERT INTO `ps_fieldtestimonials_lang` VALUES ('1', '2', '<p>Sed mauris Pellentesque elit Aliquam at lacus interdum nascetur elit ipsum. Enim ipsum hendrerit Suspendisse turpis laoreet  Suspendisse turpis laoreet fames. fames.</p>');
INSERT INTO `ps_fieldtestimonials_lang` VALUES ('1', '3', '<p>Sed mauris Pellentesque elit Aliquam at lacus interdum nascetur elit ipsum. Enim ipsum hendrerit Suspendisse turpis laoreet  Suspendisse turpis laoreet fames. fames.</p>');
INSERT INTO `ps_fieldtestimonials_lang` VALUES ('1', '4', '<p>Sed mauris Pellentesque elit Aliquam at lacus interdum nascetur elit ipsum. Enim ipsum hendrerit Suspendisse turpis laoreet  Suspendisse turpis laoreet fames. fames.</p>');

INSERT INTO `ps_fieldtestimonials_lang` VALUES ('2', '1', '<p>Sed mauris Pellentesque elit Aliquam at lacus interdum nascetur elit ipsum. Enim ipsum hendrerit Suspendisse turpis laoreet  Suspendisse turpis laoreet fames. fames.</p>');
INSERT INTO `ps_fieldtestimonials_lang` VALUES ('2', '2', '<p>Sed mauris Pellentesque elit Aliquam at lacus interdum nascetur elit ipsum. Enim ipsum hendrerit Suspendisse turpis laoreet  Suspendisse turpis laoreet fames. fames.</p>');
INSERT INTO `ps_fieldtestimonials_lang` VALUES ('2', '3', '<p>Sed mauris Pellentesque elit Aliquam at lacus interdum nascetur elit ipsum. Enim ipsum hendrerit Suspendisse turpis laoreet  Suspendisse turpis laoreet fames. fames.</p>');
INSERT INTO `ps_fieldtestimonials_lang` VALUES ('2', '4', '<p>Sed mauris Pellentesque elit Aliquam at lacus interdum nascetur elit ipsum. Enim ipsum hendrerit Suspendisse turpis laoreet  Suspendisse turpis laoreet fames. fames.</p>');

INSERT INTO `ps_fieldtestimonials_lang` VALUES ('3', '1', '<p>Sed mauris Pellentesque elit Aliquam at lacus interdum nascetur elit ipsum. Enim ipsum hendrerit Suspendisse turpis laoreet  Suspendisse turpis laoreet fames. fames.</p>');
INSERT INTO `ps_fieldtestimonials_lang` VALUES ('3', '2', '<p>Sed mauris Pellentesque elit Aliquam at lacus interdum nascetur elit ipsum. Enim ipsum hendrerit Suspendisse turpis laoreet  Suspendisse turpis laoreet fames. fames.</p>');
INSERT INTO `ps_fieldtestimonials_lang` VALUES ('3', '3', '<p>Sed mauris Pellentesque elit Aliquam at lacus interdum nascetur elit ipsum. Enim ipsum hendrerit Suspendisse turpis laoreet  Suspendisse turpis laoreet fames. fames.</p>');
INSERT INTO `ps_fieldtestimonials_lang` VALUES ('3', '4', '<p>Sed mauris Pellentesque elit Aliquam at lacus interdum nascetur elit ipsum. Enim ipsum hendrerit Suspendisse turpis laoreet  Suspendisse turpis laoreet fames. fames.</p>');


CREATE TABLE IF NOT EXISTS `ps_fieldtestimonials_shop` (
    `id_fieldtestimonials` int(10) unsigned NOT NULL,
    `id_shop` int(10) unsigned NOT NULL,
    PRIMARY KEY (`id_fieldtestimonials`,`id_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `ps_fieldtestimonials_shop` VALUES (1, 1);
INSERT INTO `ps_fieldtestimonials_shop` VALUES (2, 1);
INSERT INTO `ps_fieldtestimonials_shop` VALUES (3, 1);

