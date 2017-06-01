<div id="tetris">
<div class="left">
<h1>JsTetris 1.1.0</h1>
<div class="menu">
<div><input value="New Game" id="tetris-menu-start" type="button"></div>
<div><input value="Reset" id="tetris-menu-reset" type="button"></div>
<div><input value="Help" id="tetris-menu-help" type="button"></div>
<div><input value="Highscores" id="tetris-menu-highscores" type="button"></div>
</div>
<div class="keyboard">
<div class="up"><input value="^" id="tetris-keyboard-up" type="button"></div>
<div class="down"><input value="v" id="tetris-keyboard-down" type="button"></div>
<div class="left"><input value="&lt;" id="tetris-keyboard-left" type="button"></div>
<div class="right"><input value="&gt;" id="tetris-keyboard-right" type="button"></div>
</div>
<div id="tetris-nextpuzzle"></div>
<div id="tetris-gameover">Game Over</div>
<div class="stats">
<table cellpadding="0" cellspacing="0">
<tbody>
<tr>
<td class="level">Level:</td>
<td><span id="tetris-stats-level">1</span></td>
</tr>
<tr>
<td class="score">Score:</td>
<td><span id="tetris-stats-score">0</span></td>
</tr>
<tr>
<td class="lines">Lines:</td>
<td><span id="tetris-stats-lines">0</span></td>
</tr>
<tr>
<td class="apm">APM:</td>
<td><span id="tetris-stats-apm">0</span></td>
</tr>
<tr>
<td class="time">Time:</td>
<td><span id="tetris-stats-time">0</span></td>
</tr>
</tbody>
</table>
</div>
</div>
	<div id="tetris-area">
	    <div class="grid1"></div>
	    <div class="grid2"></div>
	    <div class="grid3"></div>
	    <div class="grid4"></div>
	    <div class="grid5"></div>
	    <div class="grid6"></div>
	</div>
<div id="tetris-help" class="window">
<div class="top">Help <span id="tetris-help-close" class="close">x</span>
</div>
<div class="content">
<b>Controllers:</b> <br>
up - rotate <br>
down - move down <br>
left - move left <br>
right - move right <br>
space - fall to the bottom <br>
n - new game <br>
r - reset <br>
<br>
<b>Rules:</b> <br>
1) Puzzle speed = 80+700/level miliseconds, the smaller value the
faster puzzle falls <br>
2) If puzzles created in current level &gt;= 10+level*2 then
increase level <br>
3) After puzzle falling score is increased by 1000*level*linesRemoved <br>
4) Each "down" action increases score by 5+level (pressing space counts
as multiple down actions)
</div>
</div>
<div id="tetris-highscores" class="window">
<div class="top">Highscores <span id="tetris-highscores-close" class="close">x</span>
</div>
<div class="content">
<div id="tetris-highscores-content"></div>
<br>
Note: these scores are kept in cookies, they are only visible to your
computer, other players that visit this page see their own scores.
</div>
</div>
</div>
