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

&nbsp;

## Intro to the concept

Wouldn't MLB Showdown be more fun if we had more assurance that the cards were realistic representations of their real-life counterparts? That is the premise of this project.

Similarly, a working hypothesis is that more statistical accuracy leads to better game design. Variety does not necessarily have to be sacrificed, though the jury is still out on whether this would instead make for a 'more boring' game than that produced by tuning the cards per what 'feels right'. Perhaps additional tuning will be needed. Admittedly this is an important subject, and results have yet to be seen.

One of the most difficult concepts in creating cards is cutting small differences. Many players' cards look very close. Let's let the simulation and calculations decide what is most accurate, as they can do the job better than the human eye/gut. There is a beauty in the output of the algorithm.

The goal is to produce cards in the style of the 2000/2001/Colby-Tallafuss-and-team-created cards.

## Acronyms and Definitions

| PA | Plate Appearances |
| --- | --- |
| OB | On-Base |
| C | Control |
| PU | Pop-up |
| SO | Strikeout |
| GB | Ground ball out |
| FB | Fly ball out |
| BB | Walk |
| 1B | Single |
| 1B+ | Single+ |
| 2B | Double |
| 3B | Triple |
| HR | Homerun |
| pt. | points |
| Slot | A position on a player's Showdown card chart, corresponding to one roll of the die. Hence there are 20 slots on a player's chart. |

&nbsp;

## Calculating Charts: Distributing the 20 values and OB/Control

The developed model consists of five steps.

Throughout the model, a key concept is that a batter with OB 7 and 2 outs on his chart could be better than a batter with OB 9 and 7 outs on his chart. High OB is not necessarily better – look at the whole chance of getting on base.

**Step 1: Obtain the season statistics of the specific players to represent as Showdown cards.**

Todo: Detail out which statistics are important.

**Step 2: Obtain a representative sample of players from the existing Showdown universe.**

- Get the average frequency for each chart result. For example: 5% of pitchers have 1 PU result, 60% have 2 PU results, and 35% have 3 PU results.&nbsp;
- Generate a large number (say 1000) of random players from the identified probabilities of each amount or results (i.e. an amount of result being 2 PU values on the chart). Normalize so the chart values sum to 20.&nbsp;

**Step 3: Given the number of possible out results on a card (i.e. 2 through 7 for batters, 14 through 18 for pitchers)**** 1 ****, calculate what a given player's card would be if it had that number of out results (i.e. what would be a batter's OB if it's card had two total out values on it? Three out values?).**

(Explain the OB/out tradeoff, and how we deal with it by determining whatever combination leads to the most accurate results)

To calculate a card, there are 2 steps:

1. 
**Calculate OB**** 2 ****.** &nbsp;

The chance of getting on-base can be modeled as:

&nbsp;

Equation 1:

OBP = (chance of batter's chart) \* (chance of getting on base on batter's chart) + (chance of pitcher's chart) \* (chance of getting on base on pitcher's chart)

&nbsp;

which in turn gives:

&nbsp;

Equation 2:

OBP = (OB-C)/20\*(20-NumOutsOnBatter'sChart)/20 + (20-(OB-C))/20\*(20-NumOutsOnPitcher'sChart)/20

&nbsp;

OBP is known from the actual season statistic. From the generated random pitcher set, we have Control and pitcher chart information. Assuming number of outs on the batter's chart, the only variable left is OB, for which we solve the equation.

&nbsp;

1. Calculate precise number of slots for each chart result.&nbsp;

The key is expressing the season results in terms of plate appearances, which is easily modeled in Showdown. We can determine in terms of plate appearances how often a player achieved each possible result:

| **Card Slot** | **Statistic** |
| --- | --- |
| SO | strikeouts |
| GB | 
o\*r/(1+r)3
 |
| FB | 
o - GBstatistic4
 |
| BB | walks |
| 1B | singles |
| 1B+ | - |
| 2B | doubles |
| 3B | triples |
| HR | homeruns |
| **20** | **Plate Appearances** |

&nbsp;

The number of available card slots is 20, and all the statistics together will sum to equal Plate Appearances. Note that there is no realistic analog for single+.

&nbsp;

We compute the number of slots assigned to each value in a similar fashion to the way we solved for OB.

&nbsp;

Equation 3:

SeasonCount / PlateApperances = (chance of batter's chart) \* (chance of getting that result on batter's chart) + (chance of pitcher's chart) \* (chance of getting that result on pitcher's chart)

&nbsp;

An example for doubles:

Equation 4:

Doubles/PlateAppearances = (OB-C)/20\*NumDoublesOnBattersChart/20 + (20-(OB-C))/20\*NumDoublesOnPitchersChart/20

&nbsp;

Again, we know everything except NumDoublesOnBattersChart, so we can solve. This is done for all statistics. Since all the input statistics add to Plate Appearances, we end up with precise values for each slot (that add to 20 as they should). Remember that we have many pitcher opponents to calculate against, so we take the averages in the end.

&nbsp;

_Ex:_

_The input is 1) the set of 'average' pitcher cards, and 2) the number of outs on the batter's card to be calculated. An example of a result:_

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

_The values correspond to how the slots should be divided on the chart with the given number of outs._

&nbsp;

1. Round chart results&nbsp;

Turn the precise values into results we could put on a chart. Since you can't put 2.34 SO results on a chart, the computed values must be rounded, in this case leading to a printed SO chart value of "1-2"5. Note that this is the main way of obtaining 1B+ results. If a batter's chart rounds to less than 20, if they have a decent number of 1B and 2B results, they may be allotted 1B+ result(s).6 In any case, if the chart does not round to 20, the most appropriate values7 are adjusted.

