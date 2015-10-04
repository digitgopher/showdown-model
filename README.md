#MLB Showdown

> "MLB Showdown, meet Sabermetrics"

This project makes MLB Showdown cards that are *realistic* representations of players. Season metrics are transformed into statistically accurate cards that play as true-to-life as possible.

[Detailed model description.](model.md)

#### Status

- Card charts (distributing the 1-20 values): *Implemented, but still under development.*
- Points: *TBD*
- Speed: *TBD*
- Fielding: *TBD*
- IP: *TBD*

#### Usage

```
    php formula.php u p db Rpath [ dist [ num_opp ] ]
        u = MySQL username
        p = MySQL password
        db = database name
        Rpath = path to R executable
        dist = Method to generate random opponents. Default (and only recommended option) is 'discrete'. Options:'discrete','continuous','single'.
        num_opp = Number of opponents of each kind to generate. Default is 200. If 'single' is chosen num_opp is always 1.
```

- Data is found [here](https://github.com/digitgopher/showdown-data), specifically `cards-tables.sql` for card data and `[yyyy]_all.sql` for MLB season data.
- R and required libraries (truncnorm, rjson, RMySQL) used in the default case. If `single` is used for `num_opp`, R is not needed so pass a dummy argument. In this case a distribution of opponents won't be available, as there is only one defined opponent card representing a 'standard' batter/pitcher.

#### Premises

- Game design is enhanced with increasingly-lifelike players.
- Statistical accuracy leads to innovative card sets.
- There is an elegant beauty in producing cards from a well-designed algorithm.
- One of the most difficult concepts in chart creation is 'cutting small differences'. Many cards look very close. The differences should be defined by a *technical process* rather than the *human eye and gut feelings*.

#### Related

Developed alongside this project are a [card data visualization and a game statistics simulator](http://digitgopher.github.io/showdown-app/). Additionally, [card data](https://github.com/digitgopher/showdown-data/releases) was compiled in an easy-to-use format.


#### About MLB Showdown

Play baseball with trading cards, where 20 sided dice are rolled to determine the result of an at-bat. See the [rules][1] of the [game][2]. Example [pitcher][4] and [position player][3] cards.

[1]: http://www.geocities.ws/mlbshowdown/rulebook.html
[2]: http://en.wikipedia.org/wiki/MLB_Showdown
[3]: http://www.showdowncards.com/images/product/1.jpg
[4]: http://www.showdowncards.com/images/product/5.jpg
