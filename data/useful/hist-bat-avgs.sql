SELECT 

yearID, 
avg(points),
avg(speed),
avg(onbase),
avg(SO1),
avg(GB1),
avg(FB1),
avg(SO1 + GB1 + FB1) as `NUM-OUT`,
avg(BB1),
avg(1B1),
avg(1Bplus1),
avg(2B1),
avg(3B1),
avg(HR1),
(20 - avg(SO1 + GB1 + FB1)) as `NUM-ONBASE`

FROM mlb.battercards Group by yearID;