**Step 4: Choose the OB/out (or C/out) combination that, after the card is calculated, leads to the least un-accurate card.**

Essentially this is answering the question "with the rounded chart values, how far off are the expected results (i.e. the percent difference between real-life season and expected Showdown 2B/PA)?"

If we used 2 through 7 outs as possibilities for batters, each batter will have 6 possible cards. Each card's total error is calculated, and the one with the least error is selected.

Ex: &nbsp;_Batter season HR/PA = .0315._

&nbsp; &nbsp; &nbsp;_Batter OB = 8_

_&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Batter HR results = 3_

_&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Pitcher C = 4__8_

_&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Pitcher HR results = 0_

_&nbsp; &nbsp; &nbsp; &nbsp; Using Equation 4: _

_&nbsp; &nbsp; &nbsp; &nbsp; Homeruns/PlateAppearances = (8 - 4)/20\*3/20 + (20 - (8 - 4))/20\*0/20 = .03_

**&nbsp; &nbsp; &nbsp; &nbsp;** _Error = |.03 - .0315| = .0015_

_&nbsp; &nbsp; &nbsp; &nbsp; If the other 7 results on the batter's chart __9__ also have an error of .0015, the total error is 8\*.0015 &nbsp; &nbsp; &nbsp; &nbsp; = .012_

_&nbsp; &nbsp; &nbsp; &nbsp; __The .012 value is compared the similar value of each of the other 5 options._

**Step 5: Perform the entire process multiple times and choose the charts that are created most often.**

The main uncertainty in the model comes from Step 2, creating random representative players. &nbsp;In Steps 3 and 4, direct calculations are used, but their result is only as accurate as the sample obtained in Step 2. For this reason, not only is a large sample needed, but the entire process will need to be performed multiple times in order to obtain the result that achieves the cards with overall best performance.10

&nbsp;

## Calculating Defense & Speed

TBD

## Calculating IP

TBD

## Calculating Points

TBD

&nbsp;

## Appendix 1: What's the big deal with On-base?

Though the term OOB should be used when dealing with pitchers, for simplicity we will universally use the term OBP.

In thinking about how to determine a pitcher's control number, a few questions have to be asked.

What is the point of control? The control number determines how often the swing is rolled on the pitcher's chart. Generally, if the pitcher has advantage the batter will be out, and if the batter has advantage he will get on base. When considering exact number of outs on each chart, the scenarios expound, but the advantage to the added complexity is greater granularity created by the increased combination of chart interactions. Essentially, control, equates to OOB. More precisely, control, along with the number of outs on the pitcher's chart, combined with Onbase and number of outs on batter's chart provides an exact measure of OBP.

Here are some sample statistics from existing sets of batters:

| **Set** | **Count** | **OB Avg** | **OB Stddev** | **Outs Avg** | **Outs Stddev** |
| --- | --- | --- | --- | --- | --- |
| Sample of 2000 Base Set | 75 | 7.31 | 1.20 | 3.85 | 0.96 |
| Sample of 2001 Base Set | 53 | 8.11 | 1.09 | 4.02 | 0.86 |
| Sample of 2013 Set | 315 | 6.99 | 1.58 | 4.37 | 1.37 |
| **Averages** | &nbsp; | **7.47** | **1.29** | **4.08** | **1.06** |

&nbsp;

A normal distribution mean of 7.47 and sd of 1.29 produces the following percentiles:11

 ![](data:image/*;base64,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)

&nbsp;

Based simply on the chart above, we can determine how many of each Onbase numbers are in an average set.

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

&nbsp;

Here are the percentiles for a pitcher outs distribution, based on "normal distribution, mean=4.08, sd=1.06".12 Percentiles:

 ![](data:image/*;base64,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)

&nbsp;

Below is the ranking of pitcher charts, sorted by OBP – the best pitcher is at the top, the worst at the bottom. The combination of pitcher charts with control 0 – 6 and 12 – 19 outs on chart are included. Admittedly, this is a liberal number of chart combinations. Charts like [2,12] and [5,19] may be uncommon, but there isn't really a reason to exclude them for now. The ridiculous four charts [0,19] [1,19] [6,12] and [5,12] are excluded.

An explanation of each column pair:

- Distributed Batters: Each pitcher's OOB was calculated from facing 3,000 random batters, generated according to the distribution as described above. The red highlighted text shows which charts are in a different ordering when compared to Single Batters.&nbsp;
- Single Batters: Each pitcher's OOB was calculated from facing a single set of 48 batters, representing a standard set of possibilities of charts to face. The combination of Onbase 4 – 11 and 1 – 6 outs on chart made the set of 48 batters.&nbsp;
- Weak Batters: The same as Single Batters, except only Onbase 4-7 were used, so 24 batters in total.&nbsp;
- Strong Batters: The same as Single Batters, except only Onbase 8-11 were used, so 24 batters in total.&nbsp;

| **Distributed Batters** | **Single Batters** | **Weak Batters** | **Strong Batters** |
| --- | --- | --- | --- |
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

&nbsp;

Weak/Strong Batters categories are only included so the change in pitcher performance can be observed. Observation: In a relative sense, low control higher out pitchers are better against lower onbase batters. This is may be intuitive, but the empirical results leave no doubt.

Another concept to be considered is playing by NL rules, where the pitcher comes to bat. In this situation, a [5,14] pitcher is much more desirable than a [1,17] pitcher, even though they both have a 0.369 OOB against a straight-average player set (one each of 48 batter charts, OB 4-11 outs 1-6). For example, against a somewhat average [3,15] pitcher the two pitchers in question will hit 0.255 and 0.250 respectively. Even against a lower-tier [0,15] pitcher, they will go 0.263 and 0.245 respectively. Which such a small difference, the value of a pitcher batting can be safely ignored.
