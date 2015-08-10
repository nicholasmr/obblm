## Purpose ##

Overview of the extra stats being added for botocs and what is being measured.
Queries and issues can be added to this page and requests for extra stats can be in made in comments.

Some of the stats being collected should be joined together to form more interesting statistics (the rushing\_distance_stats should be summed into a single rushing\_distance stat)._

All successful stat counts include re-rolls, re-rolls themselves aren't specifically being counted. This would be possible but would require a lot more stats, e.g you would need to know roll fails, re-rolled fails, re-rolled successes, rolled successes.

I am assuming all player statistics will essentially form the basis for the team statistics so I didn't collect at the team level.

Things that could be added, inducement information

Goblin bombardiers:- I can work out how many bombs were thrown but the effect of the bombs is harder due to OR filtering and the effect of the bombs is a nested effect\_roll->next\_player->effect\_roll thing

**Special notation:**

The below list is also used by OBBLM to maintain its list of "Extra stats" (ES) fields. Because of this a special notation is applied to the field names, which goes like this:
  * "!" prefix denotes that the field is in a non-working order, and is ignored by OBBLM.
  * "+" prefix denotes that the field is a _common_ (not ES) field used in OBBLM.
  * "%" prefix denotes that the field is **NOT** a _scalar_, which is used by the module linking OBBLM with BOTOCS.


### Offensive stats ###


