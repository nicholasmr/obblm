<?php

class Gallery implements ModuleInterface
{


public static function main($argv) # argv = argument vector (array).
{
    global $lng;

    title($lng->getTrn('name', 'Gallery'));

    if (isset($_POST['type'])) {
        echo "<center><a href='handler.php?type=gallery'>".$lng->getTrn('common/back')."</a></center>\n";
        switch ($_POST['type'])
        {
            case 'team':
                $t = new Team((int) $_POST['tid']);
                echo "<b>".$lng->getTrn('playersof', 'Gallery')." $t->name</b><br><hr><br>\n";
                foreach ($t->getPlayers() as $p) {
                    $img = new ImageSubSys(IMGTYPE_PLAYER, $p->player_id);
                    $pic = $img->getPath();
                    echo "<div style='float:left; padding:10px;'>$p->name (#$p->nr)<br><a href='".urlcompile(T_URL_PROFILE,T_OBJ_PLAYER,$p->player_id,false,false)."'><img HEIGHT=150 src='$pic' alt='pic'></a></div>";
                }
                break;

            case 'stad':
                echo "<b>".$lng->getTrn('stads', 'Gallery')."</b><br><hr><br>\n";
                foreach (Team::getTeams() as $t) {
                    $img = new ImageSubSys(IMGTYPE_TEAMSTADIUM, $t->team_id);
                    $pic = $img->getPath();
                    echo "<div style='float:left; padding:10px;'>$t->name<br><a href='$pic'><img HEIGHT=150 src='$pic' alt='pic'></a></div>";
                }
                break;

            case 'coach':
                echo "<b>".$lng->getTrn('coaches', 'Gallery')."</b><br><hr><br>\n";
                foreach (Coach::getCoaches() as $c) {
                    $img = new ImageSubSys(IMGTYPE_COACH, $c->coach_id);
                    $pic = $img->getPath();
                    echo "<div style='float:left; padding:10px;'>$c->name<br><a href='$pic'><img HEIGHT=150 src='$pic' alt='pic'></a></div>";
                }
                break;
        }

        return;
    }

    echo $lng->getTrn('note', 'Gallery');
    ?>
    <ul>
    <li>
        <?php 
        $teams = get_rows('teams', array('team_id', 'name'));
        objsort($teams, array('+name'));
        echo $lng->getTrn('players', 'Gallery')."
        <form method='POST' style='display:inline; margin:0px;'><select name='tid' onChange='this.form.submit();'>
        <option value='0'>-".$lng->getTrn('none', 'Gallery')."-</option>".
        implode("\n", array_map(create_function('$o', 'return "<option value=\'$o->team_id\'>$o->name</option>";'), $teams))
        ."</select><input type='hidden' name='type' value='team'></form>
        ";?>
    </li>
    <li><?php echo inlineform(array('type' => 'stad'),  'stadForm',     $lng->getTrn('stads', 'Gallery'));?></li>
    <li><?php echo inlineform(array('type' => 'coach'), 'coachesForm',  $lng->getTrn('coaches', 'Gallery'));?></li>
    </ul>
    <?php

}

public static function getModuleAttributes()
{
    return array(
        'author'     => 'Nicholas Mossor Rathmann',
        'moduleName' => 'Gallery',
        'date'       => '2009',
        'setCanvas'  => true,
    );
}

public static function getModuleTables()
{
    return array();
}    

public static function getModuleUpgradeSQL()
{
    return array();
}

public static function triggerHandler($type, $argv){
}

}
?>
