<?php
// Double entries for Angels,Marlins,Montreal,philly?
$teams = ['ANA','ARI','ATL','BAL','BOS','CHA','CHN','CIN','CLE','COL',
          'DET','FLO','HOU','KCA','LAA','LAN','MIA','MIL','MIN','MON',
          'NYA','NYN','OAK','PHI','PHN','PIT','SDN','SEA','SFN','SLN',
          'TBA','TEX','TOR','WAS'];


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
    FROM 2013_bat_std bs
    INNER JOIN 2013_bat_ratio br ON bs.nameFull = br.nameFull AND bs.PA = br.PA
    WHERE bs.AB > 100
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
    FROM 2013_pitch_std ps
    INNER JOIN 2013_pitch_opp po ON ps.nameFull = po.nameFull AND ps.IP = po.IP
    INNER JOIN 2013_pitch_ratio pr ON ps.nameFull = pr.nameFull AND ps.IP = pr.IP
    WHERE ps.G > 9
    ORDER BY po.AB DESC
;";