|pass\_attempts|cp\_a|Number of pass throw attempts of the ball.|
|:-------------|:----|:-----------------------------------------|
|+completion   |cp   |Number of completions of throws of the ball (+1 spp)|
|interceptions\_thrown|cp\_int|Number of times the thrower has been intercepted.|
|safe\_throws  |cp\_st|Number of times an interception was canceled by safe throw.|
|pass\_distance|cp\_dist|Number of squares progression the ball was thrown towards the endzone (this should be multiplied up to give number of paces (x5?)|
|dumpoff\_attempts|dmp\_a|Number of passes thrown which have been dumpoffs (this is informational, pass\_attempts includes dump offs).|
|dumpoff\_completions|dmp  |Number of completions from dump offs (as above this is for informational purposes, pass\_completions includes dump off completions).|
|catch\_attempts|catch\_a|Number of catch attempts made my a player from a throw.|
|catches       |catch|Number of catches made (including re-rolled).|
|handoffs      |hnd  |Number of hand offs this player has made  |
|handoffs\_received|hnd\_r\_a|Number of times this player has been handed off to.|
|handoff\_catches|hnd\_r|Number times this player caught a hand off (including re-rolled).|
|pickup\_attempts|pick\_a|Number of times attempting to pick the ball up.|
|pickups       |pick |Number of successful pick ups (including re-rolled).|



### Movement stats ###

All progression is counted while carrying the ball.

|rushing\_distance\_leap|rush\_dist\_lp|Squares of progression towards the end zone leaping with the ball.|
|:----------------------|:-------------|:-----------------------------------------------------------------|
|rushing\_distance\_push|rush\_dist\_p |quares of progression towards the end zone from pushes.           |
|rushing\_distance\_move|rush\_dist\_m |Squares of progression with the ball running towards the end zone in a normal move.|
|rushing\_distance\_block|rush\_dist\_b |Squares of progression towards the end zone from blocks/blitzes.  |
|rushing\_distance\_shadowing|rush\_dist\_sh|Squares of progression towards the end zone from shadowing.       |
|leap\_attempts         |lp\_a         |Number of leap attempts.                                          |
|leaps                  |lp            |Number of successful leaps (including re-rolled).                 |
|dodge\_attempts        |dg\_a         |Number of dodge attempts                                          |
|dodges                 |dg            |Number of successful dodges (including re-rolled)                 |
|blitz\_actions         |blz           |Number of times this player has blitzed.                          |
|gfi\_attempts          |gfi\_a        |Go for it attempts                                                |
|gfis                   |gfi           |Successful go for its.                                            |


### Blocking stats ###

Just to note inflicted counts here are based on the status of the player at the end of the everything, so if you cause a casuality and get a kill but the apoth is used to heal it, it doesnt count the death in the inflicted although it is still casualty of course.

|inflicted\_blocks|blk\_i|Number of times this player tried to throw a block.|
|:----------------|:-----|:--------------------------------------------------|
|inflicted\_defender\_downs|pow\_i|Number of times defender down was the selected result.|
|inflicted\_defender\_stumbles|stmbl\_i|Number of times defender stumbles was the selected result.|
|inflicted\_pushes|psh\_i|Number of times push was the selected result.      |
|inflicted\_both\_downs|both\_i|Number of times both down was the selected result. |
|inflicted\_attacker\_downs|skul\_i|Number of times attacker down was the selected result.|
|inflicted\_knock\_downs|dwns\_i|Number of times this player knocked the other player down blocking (as the attacker, the defends ends up down or as the defender the attacker ends up down).|
|inflicted\_strip\_balls|strp\_i|Number of times strip ball has been used by this player.|
|inflicted\_sacks |sack\_i|Number of times this player knocked the other player down blocking (as the attacker, the defends ends up down or as the defender the attacker ends up down), when that player was carrying the ball.|
|inflicted\_crowd\_surfs|surf\_i|Number of times the push result has ended up in as an injury roll (presuming from being crowd surfed).|
|inflicted\_stuns |st\_i |Number of times this player knocked the other player down blocking (as the attacker, the defends ends up down or as the defender the attacker ends up down) and that player ended up stunned.|
|inflicted\_kos   |ko\_i |Number of times this player knocked the other player down blocking (as the attacker, the defends ends up down or as the defender the attacker ends up down) and that player ended up KOed.|
|inflicted\_bhs   |bh\_i |Number of times this player knocked the other player down blocking (as the attacker, the defends ends up down or as the defender the attacker ends up down) and that player ended up badly hurt (after apoth).|
|inflicted\_sis   |si\_i |Number of times this player knocked the other player down blocking (as the attacker, the defends ends up down or as the defender the attacker ends up down) and that player ended up seriously injured (after apoth).|
|inflicted\_kills |ki\_i |Number of times this player knocked the other player down blocking (as the attacker, the defends ends up down or as the defender the attacker ends up down) and that player ended up dead (after apoth)|
|sustained\_blocks|blk\_s|Number of times this player has been blocked.      |
|sustained\_knocked\_downs|dwn\_s|Number this this player was knocked down while blocking either from sustaining a block or when throwing a block.|
|sustained\_sacks |sack\_s|Number this this player was knocked down while blocking either from sustaining a block or when throwing a block when carrying the ball.|
|sustained\_crowd\_surfs|surf\_s|Number of times this player has been pushed and required been required to make an injury roll (from crowd surfs).|
|sustained\_stuns |st\_s |Total number of times this player has been stunned (from any means). All these stats check player status at the end of the turn.|
|sustained\_kos   |ko\_s |Total number of times this player has been KOed (from any means).|
|sustained\_bhs   |bh\_s |Total number of times this player has been badly hurt (from any means).|
|sustained\_sis   |si\_s |Total number of times this player has been seriously injured (from any means).|
|sustained\_kill  |ki\_s |Total number of times this player has been killed (from any means)... this would only ever be 1!|


### Fouling stats ###

|inflicted\_fouls|fl\_i|Number of times this player has fouled another.|
|:---------------|:----|:----------------------------------------------|
|inflicted\_foul\_stuns|st\_fi|Number of times this player stunned another through fouling|
|inflicted\_foul\_kos|ko\_fi|Number of times this player knocked out another through fouling|
|inflicted\_foul\_bhs|bh\_fi|Number of times this player badly hurt another through fouling|
|inflicted\_foul\_sis|si\_fi|Number of times this player seriously injured another through fouling|
|inflicted\_foul\_kills|ki\_fi|Number of times this player killed another through fouling|
|sustained\_fouls|fl\_s|Number of times this player has been fouled.   |
|sustained\_ejections|ejct |Number of times this player was ejected for fouling.|


### Healing stats ###

|apothecary\_used|ap|Number of times the apoth has been used on this player|
|:---------------|:-|:-----------------------------------------------------|
|ko\_recovery\_attempts|ko\_ra|Number of recovery rolls from KOs                     |
|ko\_recoveries  |ko\_r|Number of successful KOs recoveries                   |
|thickskull\_used|thk|Number of times thick skull was used by this player.  |
|regeneration\_attempts|rgn\_a|Number of time this player attempted to regenerate.   |
|regenerations   |rgn|Number of times the regenerate roll succeeded.        |

### Kicking stats ###

Note: these are only recorded for a player with the kick skill. The only other thing I could try and get out of it might be direction kicked but it would be 8 more stats for not a whole lot.

|kickoffs|kck|Number of times this player kicked off|
|:-------|:--|:-------------------------------------|
|kick\_distance|kck\_dist|Distance the ball was kicked in squares.|

### Dice stats ###

Note: Might be fun to see how lucky a player is

|dice\_rolls|dice|Number of times this player rolled a simple roll or skill roll.|
|:----------|:---|:--------------------------------------------------------------|
|dice\_natural\_ones|1s  |Number of natural ones rolled.                                 |
|dice\_natural\_sixes|6s  |Number of natural sixes rolled.                                |
|dice\_target\_sum|dice\_trg|Sum of the total targets required.                             |
|dice\_roll\_sum|dice\_sum|Sum of what was actually rolled (with above would be used to show averages).|


### SPPs stats ###

The improvement roll info should be used to filter the list of skills for selection by a player on the team roster page

|+interception|intcpt|Number of times this player intercepted the ball.|
|:------------|:-----|:------------------------------------------------|
|+casualties  |cas   |Number of casualties caused earning spp.         |
|+touchdown   |td    |Number of touchdowns this player scored.         |
|%injuries    |inj   |Injuried sustained by this player.               |
|+mvp         |mvp   |1 if this player was MVP                         |
|+inflicted\_bh\_spp\_casualties|bh    |Used to count badly hurts which counted as a spp casuality|
|+inflicted\_si\_spp\_casualties|si    |Used to count serious injuries which counted as a spp casuality|
|+inflicted\_kill\_spp\_casualties|ki    |Used to count deaths which counted as a spp casuality|
|+improvement\_roll1|ir1\_d1,ir2\_d1,ir3\_d1|The skill up improvement roll (d1)               |
|+improvement\_roll2|ir1\_d2,ir2\_d2,ir3\_d2|The skill up mprovement roll (d2)                |


### Big Guy stats ###

|big\_guy\_stupidity\_attempts|big\_stp\_a|Number of rolls for really stupid, bonehead, take root and wild animal.|
|:----------------------------|:----------|:----------------------------------------------------------------------|
|big\_guy\_stupidity\_successes|big\_stp   |Number of times the really stupid, bonehead, take root and wild animal roll succeeded.|
|big\_guy\_stupidity\_blitz\_attempts|big\_bltz\_a|Number of times this big guy declared a blitz                          |
|big\_guy\_stupidity\_blitz\_successes|big\_bltz  |Number of times this big guy was able to blitz                         |
|throw\_team\_mate\_attempts  |TTM\_a     |Number of attempts to throw a team mate by this player                 |
|throw\_team\_mate\_successes |TTM        |Number of times this player successfully threw a team mate.            |
|throw\_team\_mate\_distance  |TTM\_dist  |How far this player has thrown team mates in squares.                  |
|throw\_team\_mate\_to\_safe\_landing|TTM\_landed|Number of times this player successfully threw a team mate and the thrown player landed.|

### Right Stuff stats ###
|times\_thrown|RS\_thrn|Number of times this player has been thrown|
|:------------|:-------|:------------------------------------------|
|landing\_attempts|RS\_land\_a|Number of times this player has attempted to land|
|landings     |RS\_land|Number of times this player successfully landed.|
|distance\_thrown|RS\_dist|The distance this player has been thrown   |
|rushing\_distance\_thrown|RS\_rush\_dist|The distance the ball progressed towards the end zone when this player was thrown (should be added to rushing distance total stat)|


### Vampire stats ###

The inflicted stats aren't working atm

|bloodlust\_rolls|bldlst\_a|Number of blood lust rolls|
|:---------------|:--------|:-------------------------|
|bloodlust\_successes|bldlst   |Number of times this player didn't succumb to blood lust.|
|bloodfeeds      |bldfed   |Number of blood feeds by this vampire|
|hypnoze\_rolls  |hyp\_a   |Number of times hypnotic gaze was used|
|hypnoze\_successes|hyp      |Number of times hypnotic gaze was successful|
|!inflicted\_bloodfeed\_stuns|bld\_st\_i|Number of stuns from a blood feed (doesn't seem to be working, path is action blood feed, armour roll 2 for thrall, injury roll for thrall, end status stunned). Not sure why it doesn't go straight to the injury roll.|
|!inflicted\_bloodfeed\_kos|bld\_ko\_i|Number of KOs from a blood feed (as above)|
|!inflicted\_bloodfeed\_bhs|bld\_bh\_i|Number of badly hurts from a blood feed (as above)|

### Thrall stats ###

As above as the path for outcome of thrall injuries isnt working (or at least my filter is wrong), i cant work out sustained effects of the blood feed.

|!fed\_on|fed\_s|Number of times this thrall player has been fed on.|
|:-------|:-----|:--------------------------------------------------|

### Skill stats ###

|tentacles\_rolls|tent\_a|Number of times this player used his tentacles|
|:---------------|:------|:---------------------------------------------|
|tentacles\_successes|tent   |Number of times this players successfully held another|
|foul\_appearance\_rolls|foul\_a|Number of times foul appearance was rolled    |
|foul\_appearance\_successes|foul   |Number of times foul appearance succeeded     |
|dauntless\_rolls|dau\_a |Number of times dauntless was rolled          |
|dauntless\_successes|dau    |Number of times dauntless succeeded           |
|shadowing\_rolls|shad\_a|Number of times shadowing was attempted       |
|shadowing\_successes|shad   |Number of times shadowing succeeded           |


### Bomb Throwing stats ###


|bombs\_throw\_attempts|bomb\_a|Number of times a bomb throw attempts|
|:---------------------|:------|:------------------------------------|
|bombs\_thrown         |bomb\_t|Number of times a bomb was thrown    |
|sustained\_bomb\_effect|sbo\_ef|Number of times effected by a bomb   |
|sustained\_bomb\_stun |sbo\_st|Number of times stunned by a bomb    |
|sustained\_bomb\_ko   |sbo\_ko|Number of times knocked out by a bomb|
|sustained\_bomb\_bh   |sbo\_bh|Number of times badly hurt by a bomb |
|sustained\_bomb\_si   |sbo\_si|Number of times seriously hurt by a bomb|
|sustained\_bomb\_kill |sbo\_ki|Number of times killed by a bomb     |