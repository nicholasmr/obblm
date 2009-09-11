<?php
if (isset($_POST['type'])) {
    switch ($_POST['type'])
    {
        case 'change':
            $t = new Tour($_POST['trid']);
            status($t->chRS($_POST['rs']) && $t->chType($_POST['ttype']) && $t->rename($_POST['tname']));
            break;

        case 'delete':
            if (isset($_POST['delete']) && $_POST['delete']) {
                $t = new Tour($_POST['trid']);
                status($t->delete(true));
            }
            else {
                status(false, 'Please mark the agreement box before trying to delete a tournament.');
            }
            break;

        case 'move':
            $t = new Tour($_POST['trid']);
            status($t->ch_did($_POST['did']));
            break;

        case 'lock':
            $t = new Tour($_POST['trid']);
            status($t->setLocked(isset($_POST['lock']) && $_POST['lock']));
            break;
    }
}

title($lng->getTrn('secs/admin/th'));
$tours = Tour::getTours();
$nameChangeJScode = "e = document.forms['tourForm'].elements; e['tname'].value = e['trid'].options[e['trid'].selectedIndex].text;";

?>
<div class="adminBox">
    <div class="boxTitle3"><?php echo $lng->getTrn('secs/admin/edit_tour');?></div>
    <div class="boxBody">
    <form id='tourForm' method="POST">
        <br>
        <b>Edit tournament:</b><br>
        <select name="trid" onChange="<?php echo $nameChangeJScode;?>">
            <?php
            foreach ($tours as $t) {
                echo "<option value='$t->tour_id'>$t->name</option>\n";
            }
            ?>
        </select>
        <br><br>
        <hr>
        <br>
        <b>New name:</b><br>
        <input type='text' name='tname' length='20' value=''>

        <script language="JavaScript" type="text/javascript">
            <?php echo $nameChangeJScode;?>
        </script>

        <br><br>
        <b>New ranking system:</b> (<?php echo $lng->getTrn('secs/admin/prefixes');?>)<br>
        <select name='rs'>
        <?php
        foreach (Tour::getRSSortRules(false, true) as $idx => $r) {
            echo "<option value='$idx'>RS #$idx | $r</option>\n";
        }
        ?>
        </select>

        <br><br>
        <b>New tournament type:</b><br>
        <input type="radio" name="ttype" value="<?php echo TT_RROBIN;?>" > Round-Robin<br>
        <input type="radio" name="ttype" value="<?php echo TT_FFA;?>" CHECKED> FFA (free for all) single match<br>
        <br>

        <input type="hidden" name="type" value="change">
        <input type="submit" value="Submit changes" <?php echo (empty($tours)) ? 'DISABLED' : ''?>>
        <br>
    </form>
    </div>
</div>

<div class="adminBox">
    <div class="boxTitle3"><?php echo $lng->getTrn('secs/admin/tour_del');?></div>
    <div class="boxBody">
    <form method="POST">
        <b>I wish to delete the following tournament</b><br>
        <select name="trid">
            <?php
            foreach ($tours as $t) {
                echo "<option value='$t->tour_id'>$t->name</option>\n";
            }
            ?>
        </select>
        <br><br>
        <b><?php echo $lng->getTrn('secs/admin/advise/have_read');?>:</b>
        <input type="checkbox" name="delete" value="1">
        <br><br>
        <b><u>Advisement/warning:</u></b><br>
        <?php echo $lng->getTrn('secs/admin/advise/pre');?>
        <br>
        <ul>
            <li><?php echo $lng->getTrn('secs/admin/advise/e1');?></li>
            <li><?php echo $lng->getTrn('secs/admin/advise/e2');?></li>
        </ul>
        <br>
        <input type="hidden" name="type" value="delete">
        <input type="submit" value="Delete" onclick="if(!confirm('Are you absolutely sure you want to delete this tournament?')){return false;}">
    </form>
    </div>
</div>

<div class="adminBox">
    <div class="boxTitle3"><?php echo $lng->getTrn('secs/admin/move_div');?></div>
    <div class="boxBody">
    <form method="POST">
        <b>Move tournament</b><br>
        <select name="trid">
            <?php
            foreach ($tours as $t) {
                echo "<option value='$t->tour_id'>$t->name</option>\n";
            }
            ?>
        </select><br><br>
        <b>...to the division</b><br>
        <select name="did">
            <?php
            foreach (Division::getDivisions() as $d) {
                echo "<option value='$d->did'>$d->name</option>\n";
            }
            ?>
        </select><br><br>
        <input type="hidden" name="type" value="move">
        <input type="submit" value="Move">
    </form>
    </div>
</div>

<div class="adminBox">
    <div class="boxTitle3">Lock/unlock tournament</div>
    <div class="boxBody">
    <form method="POST">
        <b>Tournament</b><br>
        <select name="trid">
            <?php
            foreach ($tours as $t) {
                echo "<option value='$t->tour_id'>$t->name".(($t->locked) ? ' (is locked)' : '')."</option>\n";
            }
            ?>
        </select><br><br>
        <b>Set locked state to</b> (locked/unlocked = checked/unchecked):
        <input type="checkbox" name="lock" value="1">
        <br><br>
        <input type="hidden" name="type" value="lock">
        <input type="submit" value="OK">
    </form>
    </div>
</div>
<?php
