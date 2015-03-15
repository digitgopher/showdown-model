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

## Quantitative MLB Showdown Cards: A working model

Created by Daniel Tixier

Updated: 12/13/14

The implementation of the algorithm described in this model can be found at https://github.com/digitgopher/MLBShowdownStatistics


## Intro to the concept

The premise of this project asserts that MLB Showdown cards should be realistic representations of their player. Not just partly realistic, but as statistically accurate as possible. This is ambitious; the first project of its kind.

We do not mean to undermine game design, but rather enhance it. The goal is to give game designers another tool in their repertoire. In fact, it has already been shown that the quest for statistical accuracy may lead to innovative card sets. 

There is an undeniable beauty in producing cards solely based on a finely tuned and crafted algorithm. One of the most difficult concepts in creating cards is cutting small differences. Many cards look very close. Should the differences be defined by the human eye and gut feelings, or by the process? For the first time this has become an option.

## Background concepts and terminology

1. ***Slot***: A position on a player's Showdown card chart, corresponding to one roll of the die. There are 20 slots on a player's chart.

2. A 14-16 range on chart is fundamentally equal with a 12-14 displayed chart. Both have 3 slots.

3. A key concept to keep in mind is that a (7,2) batter (one with OB 7, and 2 out values on his chart), could be better than a (9,7) batter. All things equal, higher OB is obviously better. However, even one extra out value can have a significant impact on OBP. Similar for pitcher control.

4. ***Number of possible out results on a card.*** Initially defined as:

- 2 through 7 for batters
- 14 through 18 for pitchers
- That is, a batter can have between two and seven out values on his chart. These ranges are arbitrary, informed by the existing card domain.

5.There is no analog to a 1B+ result. The concept of the 1B+ feels “gamey”; they do not have a statistical equivalent. However, we still try to account for them. 

- They are actually given when the rounding of singles and doubles leaves room for them, this means that they do not correlate with the traditional “speedy guys get more 1B+ results” rule of thumb.  Admittedly, 1B+ results are given sparingly. 

## Calculating Charts: Distributing the 20 values and OB/Control

The developed model consists of five steps.

**Step 1**: Obtain the season statistics of the specific players to represent as Showdown cards.

**Step 2**: Obtain a *representative sample* of players from the existing Showdown universe.

1. Get the average frequency for *each* chart result (SO, GB, etc). 
	- Example: 5% of all pitchers have 1 PU result, 60% have 2 PU results, and 35% have 3 PU results.
2. Generate a large number (say 1000) of random players from the identified discrete result probabilities. Normalize so all chart values sum to 20.

**Step 3**: Take the range of *number of possible out results* on a card, and *calculate* what a given player’s **card would be** if it had that number of out results.

-  *What should a batter’s OB value be if its card had three total out values on it? How would those out values be distributed?*

There is an OB/out trade-off. We could decrease a batter's OB, but if we also turn some of his FB results into 1B results it may end up being a better card.

To calculate a card, there are 2 steps (We give the example of a batter, but a pitcher could be calculated by similar means):

1. **Calculate OB.** 
  The chance of getting on-base can be modeled as:

  > *Equation 1*
  > OBP = (chance of batter's chart) \* (chance of getting on base on batter's chart) + (chance of pitcher's chart) \* (chance of getting on base on pitcher's chart)

  which, translated to Showdown chart values, gives:

  > *Equation 2*
  > OBP = (OB-C)/20\*(20-NumOutsOnBatter'sChart)/20 + (20-(OB-C))/20\*(20-NumOutsOnPitcher'sChart)/20


  OBP is known from the actual season statistic. From the generated random pitcher set (**Step 2** above), we have Control and pitcher chart information. Assuming number of outs on the batter's chart, the only variable left is OB, for which we can solve.


