
-- Batting Standard
-- http://www.baseball-reference.com/leagues/MLB/2014-standard-batting.shtml
-- Data obtained 10/25/2014
DROP TABLE IF EXISTS `2014_bat_std`;
CREATE TABLE `2014_bat_std` (
  `Rk` int(11) NOT NULL,
  `nameFull` varchar(30) NOT NULL,
  `nameFirst` varchar(15) NOT NULL,
  `nameLast` varchar(15) NOT NULL,
  `Hand` varchar(1),
  `Age` varchar(3),
  `Team` varchar(3) NOT NULL,
  `Lg` varchar(3) NOT NULL,
  `G` int(11) NOT NULL,
  `PA` int(11) NOT NULL,
  `AB` int(11) NOT NULL,
  `R` int(11),
  `H` int(11) NOT NULL,
  `2B` int(11) NOT NULL,
  `3B` int(11) NOT NULL,
  `HR` int(11) NOT NULL,
  `RBI` int(11),
  `SB` int(11) NOT NULL,
  `CS` int(11) NOT NULL,
  `BB` int(11) NOT NULL,
  `SO` int(11) NOT NULL,
  `BA` DECIMAL(6,5),
  `OBP` DECIMAL(6,5),
  `SLG` DECIMAL(6,5),
  `OPS` DECIMAL(6,5),
  `OPSplus` DECIMAL(6,5),
  `TB` int(11),
  `GDP` int(11),
  `HBP` int(11),
  `SH` int(11),
  `SF` int(11),
  `IBB` int(11),
  `Pos` varchar(15) NOT NULL,
  PRIMARY KEY (`Rk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `2014_bat_std`(`Rk`,`nameFull`,`nameFirst`,`nameLast`,`Hand`,`Age`,`Team`,`Lg`,`G`,`PA`,`AB`,`R`,`H`,`2B`,`3B`,`HR`,
`RBI`,`SB`,`CS`,`BB`,`SO`,`BA`,`OBP`,`SLG`,`OPS`,`OPSplus`,`TB`,`GDP`,`HBP`,`SH`,`SF`,`IBB`,`Pos`)
VALUES
-- Data to be inserted, the key phrase here will be replaced.
Input Batting Standard data here

;

-- Batting Ratios
-- http://www.baseball-reference.com/leagues/MLB/2014-ratio-batting.shtml
-- Data obtained 10/25/2014
DROP TABLE IF EXISTS `2014_bat_ratio`;
CREATE TABLE `2014_bat_ratio` (
  `Rk` int(11) NOT NULL,
  `nameFull` varchar(30) NOT NULL,
  `nameFirst` varchar(15) NOT NULL,
  `nameLast` varchar(15) NOT NULL,
  `Hand` varchar(1),
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
  PRIMARY KEY (`Rk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `2014_bat_ratio`(`Rk`,`nameFull`,`nameFirst`,`nameLast`,`Hand`,`Age`,`PA`,`Team`,`HR_pct`,`SO_pct`,`BB_pct`,`XBH_pct`,
`X__H_pct`,`SO__W`,`AB__SO`,`AB__HR`,`AB__RBI`,`GB__FB`,`GO__AO`,`IP_pct`,`LD_pct`,`HR__FB`,`IF__FB`)
VALUES
-- Data to be inserted, the key phrase here will be replaced.
Input Batting Ratios data here

;

-- Pitching Standard
-- http://www.baseball-reference.com/leagues/MLB/2014-standard-pitching.shtml
-- Data obtained 10/25/2014
DROP TABLE IF EXISTS `2014_pitch_std`;
CREATE TABLE `2014_pitch_std` (
  `Rk` int(11) NOT NULL,
  `nameFull` varchar(30) NOT NULL,
  `nameFirst` varchar(15) NOT NULL,
  `nameLast` varchar(15) NOT NULL,
  `Hand` varchar(1),
  `Age` varchar(3),
  `Team` varchar(3) NOT NULL,
  `Lg` varchar(3) NOT NULL,
  `W` int(11),
  `L` int(11),
  `WL_pct` DECIMAL(6,3),
  `ERA` DECIMAL(6,3),
  `G` int(11) NOT NULL,
  `GS` int(11) NOT NULL,
  `GF` int(11),
  `CG` int(11),
  `SHO` int(11),
  `SV` int(11),
  `IP` DECIMAL(6,3),
  `H` int(11),
  `R` int(11),
  `ER` int(11),
  `HR` int(11),
  `BB` int(11),
  `IBB` int(11),
  `SO` int(11),
  `HBP` int(11),
  `BK` int(11),
  `WP` int(11),
  `BF` int(11),
  `ERAplus` int(11),
  `FIP` DECIMAL(6,3),
  `WHIP` DECIMAL(6,3),
  `H9` DECIMAL(6,3),
  `HR9` DECIMAL(6,3),
  `BB9` DECIMAL(6,3),
  `SO9` DECIMAL(6,3),
  `SOW_pct` DECIMAL(6,3),
  PRIMARY KEY (`Rk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `2014_pitch_std`(`Rk`,`nameFull`,`nameFirst`,`nameLast`,`Hand`,`Age`,`Team`,`Lg`,`W`,`L`,`WL_pct`,`ERA`,`G`,`GS`,`GF`,`CG`,
  `SHO`,`SV`,`IP`,`H`,`R`,`ER`,`HR`,`BB`,`IBB`,`SO`,`HBP`,`BK`,`WP`,`BF`,`ERAplus`,`FIP`,`WHIP`,`H9`,`HR9`,`BB9`,`SO9`,`SOW_pct`)
VALUES
-- Data to be inserted, the key phrase here will be replaced.
Input Pitching Standard data here

;

-- Pitching Opposition 
-- http://www.baseball-reference.com/leagues/MLB/2014-batting-pitching.shtml
-- Data obtained 10/25/2014
DROP TABLE IF EXISTS `2014_pitch_opp`;
CREATE TABLE `2014_pitch_opp` (
  `Rk` int(11) NOT NULL,
  `nameFull` varchar(30) NOT NULL,
  `nameFirst` varchar(15) NOT NULL,
  `nameLast` varchar(15) NOT NULL,
  `Hand` varchar(1),
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
  PRIMARY KEY (`Rk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `2014_pitch_opp`(`Rk`,`nameFull`,`nameFirst`,`nameLast`,`Hand`,`Age`,`Team`,`IP`,`PAu`,`G`,`PA`,`AB`,`R`,
  `H`,`2B`,`3B`,`HR`,`SB`,`CS`,`BB`,`SO`,`BA`,`OBP`,`SLG`,`OPS`,`BAbip`,`TB`,`GDP`,`HBP`,`SH`,`SF`,`IBB`,`ROE`)
VALUES
-- Data to be inserted, the key phrase here will be replaced.
Input Pitching Opposition data here

;

-- Pitching Ratios 
-- http://www.baseball-reference.com/leagues/MLB/2014-ratio-pitching.shtml
-- Data obtained 10/25/2014
DROP TABLE IF EXISTS `2014_pitch_ratio`;
CREATE TABLE `2014_pitch_ratio` (
  `Rk` int(11) NOT NULL,
  `nameFull` varchar(30) NOT NULL,
  `nameFirst` varchar(15) NOT NULL,
  `nameLast` varchar(15) NOT NULL,
  `Hand` varchar(1),
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
  PRIMARY KEY (`Rk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `2014_pitch_ratio`(`Rk`,`nameFull`,`nameFirst`,`nameLast`,`Hand`,`Age`,`Team`,`IP`,
  `Ptn_pct`,`HR_pct`,`SO_pct`,`BB_pct`,`XBH_pct`,`X__H_pct`,`GB__FB`,`GO__AO`,`IP_pct`,`LD_pct`,
  `HR__FB`,`IF__FB`,`DPOpp`,`DP`,`DP_pct`)
VALUES
-- Data to be inserted, the key phrase here will be replaced.
Input Pitching Ratios data here

;