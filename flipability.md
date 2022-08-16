delete duplicates

DELETE t1 FROM flipability_property t1
INNER JOIN flipability_property t2
WHERE
t1.id < t2.id AND
t1.url = t2.url;

delete from flipability_property where erf is NULL or erf = 0;

delete from flipability_property where price = 0;

delete from flipability_property where erf < 500;

delete FROM `flipability_property` WHERE timestamp > '2022-08-16 11:26:00';