2. **Calculate precise number of slots for each chart result.**

  The key here is expressing season results in terms of plate appearances, as this is most directly modeled in Showdown. 

  We can determine in terms of plate appearances how often a player achieved each possible result:

    | **Card Chart** | **Season Statistic** |
    | --- | --- |
    | SO | strikeouts |
    | GB | o\*r/(1+r)|
    | FB | o - GBseasonStatistic|
    | BB | walks |
    | 1B | singles |
    | 1B+ | - |
    | 2B | doubles |
    | 3B | triples |
    | HR | homeruns |
    | **20** | **Plate Appearances** |

  > Notes:
  > 1. r = ground-ball-to-fly-ball ratio, a published statistic.
  > 2. o = in-play outs = (atbats - strikeouts - hits).
  > 3. Alternative equations could be given regarding FB, but it is simplest to relate to the defined GB Statistic.
  > 4. There is no realistic analog for single+.

  The number of available card slots is 20. All the statistics together will sum to equal Plate Appearances, both in Showdown and the season statistics. Note this strategy rolls up other possibilities like fielders' choices, sacrifices, etc.

  We compute the number of slots assigned to each value in a similar fashion to the way we solved for OB:

  > Equation 3:
  > SeasonCount / PlateApperances = (chance of batter's chart) \* (chance of getting that result on batter's chart) + (chance of pitcher's chart) \* (chance of getting that result on pitcher's chart)

  Let's show an example for doubles:

  > Equation 4:
  > Doubles/PlateAppearances = (OB-C)/20\*NumDoublesOnBattersChart/20 + (20-(OB-C))/20\*NumDoublesOnPitchersChart/20

  Again, we know everything except `NumDoublesOnBattersChart`, so we can solve. This is done for all statistics. 

  Since all the input statistics add to Plate Appearances, we end up with precise values for each slot (that add to 20 as they should). Remember that there will be many pitcher opponents against which we are calculating, so they are averages in the end.

  Example:

  _The input is 1) the set of 'average' pitcher cards, and 2) the number of outs on the batter's card to be calculated. A result could be:_

  _[OB] => 9.077816_
  _[SO] => 5_
  _[GB] => 0_
  _[FB] => 0_
  _[BB] => 6.976638_
  _[1B] => 6.022451_
  _[1B+] => 0_
  _[2B] => 1.180976_
  _[3B] => 0_
  _[HR] => 0.819933_
  
  _The values correspond to how the slots should be divided on the chart with the given number of outs (five in this case; they happen to all be assigned to SO)._

3. **Round chart results.**

  Turn the precise values into results we could put on a printed chart. 

  Since you can't put 2.34 SO results on a chart, the computed values must be rounded, in this case the printed SO chart value could be "1-2". 

  However, in any case the most appropriate values (1B+ or otherwise) will be adjusted.
  
  Most appropriate values are defined as the values which were rounded the most. For example, if after rounding a chart adds to 19, then the value that is 1) *rounded down* and 2) *the closest to being rounded up*, is rounded up instead.


**Step 4**: Choose the OB/num-of-out (or Control/num-of-out) combination that, after the card is calculated, leads to the *least un-accurate card*. 

  Essentially this is answering the question "with the rounded chart values, how far off are the expected results?" (i.e. the percent difference between real-life season 2B/PA and expected Showdown 2B/PA)

  If we used two through seven outs as possibilities for batters, each batter will have 6 possible cards. Each card's total error is calculated, and the one with the least error is selected.

  Example:
   _Batter season HR/PA = .0315._
  _Batter OB = 8_
  _Batter HR results = 3_
  _Pitcher C = 4_ (Again, we actually calculate for all the pitchers in our sample and average the result. Only one pitcher is shown here for simplicity.)
  _Pitcher HR results = 0_

  _Using Equation 4 obtains: _
  > Homeruns/PlateAppearances = (8 - 4)/20\*3/20 + (20 - (8 - 4))/20\*0/20 = .03

  Thus,  _Error = |.03 - .0315| = .0015_

  _Say the other 7 results on the batter's chart also have an error of .0015, then the total error is 8\*.0015 = .012_

