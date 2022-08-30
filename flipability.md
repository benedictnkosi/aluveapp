delete duplicates

ALTER TABLE `flipability_property` ADD `id` INT NOT NULL AUTO_INCREMENT AFTER `erf`, ADD PRIMARY KEY (`id`);
ALTER TABLE `flipability_property` ADD `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `id`;
ALTER TABLE `flipability_property` CHANGE `ï»¿bedrooms` `bedrooms` INT(11) NULL DEFAULT NULL;
ALTER TABLE `flipability_property` ADD `type` VARCHAR(11) NOT NULL DEFAULT 'house' AFTER `id`;


DELETE t1 FROM flipability_property t1
INNER JOIN flipability_property t2
WHERE
t1.id < t2.id AND
t1.url = t2.url;

delete from flipability_property where erf is NULL or erf = 0;

delete from flipability_property where price = 0;

delete from flipability_property where erf < 500;

delete FROM `flipability_property` WHERE timestamp > '2022-08-16 11:26:00';

