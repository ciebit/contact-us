--
-- Fale Conosco
--
CREATE TABLE IF NOT EXISTS `cb_contactus_messages` (
  `id`                      int(5) unsigned NOT NULL AUTO_INCREMENT,
  `name`                    varchar(100) DEFAULT NULL,
  `address_place`           varchar(500) DEFAULT NULL,
  `address_number`          varchar(10) DEFAULT NULL,
  `address_neighborhood`    varchar(80) DEFAULT NULL,
  `address_complement`      varchar(500) DEFAULT NULL,
  `address_cep`             varchar(8) DEFAULT NULL,
  `address_city_id`         int(8) unsigned DEFAULT NULL,
  `address_city_name`       varchar(100) DEFAULT NULL,
  `address_state_name`      varchar(100) DEFAULT NULL,
  `phone`                   varchar(20) DEFAULT NULL,
  `email`                   varchar(100) DEFAULT NULL,
  `subject`                 varchar(300) DEFAULT NULL,
  `body`                    text NOT NULL,
  `date_hour`               datetime NOT NULL,
  `status`                  tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='versao:001';

--
-- Respostas
--
CREATE TABLE IF NOT EXISTS `cb_contactus_replies` (
  `id`          int(5) unsigned NOT NULL AUTO_INCREMENT,
  `message_id` int(5) unsigned NOT NULL,
  `reply`       text NOT NULL,
  `date_hour`   datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='versao:001';
