
-- Batting standard
-- http://www.baseball-reference.com/leagues/MLB/2013-standard-batting.shtml
-- Data obtained 10/2014
DROP TABLE IF EXISTS `2013_bat_std`;
CREATE TABLE `2013_bat_std` (
  `Rk` int(11) NOT NULL,
  `nameFull` varchar(30) NOT NULL,
  `nameFirst` varchar(15) NOT NULL,
  `nameLast` varchar(15) NOT NULL,
  `Age` varchar(3) NOT NULL,
  PRIMARY KEY (`nameFull`,`Team`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `2013_bat_std`(`Rk`,`nameFull`,`nameFirst`,`nameLast`,`Age`)
VALUES
-- Input data from conv_file here

;

-- Batting ratios
-- http://www.baseball-reference.com/leagues/MLB/2013-ratio-batting.shtml
-- Data obtained 10/2014
DROP TABLE IF EXISTS `2013_bat_ratio`;
CREATE TABLE `2013_bat_ratio` (
  `Rk` int(11) NOT NULL,
  `nameFull` varchar(30) NOT NULL,
  `nameFirst` varchar(15) NOT NULL,
  `nameLast` varchar(15) NOT NULL,
  `Age` varchar(3) NOT NULL,
  `PA` int(11) NOT NULL,
  `Team` varchar(3) NOT NULL,
  `HR_pct` DECIMAL(6,3),
  `SO_pct` DECIMAL(6,3),
  `BB_pct` DECIMAL(6,3),
  `XBH_pct` DECIMAL(6,3),
  `X__H_pct` DECIMAL(6,3),
  `SO__W` DECIMAL(6,3),
  `AB__SO` DECIMAL(6,3),
  `AB__HR` DECIMAL(6,3),
  `AB__RBI` DECIMAL(6,3),
  `GB__FB` DECIMAL(6,3),
  `GO__AO` DECIMAL(6,3),
  `IP_pct` DECIMAL(6,3),
  `LD_pct` DECIMAL(6,3),
  `HR__FB` DECIMAL(6,3),
  `IF__FB` DECIMAL(6,3),
  PRIMARY KEY (`nameFull`,`Team`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `2013_bat_ratio`(`Rk`,`nameFull`,`nameFirst`,`nameLast`,`Age`,`PA`,`Team`,`HR_pct`,`SO_pct`,`BB_pct`,`XBH_pct`,
`X__H_pct`,`SO__W`,`AB__SO`,`AB__HR`,`AB__RBI`,`GB__FB`,`GO__AO`,`IP_pct`,`LD_pct`,`HR__FB`,`IF__FB`)
VALUES
-- Input data from conv_file here

;

-- Pitching standard
-- http://www.baseball-reference.com/leagues/MLB/2013-standard-pitching.shtml
-- Data obtained 10/2014
DROP TABLE IF EXISTS `2013_pitch_std`;
CREATE TABLE `2013_pitch_std` (
  `Rk` int(11) NOT NULL,
  `nameFull` varchar(30) NOT NULL,
  `nameFirst` varchar(15) NOT NULL,
  `nameLast` varchar(15) NOT NULL,
  `Age` varchar(3) NOT NULL,
  `Team` varchar(3) NOT NULL,
  PRIMARY KEY (`nameFull`,`Team`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `2013_pitch_std`(`Rk`,`nameFull`,`nameFirst`,`nameLast`,`Age`,`Team`)
VALUES
-- Input data from conv_file here

;

-- Pitching opponents 
-- http://www.baseball-reference.com/leagues/MLB/2013-batting-pitching.shtml
-- Data obtained 10/2014
DROP TABLE IF EXISTS `2013_pitch_opp`;
CREATE TABLE `2013_pitch_opp` (
  `Rk` int(11) NOT NULL,
  `nameFull` varchar(30) NOT NULL,
  `nameFirst` varchar(15) NOT NULL,
  `nameLast` varchar(15) NOT NULL,
  `Age` varchar(3) NOT NULL,
  `Team` varchar(3) NOT NULL,
  `IP` DECIMAL(6,3) NOT NULL,
  `PAu` int(11),
  `G` int(11) NOT NULL,
  `PA` int(11) NOT NULL,
  `AB` int(11) NOT NULL,
  `R` int(11) NOT NULL,
  `H` int(11) NOT NULL,
  `2B` int(11) NOT NULL,
  `3B` int(11) NOT NULL,
  `HR` int(11) NOT NULL,
  `SB` int(11) NOT NULL,
  `CS` int(11) NOT NULL,
  `BB` int(11) NOT NULL,
  `SO` int(11) NOT NULL,
  `BA` DECIMAL(6,5) NOT NULL,
  `OBP` DECIMAL(6,5) NOT NULL,
  `SLG` DECIMAL(6,5) NOT NULL,
  `OPS` DECIMAL(6,5) NOT NULL,
  `BAbip` DECIMAL(6,5),
  `TB` int(11),
  `GDP` int(11),
  `HBP` int(11),
  `SH` int(11),
  `SF` int(11),
  `IBB` int(11),
  `ROE` int(11),
  PRIMARY KEY (`nameFull`,`Team`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `2013_pitch_opp`(`Rk`,`nameFull`,`nameFirst`,`nameLast`,`Age`,`Team`,`IP`,`PAu`,`G`,`PA`,`AB`,`R`,
  `H`,`2B`,`3B`,`HR`,`SB`,`CS`,`BB`,`SO`,`BA`,`OBP`,`SLG`,`OPS`,`BAbip`,`TB`,`GDP`,`HBP`,`SH`,`SF`,`IBB`,`ROE`)
VALUES
-- Input data from conv_file here

;

-- Pitching ratios 
-- http://www.baseball-reference.com/leagues/MLB/2013-ratio-pitching.shtml
-- Data obtained 10/2014
DROP TABLE IF EXISTS `2013_pitch_ratio`;
CREATE TABLE `2013_pitch_ratio` (
  `Rk` int(11) NOT NULL,
  `nameFull` varchar(30) NOT NULL,
  `nameFirst` varchar(15) NOT NULL,
  `nameLast` varchar(15) NOT NULL,
  `Age` varchar(3) NOT NULL,
  `Team` varchar(3) NOT NULL,
  `IP` DECIMAL(6,3) NOT NULL,
  `Ptn_pct` DECIMAL(6,3),
  `HR_pct` DECIMAL(6,3),
  `SO_pct` DECIMAL(6,3),
  `BB_pct` DECIMAL(6,3),
  `XBH_pct` DECIMAL(6,3),
  `X__H_pct` DECIMAL(6,3),
  `GB__FB` DECIMAL(6,3),
  `GO__AO` DECIMAL(6,3),
  `IP_pct` DECIMAL(6,3),
  `LD_pct` DECIMAL(6,3),
  `HR__FB` DECIMAL(6,3),
  `IF__FB` DECIMAL(6,3),
  `DPOpp` int(11),
  `DP` int(11),
  `DP_pct` DECIMAL(6,3),
  PRIMARY KEY (`nameFull`,`Team`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `2013_pitch_ratio`(`Rk`,`nameFull`,`nameFirst`,`nameLast`,`Age`,`Team`,`IP`,
  `Ptn_pct`,`HR_pct`,`SO_pct`,`BB_pct`,`XBH_pct`,`X__H_pct`,`GB__FB`,`GO__AO`,`IP_pct`,`LD_pct`,
  `HR__FB`,`IF__FB`,`DPOpp`,`DP`,`DP_pct`)
VALUES
-- Input data from conv_file here

;

----------------------------------------------------------