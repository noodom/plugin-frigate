CREATE TABLE IF NOT EXISTS `frigate_events` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `box` text DEFAULT NULL,
    `camera` text DEFAULT NULL,
    `data` text DEFAULT NULL,
    `lasted` text DEFAULT NULL,
    `startTime` int(11) NULL,
    `endTime` int(11) NULL,
    `false_positive` text DEFAULT NULL,
    `hasClip` tinyint(1),
    `clip` text DEFAULT NULL,
    `hasSnapshot` tinyint(1),
    `snapshot` text DEFAULT NULL,
    `event_id` text DEFAULT NULL,
    `label` text DEFAULT NULL,
    `plusId` text DEFAULT NULL,
    `retain` text DEFAULT NULL,
    `subLabel` text DEFAULT NULL,
    `thumbnail` text DEFAULT NULL,
    `score` int(11) NULL,
    `topScore` int(11) NULL,
    `zones` text DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;