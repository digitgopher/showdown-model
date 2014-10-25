<?php
// Double entries for Angels,Marlins,Montreal,philly?
$teams = ['ANA','ARI','ATL','BAL','BOS','CHA','CHN','CIN','CLE','COL',
          'DET','FLO','HOU','KCA','LAA','LAN','MIA','MIL','MIN','MON',
          'NYA','NYN','OAK','PHI','PHN','PIT','SDN','SEA','SFN','SLN',
          'TBA','TEX','TOR','WAS'];


// Get batters
$b_query = "SELECT
            m.nameFirst,
            m.nameLast,
            b.AB,
            b.H,
            b.2B,
            b.3B,
            b.HR,
            (b.H - b.2B - b.3B - b.HR) as 1B,
            b.BB,
            b.SO,
            br.GB__FB as `G/F`,
            (b.AB + b.BB) as PA,
            b.H / b.AB as Average,
            (b.H + b.BB) / (b.AB + b.BB) as OBP,
            ((b.H - b.2B - b.3B - b.HR) + 2*b.2B + 3*b.3B + 4*b.HR) / b.AB as SLG,
            b.G,
            b.R,
            b.RBI,
            b.SB,
            b.CS
    FROM master m
    INNER JOIN batting b ON m.playerID = b.playerID
    INNER JOIN br_batters_2013 br ON m.nameConcat = br.nameFull AND b.teamID = br.team
    WHERE b.yearID = '2013' AND b.AB > 100
    ORDER BY b.AB DESC
;";

// Set Pitchers
$p_query = "select                 
            m.nameFirst,
            m.nameLast,
            pba.AB,
            pba.H,
            pba.2B,
            pba.3B,
            pba.HR,
            (pba.H - pba.2B - pba.3B - pba.HR) as 1B,
            pba.BB,
            pba.SO,
            pba.PA,
            pba.OBP,
            pba.BA,
            pr.GB__FB as 'G/F',
            pr.IF__FB * 2 / 100 as 'PUpct',
            p.G,
            p.GS,
            p.SV,
            p.IPouts,
            p.ERA,
            p.W,
            p.L
    FROM master m
    INNER JOIN pitching p ON m.playerID = p.playerID
    INNER JOIN br_pitchers_ba_2013 pba ON m.nameConcat = pba.nameFull AND p.teamID = pba.team
    INNER JOIN br_pitchers_ratio_2013 pr ON m.nameConcat = pr.nameFull AND p.teamID = pr.team
    WHERE p.yearID = '2013' AND p.G > 9
    ORDER BY pba.AB DESC
;";