_The .012 value is then compared with the similar values of each of the other 5 options._


**Step 5**: Perform the entire process multiple times and choose the chart that is created most often for each player.

  The uncertainty in the model comes from Step 2 with the creation of random representative players. In Steps 3 and 4, direct calculations are used, but their result is only as accurate as the sample obtained in Step 2. For this reason, the entire process should be performed multiple times in order to obtain the results that achieve the cards with overall best performance. 

(Best performance defined as the card that, when played, produces statistics similar to those produced during the season that the card is supposed to represent.)

&nbsp;

## Calculating Defense & Speed

TBD

## Calculating IP

TBD

## Calculating Points

TBD


## Appendix 1: What's the big deal with On-base?

Though the term OOB should be used when dealing with pitchers, for simplicity we will universally use the term OBP.

In thinking about how to determine a pitcher's control number, a few questions have to be asked.

What is the point of control? The control number determines how often the swing is rolled on the pitcher's chart. Generally, if the pitcher has advantage the batter will be out, and if the batter has advantage he will get on base. 

When considering exact number of outs on each chart, the scenarios expand and intermix. The advantage to this added complexity is greater granularity created by the increased combination of chart interactions. 

Irregardless, essentially control equates to OOB. More precisely, control, along with the number of outs on the pitcher's chart, combined with OB and number of outs on batter's chart provides an exact measure of OBP.

Here are some sample statistics from existing sets of batters:

| **Set** | **Count** | **OB Avg** | **OB Stddev** | **Outs Avg** | **Outs Stddev** |
| --- | --- | --- | --- | --- | --- |
| Sample of 2000 Base Set | 75 | 7.31 | 1.20 | 3.85 | 0.96 |
| Sample of 2001 Base Set | 53 | 8.11 | 1.09 | 4.02 | 0.86 |
| Sample of 2013 Set | 315 | 6.99 | 1.58 | 4.37 | 1.37 |
| **Averages** | &nbsp; | **7.47** | **1.29** | **4.08** | **1.06** |

&nbsp;

A normal distribution mean of 7.47 and sd of 1.29 produces the following percentiles:

(Insert chart here)

&nbsp;

Based simply on the chart above, we can determine how many of each OB numbers would be in an average set.

| **Onbase** | **Percentile for OB + .5** | **Count in a set of 100 cards** |
| --- | --- | --- |
| 4 | 1 | 1 |
| 5 | 7 | 6 |
| 6 | 23 | 16 |
| 7 | 50 | 27 |
| 8 | 77 | 27 |
| 9 | 94 | 17 |
| 10 | 99 | 5 |
| 11 | 100 | 1 |


Here are the percentiles for a pitcher outs distribution, based on "normal distribution, mean=4.08, sd=1.06". Percentiles:

(insert chart here)

Below is the ranking of pitcher charts, sorted by OBP – the best pitcher is at the top, the worst at the bottom. The combination of pitcher charts with control 0 – 6 and 12 – 19 outs on chart are included. 

Admittedly, this is a liberal number of chart combinations. Charts like [2,12] and [5,19] may be uncommon. However, there isn't really a reason to exclude them for now, though the ridiculous four charts [0,19] [1,19] [6,12] and [5,12] are excluded.

An explanation of each column pair:

- Distributed Batters: Each pitcher's OOB was calculated from facing 3,000 random batters, generated according to the distribution as described above. The red highlighted text shows which charts are in a different ordering when compared to Single Batters.
- Single Batters: Each pitcher's OOB was calculated from facing a single set of 48 batters, representing a standard set of possibilities of charts to face. The combination of Onbase 4 – 11 and 1 – 6 outs on chart made the set of 48 batters.
- Weak Batters: The same as Single Batters, except only Onbase 4-7 were used, so 24 batters in total.
- Strong Batters: The same as Single Batters, except only Onbase 8-11 were used, so 24 batters in total.

