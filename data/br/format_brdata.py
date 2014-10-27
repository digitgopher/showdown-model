# -*- coding: utf-8 -*-
"""
Created on Fri Oct 24 2014

@author: Daniel Tixier
Turns raw data downloaded from baseball-reference into an sql-friendly format, using regex.
"""
import re

fnames = ["leagues_MLB_2014-batting-pitching_players_batting_pitching.csv",
          "leagues_MLB_2014-ratio-batting_players_ratio_batting.csv",
          "leagues_MLB_2014-ratio-pitching_players_ratio_pitching.csv",
          "leagues_MLB_2014-standard-batting_players_standard_batting.csv",
          "leagues_MLB_2014-standard-pitching_players_standard_pitching.csv"]
for name in fnames:
    # Get the raw data
    with open(name, "r") as f:
        data = f.read()
    # Turn it into sql syntax
    data = re.sub(",([\*/0-9D]*)(\n|\Z)",r',"\1"\2',data) # Only use this line for files with player position data as the last col
    data = re.sub("\nRk,Name,Age.*","",data) #python doesn't need /r/n
    data = re.sub("\n","",data,1) # Replace initial newline
    data = re.sub("\n.*LgAvg[ a-zA-Z0-9].*","",data)
    data = data.replace("*,",",L,")
    data = data.replace("#,",",S,")
    data = re.sub(",([a-zA-Z\.'-]*) ([- a-zA-Z\.']*),([0-9])",r",\1 \2,R,\3",data)
    data = data.replace("%","")
    data = re.sub(",([a-zA-Z\.'-]*) ([- a-zA-Z\.']*),",r",\1 \2,\1,\2,",data)
    data = data.replace(",.",",0.")
    data = re.sub(",([ a-zA-Z\.'-]{2,}|[ a-zA-Z\.']+)",r",'\1'",data)
    data = re.sub("([a-zA-Z]{1})(')([a-zA-Z]{1})",r"\1\\\2\3",data)
    data = re.sub("\n",r"),\n(",data)
    data = data.replace(",,",",NULL,")
    data = data.replace(",,",",NULL,")
    data = data.replace(",)",",NULL)")
    data = re.sub("\A","(",data) # put parenthesis at beginning and end of file
    data = re.sub("\Z",")",data)
    
    # Write a new file
    foutputname = "conv_"+name
    with open(foutputname, "w") as g:
         g.write(data)