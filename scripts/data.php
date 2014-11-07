<?php
// Historical data and other data sets have more names:
// 'ANA','CHA','CHN','FLO','KCA','LAN','MON','NYA','NYN','PHN','SDN','SFN','SLN','TBA','WAS'
$teams = ['SEA','TEX','LAA','HOU','OAK',
          'MIN','KCR','DET','CLE','CHW',
          'BOS','NYY','TBR','TOR','BAL',
          
          'LAD','COL','ARI','SFG','SDP',
          'STL','CIN','MIL','PIT','CHC',
          'ATL','MIA','NYM','PHI','WSN'];

// Get batters
$b_query = "SELECT
            bs.nameFirst,
            bs.nameLast,
            bs.AB,
            bs.H,
            bs.2B,
            bs.3B,
            bs.HR,
            (bs.H - bs.2B - bs.3B - bs.HR) as 1B,
            bs.BB,
            bs.SO,
            br.GB__FB as `G/F`,
            (bs.AB + bs.BB) as PA,
            bs.H / bs.AB as Average,
            (bs.H + bs.BB) / (bs.AB + bs.BB) as OBP,
            ((bs.H - bs.2B - bs.3B - bs.HR) + 2*bs.2B + 3*bs.3B + 4*bs.HR) / bs.AB as SLG,
            bs.G,
            bs.R,
            bs.RBI,
            bs.SB,
            bs.CS
    FROM 2014_bat_std bs
    INNER JOIN 2014_bat_ratio br ON bs.nameFull = br.nameFull AND bs.PA = br.PA
    WHERE bs.AB > 500 #bs.nameLast = 'Zunino'
    ORDER BY bs.AB DESC
;";

// Set Pitchers
$p_query = "SELECT                 
            ps.nameFirst,
            ps.nameLast,
            po.AB,
            po.H,
            po.2B,
            po.3B,
            po.HR,
            (po.H - po.2B - po.3B - po.HR) as 1B,
            po.BB,
            po.SO,
            po.PA,
            po.OBP,
            po.BA,
            pr.GB__FB as 'G/F',
            pr.IF__FB * 2 / 100 as 'PUpct',
            ps.G,
            ps.GS,
            ps.SV,
            ps.IP,
            ps.ERA,
            ps.W,
            ps.L
    FROM 2014_pitch_std ps
    INNER JOIN 2014_pitch_opp po ON ps.nameFull = po.nameFull AND ps.IP = po.IP
    INNER JOIN 2014_pitch_ratio pr ON ps.nameFull = pr.nameFull AND ps.IP = pr.IP
    WHERE ps.G > 70 #ps.nameFull = 'Madison Bumgarner' 
    ORDER BY po.AB DESC
;";

$lg_averages = "
    SELECT  
	# Get weighted averages (straight averages will be heavy on the pitchers with 0/1/2 atbats, etc, treating them as a full player)
	SUM(BA*PA)/SUM(PA) as BA,
	SUM(OBP*PA)/SUM(PA) as OBP,
	SUM(OPS*PA)/SUM(PA) as OPS,
        # per 600 atbats is the standard
	SUM(HR)/(SUM(PA)/600) as HR,
	SUM(2B)/(SUM(PA)/600) as 2B,
	SUM(3B)/(SUM(PA)/600) as 3B,
	SUM(SB)/(SUM(PA)/600) as Sb,
	SUM(CS)/(SUM(PA)/600) as Cs,
	SUM(H)/(SUM(PA)/600) as H,
	COUNT(*) 
FROM mlb.2014_bat_std
";
