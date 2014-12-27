#The MLB Showdown Statistics Project

Creating MLB Showdown card sets that play as true-to-life as possible.

###How it works
The script [formula.php](scripts/formula.php) outputs MLB Showdown card charts based on actual season statistics.
Existing Showdown card data, as well as MLB season statistics, are stored in the data folder. There are also [various](data/showdown/process.vb) [helper](data/br/format_brdata.py) [functions](data/br/insert_brdata.py) to convert raw statistics into usable sql tables.

The sim folder contains a Showdown game simulator (developed [here](https://github.com/digitgopher/showdown-sim)) that can be found on the [project site](http://digitgopher.github.io/MLBShowdownStatistics/).
###FAQ
Sounds confusing. Are you going to release the results? *Yep, we are working on it.*

Any more details on how the model works? *Coming soon!*

Why are you using php on the command line? *Because at one point I wanted to, and it works.*

### About the game itself
Play a game of baseball with trading cards. In a nutshell: Roll a 20 sided die twice to determine the result of an at-bat.
Links: See the [rules][1] of the [game][2]. Example [pitcher][4] and [position player][3] cards.

[1]: http://www.geocities.ws/mlbshowdown/rulebook.html
[2]: http://en.wikipedia.org/wiki/MLB_Showdown
[3]: http://www.showdowncards.com/images/product/1.jpg
[4]: http://www.showdowncards.com/images/product/5.jpg
