CREATE TABLE IF NOT EXISTS `build2update` (
  `buildid` bigint(11) NOT NULL,
  `updateid` bigint(11) NOT NULL,
  PRIMARY  KEY(`buildid`),
  KEY `updateid` (`updateid`)
);

CREATE TABLE IF NOT EXISTS `submission2ip` (
  `submissionid` bigint(11) NOT NULL,
  `ip` varchar(255) NOT NULL default '',
  PRIMARY KEY (`submissionid`)
);


CREATE TABLE IF NOT EXISTS `measurement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectid` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `testpage` tinyint(1) NOT NULL,
  `summarypage` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `projectid` (`projectid`),
  KEY `name` (`name`)
);