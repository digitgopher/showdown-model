#MLB Showdown

> "MLB Showdown, meet Sabermetrics"

This is the thought that started it all: MLB Showdown cards should be *realistic* representations of the players they represent. Not just based on season statistics, but their play should be as statistically accurate , as true-to-life, as possible.

In so doing, game design will be enhanced with more-lifelike players. Additionally, statistical accuracy may lead to innovative card sets.

(Note: There is an elegant beauty in producing cards from a well-designed algorithm. One of the most difficult concepts in creating cards is 'cutting small differences'. Many cards look very close. Should the differences be defined by the *human eye and gut feelings* or by the *process*? For the first time, this has become a choice.)

Developed alongside this project is a [game statistics simulator](https://github.com/digitgopher/showdown-sim). Additionally,  [card data](https://github.com/digitgopher/showdown-data) was compiled in an easy-to-use format.

##### Status
[Detailed model description.](model.md)
- Card charts (distributing the 1-20 values): *Implemented*
- Points: *TBD*
- Speed: *TBD*
- Fielding: *TBD*
- IP: *TBD*


##### More About MLB Showdown
Play baseball with trading cards, where 20 sided dice are rolled to determine the result of an at-bat. See the [rules][1] of the [game][2]. Example [pitcher][4] and [position player][3] cards.

[1]: http://www.geocities.ws/mlbshowdown/rulebook.html
[2]: http://en.wikipedia.org/wiki/MLB_Showdown
[3]: http://www.showdowncards.com/images/product/1.jpg
[4]: http://www.showdowncards.com/images/product/5.jpg

##### Usage

Really? You want to run this script?? Ok!

- Required: [showdown-data](https://github.com/digitgopher/showdown-data), specifically `cards-tables.sql` for card data and `[yyyy]_all.sql` for MLB season data.
- Highly Encouraged: Use R and required libraries (truncnorm, rjson, RMySQL). Set `$pathToRExecutable` in `formula.php`. Optional because simulation will run without R if `single` is used for `num_opp`. In this case a distribution of opponents won't be available, as there is only one defined opponent card representing a 'standard' batter/pitcher.

```
    php formula.php u p [ dist [ num_opp ] ]
        u = MySQL username
        p = MySQL password
        dist = Method to generate random opponents. Default (and only recommended option) is 'discrete'. Options:'discrete','continuous','single'.
        num_opp = Number of opponents of each kind to generate. Default is 200. If 'single' is chosen num_opp is always 1.
```
*Output is very raw. The results format is still under development, see [releases](https://github.com/digitgopher/MLBShowdownStatistics/releases) for prototypes to date.*