| **Distributed** | **** | **Single** | **** | **Weak** | **** | **Strong** | **** |
| --- | --- | --- | --- | --- | --- | --- | --- |
| **Chart** | **OOB** | **Chart** | **OOB** | **Chart** | **OOB** | **Chart** | **OOB** |
| [6,19] | 0.107 | [6,19] | 0.122 | [6,19] | 0.060 | [6,19] | 0.185 |
| [5,19] | 0.142 | [5,19] | 0.151 | [5,19] | 0.079 | [5,19] | 0.224 |
| [6,18] | 0.153 | [6,18] | 0.168 | [4,19] | 0.108 | [6,18] | 0.226 |
| [4,19] | 0.179 | [4,19] | 0.185 | [6,18] | 0.109 | [4,19] | 0.262 |
| [5,18] | 0.186 | [5,18] | 0.195 | [5,18] | 0.127 | [5,18] | 0.263 |
| [6,17] | 0.200 | [6,17] | 0.213 | [3,19] | 0.146 | [6,17] | 0.268 |
| [3,19] | 0.216 | [3,19] | 0.224 | [4,18] | 0.154 | [4,18] | 0.299 |
| [4,18] | 0.220 | [4,18] | 0.227 | [6,17] | 0.158 | [3,19] | 0.301 |
| [5,17] | 0.230 | [5,17] | 0.238 | [5,17] | 0.175 | [5,17] | 0.301 |
| [6,16] | 0.246 | [6,16] | 0.258 | [2,19] | 0.185 | [6,16] | 0.309 |
| [2,19] | 0.253 | [2,19] | 0.263 | [3,18] | 0.190 | [3,18] | 0.335 |
| [3,18] | 0.255 | [3,18] | 0.263 | [4,17] | 0.200 | [4,17] | 0.335 |
| [4,17] | 0.262 | [4,17] | 0.268 | [6,16] | 0.208 | [2,19] | 0.340 |
| [5,16] | 0.274 | [5,16] | 0.282 | [5,16] | 0.223 | [5,16] | 0.340 |
| [2,18] | 0.290 | [2,18] | 0.299 | [2,18] | 0.226 | [6,15] | 0.350 |
| [6,15] | 0.292 | [3,17] | 0.301 | [3,17] | 0.234 | [3,17] | 0.369 |
| [3,17] | 0.294 | [6,15] | 0.304 | [4,16] | 0.247 | [2,18] | 0.371 |
| [4,16] | 0.303 | [4,16] | 0.309 | [6,15] | 0.257 | [4,16] | 0.371 |
| [5,15] | 0.317 | [5,15] | 0.325 | [1,18] | 0.263 | [5,15] | 0.379 |
| [1,18] | 0.324 | [1,18] | 0.335 | [2,17] | 0.268 | [6,14] | 0.391 |
| [2,17] | 0.326 | [2,17] | 0.335 | [5,15] | 0.271 | [2,17] | 0.402 |
| [3,16] | 0.333 | [3,16] | 0.340 | [3,16] | 0.278 | [3,16] | 0.402 |
| [6,14] | 0.338 | [6,14] | 0.349 | [4,15] | 0.293 | [1,18] | 0.407 |
| [4,15] | 0.344 | [4,15] | 0.350 | [0,18] | 0.299 | [4,15] | 0.407 |
| [1,17] | 0.358 | [5,14] | 0.369 | [1,17] | 0.301 | [5,14] | 0.418 |
| [0,18] | 0.359 | [1,17] | 0.369 | [6,14] | 0.306 | [6,13] | 0.433 |
| [5,14] | 0.361 | [0,18] | 0.371 | [2,16] | 0.309 | [2,16] | 0.433 |
| [2,16] | 0.362 | [2,16] | 0.371 | [5,14] | 0.319 | [1,17] | 0.436 |
| [3,15] | 0.372 | [3,15] | 0.379 | [3,15] | 0.321 | [3,15] | 0.436 |
| [6,13] | 0.384 | [4,14] | 0.392 | [0,17] | 0.335 | [0,18] | 0.443 |
| [4,14] | 0.386 | [6,13] | 0.394 | [4,14] | 0.339 | [4,14] | 0.444 |
| [0,17] | 0.390 | [0,17] | 0.403 | [1,16] | 0.340 | [5,13] | 0.456 |
| [1,16] | 0.392 | [1,16] | 0.403 | [2,15] | 0.350 | [1,16] | 0.465 |
| [2,15] | 0.399 | [2,15] | 0.408 | [6,13] | 0.356 | [2,15] | 0.465 |
| [5,13] | 0.405 | [5,13] | 0.412 | [3,14] | 0.365 | [0,17] | 0.469 |
| [3,14] | 0.410 | [3,14] | 0.418 | [5,13] | 0.368 | [3,14] | 0.470 |
| [0,16] | 0.422 | [4,13] | 0.433 | [0,16] | 0.371 | [4,13] | 0.480 |
| [1,15] | 0.426 | [0,16] | 0.434 | [1,15] | 0.379 | [1,15] | 0.493 |
| [4,13] | 0.427 | [1,15] | 0.436 | [4,13] | 0.385 | [0,16] | 0.496 |
| [2,14] | 0.435 | [2,14] | 0.444 | [2,14] | 0.391 | [2,14] | 0.496 |
| [3,13] | 0.449 | [3,13] | 0.456 | [0,15] | 0.407 | [3,13] | 0.504 |
| [0,15] | 0.453 | [0,15] | 0.465 | [3,13] | 0.409 | [4,12] | 0.516 |
| [1,14] | 0.460 | [1,14] | 0.470 | [1,14] | 0.418 | [0,15] | 0.522 |
| [4,12] | 0.468 | [4,12] | 0.474 | [4,12] | 0.432 | [1,14] | 0.522 |
| [2,13] | 0.471 | [2,13] | 0.480 | [2,13] | 0.433 | [2,13] | 0.527 |
| [0,14] | 0.484 | [3,12] | 0.495 | [0,14] | 0.444 | [3,12] | 0.537 |
| [3,12] | 0.488 | [0,14] | 0.496 | [3,12] | 0.453 | [0,14] | 0.548 |
| [1,13] | 0.494 | [1,13] | 0.504 | [1,13] | 0.456 | [1,13] | 0.551 |
| [2,12] | 0.508 | [2,12] | 0.516 | [2,12] | 0.474 | [2,12] | 0.558 |
| [0,13] | 0.516 | [0,13] | 0.528 | [0,13] | 0.480 | [0,13] | 0.574 |
| [1,12] | 0.527 | [1,12] | 0.538 | [1,12] | 0.495 | [1,12] | 0.580 |
| [0,12] | 0.547 | [0,12] | 0.559 | [0,12] | 0.516 | [0,12] | 0.601 |


Weak/Strong Batters categories are only included so the change in pitcher performance can be observed. 

Observation: In a relative sense, low control higher out pitchers are better against lower onbase batters. This is may be intuitive, but the empirical data leaves no doubt.

Another concept to be considered is playing by NL rules, where the pitcher comes to bat. A [5,14] pitcher would seem much more desirable than a [1,17] pitcher, because he would have the chance of getting the advantage with more possibilites for getting on base. Both these charts have a 0.369 OOB against a straight-average player set (one each of 48 batter charts, OB 4-11 outs 1-6). 

In reality, pitcher batting isn't really a factor. Against a somewhat average [3,15] pitcher, the two pitchers in question will hit 0.255 and 0.250 respectively. Even against a lower-tier [0,15] pitcher, they will go 0.263 and 0.245 respectively. With such a small difference, the value of a pitcher batting can be safely ignored.
