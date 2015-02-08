#The MLB Showdown Statistics Project

Creating MLB Showdown card sets that play as true-to-life as possible.

Providing a [game statistics simulator](http://digitgopher.github.io/MLBShowdownStatistics/).

### Methodology to determine card charts (distributing the 1-20 values)
(Implemented in [formula.php](scripts/formula.php) and supporting scripts.)

**Step 1**: Obtain the season statistics of the specific players to represent as Showdown cards.

**Step 2**: Obtain a *representative sample* of players from the existing Showdown universe.

**Step 3**: Given the number of possible out results on a card (i.e. 2 through 7 for batters, 14 through 18 for pitchers), *calculate* what a given player’s **card would be** if it had that number of out results (i.e. what should a batter’s OB value be if it's card had three total out values on it? Additionally, how would those out values be distributed?).

**Step 4**: Choose the OB/num-of-out (or Control/num-of-out) combination that, after the card is calculated, leads to the *least un-accurate card*. 

**Step 5**: Perform the entire process multiple times and choose the chart that is created most often for each player.

### Other methodology: TBD
- Points
- Speed
- Fielding
- IP

### Data

- Season statistics data from Baseball Reference.
- Showdown card data compiled from various sources.

There are [various](data/showdown/process.vb) [helper](data/br/format_brdata.py) [functions](data/br/insert_brdata.py) (and useful regex notes) to convert raw data into usable MySQL tables.

###FAQ
Are you going to release the results of the model? *We are working towards this goal. See the [releases](https://github.com/digitgopher/MLBShowdownStatistics/releases) page for prototypes to date.*

Why are you using php on the command line? *Because at one point I wanted to, and it works.*

### About the game itself
Play a game of baseball with trading cards. In a nutshell: Roll a 20 sided die twice to determine the result of an at-bat.
Links: See the [rules][1] of the [game][2]. Example [pitcher][4] and [position player][3] cards.

[1]: http://www.geocities.ws/mlbshowdown/rulebook.html
[2]: http://en.wikipedia.org/wiki/MLB_Showdown
[3]: http://www.showdowncards.com/images/product/1.jpg
[4]: http://www.showdowncards.com/images/product/5.jpg
