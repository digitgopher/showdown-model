# -*- coding: utf-8 -*-
"""
Created on Sat Oct 25 2014

@author: Daniel Tixier
This script creates a sql file, which can then be run to add the data to the database.
"""
# Define names
skeletonFile = "2014_def.sql"
dataMapping = [["conv_leagues_MLB_2014-batting-pitching_players_batting_pitching.csv","Input Pitching Opposition data here"],
                 ["conv_leagues_MLB_2014-ratio-batting_players_ratio_batting.csv","Input Batting Ratios data here"],
                 ["conv_leagues_MLB_2014-ratio-pitching_players_ratio_pitching.csv","Input Pitching Ratios data here"],
                 ["conv_leagues_MLB_2014-standard-batting_players_standard_batting.csv","Input Batting Standard data here"],
                 ["conv_leagues_MLB_2014-standard-pitching_players_standard_pitching.csv","Input Pitching Standard data here"]]
foutputname = "2014_all.sql"

# Get skeleton
with open(skeletonFile, "r") as f:
    data = f.read()
    
# Insert data
for pair in dataMapping:
    with open(pair[0], "r") as f:
        curData = f.read()
    data = data.replace(pair[1], curData)

# Output file
with open(foutputname, "w") as f:
     f.write(data)