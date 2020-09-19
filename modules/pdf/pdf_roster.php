<?php

/*
 *  Copyright (c) Daniel Straalman <email is protected> 2008-2009. All Rights Reserved.
 *
 *
 *  This file is part of OBBLM.
 *
 *  OBBLM is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  OBBLM is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/*
 * Author Daniel Straalman, 2009
 *
 * Note: Detailed view does not work, only regular view. Detailed view with player history (sold/dead) would never fit on an A4 paper.
 */
 
class PDFroster implements ModuleInterface
{

public static function getModuleAttributes()
{
    return array(
        'author'     => 'Daniel Straalman',
        'moduleName' => 'PDF roster',
        'date'       => '2008-2009',
        'setCanvas'  => false, 
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
 
public static function triggerHandler($type, $argv){}

public static function main($argv)
{

global $pdf;
global $DEA;
global $skillarray;
global $rules;
global $inducements;
global $starpairs;

define("MARGINX", 20);
define("MARGINY", 20);
define("DEFLINECOLOR", '#000000');
define("HEADLINEBGCOLOR", '#c3c3c3');

// Custom settings for inducements.

define('MAX_STARS', 2);
define('MERC_EXTRA_COST', 30000);
define('MERC_EXTRA_SKILL_COST', 50000);

// Color codes.
define('COLOR_ROSTER_NORMAL',   COLOR_HTML_NORMAL);
define('COLOR_ROSTER_READY',    COLOR_HTML_READY);
define('COLOR_ROSTER_MNG',      COLOR_HTML_MNG);
define('COLOR_ROSTER_DEAD',     COLOR_HTML_DEAD);
define('COLOR_ROSTER_SOLD',     COLOR_HTML_SOLD);
define('COLOR_ROSTER_STARMERC', COLOR_HTML_STARMERC);
define('COLOR_ROSTER_JOURNEY',  COLOR_HTML_JOURNEY);
define('COLOR_ROSTER_JOURNEY_USED',  COLOR_HTML_JOURNEY_USED);
define('COLOR_ROSTER_NEWSKILL', COLOR_HTML_NEWSKILL);
//-----
define('COLOR_ROSTER_CHR_EQP1', COLOR_HTML_CHR_EQP1); // Characteristic equal plus one.
define('COLOR_ROSTER_CHR_GTP1', COLOR_HTML_CHR_GTP1); // Characteristic greater than plus one.
define('COLOR_ROSTER_CHR_EQM1', COLOR_HTML_CHR_EQM1); // Characteristic equal minus one.
define('COLOR_ROSTER_CHR_LTM1', COLOR_HTML_CHR_LTM1); // Characteristic less than minus one.

define('T_PDF_ROSTER_SET_EMPTY_ON_ZERO', true); # Prints cp, td etc. as '' (empty string) when field = 0.

$ind_cost=0;

//
// Most of team and player data is copy/pasted from teams.php
//

$team_id = $_GET['team_id'];
// Is team id valid?
if (!get_alt_col('teams', 'team_id', $team_id, 'team_id'))
    fatal("Invalid team ID.");

$team  = new Team($team_id);
$coach = isset($_SESSION['logged_in']) ? new Coach($_SESSION['coach_id']) : null;

setupGlobalVars(T_SETUP_GLOBAL_VARS__LOAD_LEAGUE_SETTINGS, array('lid' => $team->f_lid)); // Load correct $rules for league.

$players = $team->getPlayers();

$tmp_players = array();
foreach ($players as $p) {
    if ($p->is_dead || $p->is_sold)
        continue;
    array_push($tmp_players, $p);
}
$players = $tmp_players;

// Team specific data

$rerollcost = $DEA[$team->f_rname]['other']['rr_cost'];

$pdf=new BB_PDF('L','pt','A4'); // Creating a new PDF doc. Landscape, scale=pixels, size A4
$pdf->SetAutoPageBreak(false, 20); // No auto page break to mess up layout

$pdf->SetAuthor('Daniel Straalman');
$pdf->SetCreator('OBBLM');
$pdf->SetTitle('PDF Roster for ' . utf8_decode($team->name));
$pdf->SetSubject('PDF Roster for ' . utf8_decode($team->name));

$pdf->AddFont('Tahoma','','tahoma.php');  // Adding regular font Tahoma which is in font dir
$pdf->AddFont('Tahoma','B','tahomabd.php');  // Adding Tahoma Bold

// Initial settings
$pdf->SetFont('Tahoma','B',14);
$pdf->AddPage();
$pdf->SetLineWidth(1.5);
$currentx = MARGINX;
$currenty = MARGINY;
$pdf->SetFillColorBB($pdf->hex2cmyk(HEADLINEBGCOLOR));
$pdf->RoundedRect($currentx, $currenty, 802, 20, 6, 'DF'); // Filled rectangle around Team headline
$pdf->SetDrawColorBB($pdf->hex2cmyk(DEFLINECOLOR));

// Text in headline
$pdf->SetXY($currentx+30,$currenty);
$pdf->Cell(310, 20, utf8_decode($team->name), 0, 0, 'L', false, '');
$pdf->SetFont('Tahoma','',12);
$pdf->Cell(60, 20, "Race:", 0, 0, 'R', false, '');
$pdf->Cell(70, 20, ($team->f_rname), 0, 0, 'L', false, '');
$pdf->Cell(300, 20, ("Head Coach: " . utf8_decode($team->f_cname)), 0, 0, 'R', false, '');

$currenty+=25;
$currentx+=6;
$pdf->SetXY($currentx,$currenty);

$pdf->SetFillColorBB($pdf->hex2cmyk(HEADLINEBGCOLOR));
$pdf->SetDrawColorBB($pdf->hex2cmyk(DEFLINECOLOR));
$pdf->SetFont('Tahoma','B',8);
$pdf->SetLineWidth(1.5);
$h = 14;

// Printing headline for player table
$pdf->Cell(23, $h, 'Nr', 1, 0, 'C', true, '');
$pdf->Cell(97, $h, 'Name', 1, 0, 'L', true, '');
$pdf->Cell(75, $h, 'Position', 1, 0, 'L', true, '');
$pdf->Cell(18, $h, 'MA', 1, 0, 'C', true, '');
$pdf->Cell(18, $h, 'ST', 1, 0, 'C', true, '');
$pdf->Cell(18, $h, 'AG', 1, 0, 'C', true, '');
$pdf->Cell(18, $h, 'AV', 1, 0, 'C', true, '');
$pdf->Cell(329, $h, 'Skills and Injuries', 1, 0, 'L', true, '');
$pdf->Cell(23, $h, 'MNG', 1, 0, 'C', true, '');
$pdf->Cell(21, $h, 'CP', 1, 0, 'C', true, '');
$pdf->Cell(21, $h, 'TD', 1, 0, 'C', true, '');
$pdf->Cell(21, $h, 'Int', 1, 0, 'C', true, '');
$pdf->Cell(21, $h, 'Cas', 1, 0, 'C', true, '');
$pdf->Cell(23, $h, 'MVP', 1, 0, 'C', true, '');
$pdf->Cell(25, $h, 'SPP', 1, 0, 'C', true, '');
$pdf->Cell(41, $h, 'Value', 1, 0, 'C', true, '');

$currenty+=17;

$pdf->SetXY($currentx,$currenty);
$pdf->SetFont('Tahoma', '', 8);
$h=15;  // Row/cell height for player table

//
// Printing player rows
//

$sum_spp=0;
$sum_pvalue=0;
$sum_p_missing_value=0;
$sum_avail_players=0;
$sum_players=0;
$sum_cp=0;
$sum_td=0;
$sum_int=0;
$sum_cas=0;
$sum_mvp=0;
$i=0;

// Looping through the players and printing the rows
foreach ($players as $p) {
  $i++;
  $mng='';
  
  // Journeymen
  if ($p->is_journeyman) {
    $p->position = 'Journeyman';
    $bgc=COLOR_ROSTER_JOURNEY;
    if ($p->is_journeyman_used) {
        $bgc=COLOR_ROSTER_JOURNEY_USED;
    }
  }
  else $bgc=COLOR_ROSTER_NORMAL;
  
  // Concatenate skills, upgrades and injuries
  $skillstr = $p->getSkillsStr(false);
  $injstr = $p->getInjsStr(false);
  if ($skillstr == '') {  // No skills
    if ($injstr != '') $skills_injuries=$injstr;  // Only injuries
    else $skills_injuries=''; // No skills nor injuries
  }
  else {
    if ($injstr != '') $skills_injuries=$skillstr . ', ' . $injstr;   // Skills and injuries separated with ', '
    else $skills_injuries=$skillstr;  // Only skills, no injuries
  }
  
  // Colorcoding new skills available
  if ($p->mayHaveNewSkill()) $bgc=COLOR_ROSTER_NEWSKILL;
  
  if (!($p->is_mng)) { 
    $sum_avail_players++;
    $inj="";
  } 
  else {
    $bgc=COLOR_ROSTER_MNG;
    $sum_p_missing_value+=$p->value;
    $inj="MNG"; // For MNG column
    // Removing MNG from skills and injuries
    $skills_injuries = str_replace(', MNG', '', $skills_injuries);
    $skills_injuries = str_replace('MNG', '', $skills_injuries);
    $skills_injuries = str_replace('  ', ' ', $skills_injuries);    // Maybe not needed after changes to rest of code?
  }
  
  // Characteristic's colors, copied and modified from teams.php
  foreach (array('ma', 'ag', 'av', 'st') as $chr) {
      $sub = $p->$chr - $p->{"def_$chr"};
      if ($sub == 0)  $p->{"${chr}_color"} = $bgc;
      elseif ($sub >= 1)  $p->{"${chr}_color"} = COLOR_ROSTER_CHR_GTP1;
      elseif ($sub <= -1) $p->{"${chr}_color"} = COLOR_ROSTER_CHR_LTM1;
  }

  $pp = array('nr'=>$p->nr, 'name'=>utf8_decode($p->name), 'pos'=>$p->position, 'ma'=>$p->ma, 'st'=>$p->st, 'ag'=>$p->ag, 'av'=>$p->av, 'skills'=>utf8_decode($skills_injuries), 'inj'=>$inj,
     'cp'=>$p->mv_cp, 'td'=>$p->mv_td, 'int'=>$p->mv_intcpt, 'cas'=>$p->mv_cas, 'mvp'=>$p->mv_mvp, 'spp'=>$p->mv_spp, 'value'=>$pdf->Mf($p->value));
  $sum_spp+=$p->mv_spp;
  $sum_pvalue+=$p->value;
  $sum_players++;
  $sum_cp+=$p->mv_cp;
  $sum_td+=$p->mv_td;
  $sum_int+=$p->mv_intcpt;
  $sum_cas+=$p->mv_cas;
  $sum_mvp+=$p->mv_mvp;
  
    if (T_PDF_ROSTER_SET_EMPTY_ON_ZERO) {
        foreach (array('cp','td','int','cas','mvp','spp') as $f) {
            if ($pp[$f] == 0) {
                $pp[$f] = '';
            }
        }
    }
  
  // Printing player row
  $currenty+=$pdf->print_prow($pp, $currentx, $currenty, $h, $bgc, DEFLINECOLOR, 0.5, 8, $p->ma_color, $p->st_color, $p->ag_color, $p->av_color);
}

// Filling up with empty rows to max number of players
$pp = array('nr'=>'', 'name'=>'', 'pos'=>'', 'ma'=>'', 'st'=>'', 'ag'=>'', 'av'=>'', 'skills'=>'', 'inj'=>'',
            'cp'=>'', 'td'=>'', 'int'=>'', 'cas'=>'', 'mvp'=>'', 'spp'=>'', 'value'=>'');
$bgc = COLOR_ROSTER_NORMAL;
while ($i<$rules['max_team_players']) {
  $i++;
  $currenty += $pdf->print_prow($pp, $currentx, $currenty, $h, '#FFFFFF', '#000000', 0.5, 8, $bgc, $bgc, $bgc, $bgc);
}

// Sums
$sum_pvalue -= $sum_p_missing_value;
$pdf->SetXY(($currentx=MARGINX+6+23), ($currenty+=4));
$pdf->print_box($currentx, $currenty, 172, $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 0, 0, 8, 'Tahoma', false, 'R', 'Total number of players next game:');
$pdf->print_box($currentx+=172, $currenty, 30, $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 0, 0, 8, 'Tahoma', false, 'R', $sum_avail_players . '/' . $sum_players);

$pdf->SetX($currentx=MARGINX+6+559);
$pdf->print_box($currentx, $currenty, 60, $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 0, 0, 8, 'Tahoma', false, 'R', 'Totals (excl TV for MNG players):');
$pdf->print_box($currentx+=60, $currenty, 21, $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 0, 0, 8, 'Tahoma', false, 'C', $sum_cp);
$pdf->print_box($currentx+=21, $currenty, 21, $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 0, 0, 8, 'Tahoma', false, 'C', $sum_td);
$pdf->print_box($currentx+=21, $currenty, 21, $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 0, 0, 8, 'Tahoma', false, 'C', $sum_int);
$pdf->print_box($currentx+=21, $currenty, 21, $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 0, 0, 8, 'Tahoma', false, 'C', $sum_cas);
$pdf->print_box($currentx+=21, $currenty, 23, $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 0, 0, 8, 'Tahoma', false, 'C', $sum_mvp);
$pdf->print_box($currentx+=23, $currenty, 25, $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 0, 0, 8, 'Tahoma', false, 'C', $sum_spp);
$pdf->print_box($currentx+=25, $currenty, 41, $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 0, 0, 8, 'Tahoma', false, 'R', $pdf->Mf($sum_pvalue));

// Stars and Mercs part of roster
$currentx=MARGINX+6+23;
$currenty+=$h+2;

// Draw rounded rectangle around stars and mercs
// This rectangle has flexible height depending on how high player table is
$pdf->SetLineWidth(0.6);
$pdf->RoundedRect(MARGINX+6, $currenty, 792, (560-$currenty-130), 5, 'D');

$pdf->SetXY($currentx, $currenty+=2);
$h=14;
$pdf->SetFont('Tahoma', 'B', 8);
$pdf->Cell(97+75, $h, 'Induced Stars and Mercenaries', 0, 0, 'L', true, '');
$pdf->Cell(18, $h, 'MA', 0, 0, 'C', true, '');
$pdf->Cell(18, $h, 'ST', 0, 0, 'C', true, '');
$pdf->Cell(18, $h, 'AG', 0, 0, 'C', true, '');
$pdf->Cell(18, $h, 'AV', 0, 0, 'C', true, '');
$pdf->Cell(329, $h, 'Skills', 0, 0, 'L', true, '');
//$pdf->Cell(23, $h, 'MNG', 1, 0, 'C', true, ''); // No MNG stars/mercs. They heal. ;-)
$pdf->Cell(21, $h, 'CP', 0, 0, 'C', true, '');
$pdf->Cell(21, $h, 'TD', 0, 0, 'C', true, '');
$pdf->Cell(21, $h, 'Int', 0, 0, 'C', true, '');
$pdf->Cell(21, $h, 'Cas', 0, 0, 'C', true, '');
$pdf->Cell(23, $h, 'MVP', 0, 0, 'C', true, '');
$pdf->Cell(25, $h, 'SPP', 0, 0, 'C', true, '');
$pdf->Cell(41, $h, 'Value', 0, 0, 'R', true, '');
$currenty+=14;
$pdf->SetXY($currentx, $currenty);
$h=13;

// Printing chosen stars and mercs 
$pdf->SetFont('Tahoma', '', 8);
$merc = array(0=>'No Merc');
$i=0;
if ($_POST) {
  foreach ($DEA[$team->f_rname]["players"] as $p => $m) {
    $i++;
    array_push($merc, $m);
    $pos[$i] = $p;
  }
  $postvars = array(); # initialize.
  foreach ($_POST as $postkey => $postvalue) {
    if ($postkey == "Submit") continue;
    if ($postvalue == "0") continue;
    if ($postvalue == "0k") continue;
    if ($postvalue == "-No Extra Skill-") continue;
    $postvars[str_replace('_', ' ',$postkey)] = $postvalue;
  }

  $star_array_tmp[0]=0;
  $merc_array_tmp[0]=0;
  while (list($key, $val) = each($postvars)) {
    if (strpos($key,'Star') !== false) { // if POST key is StarX
        array_push($star_array_tmp,$val);
      continue;
    }
    elseif (strpos($key,'Merc') !== false) {
      $merc_nr = preg_replace("/[^0-9]/","", $key);
      $merc_array_tmp[$merc_nr] = $pos[$val];
      if (isset($postvars["Extra$merc_nr"])) $extra_array_tmp[$merc_nr] = $postvars["Extra$merc_nr"];
      else $extra_array_tmp[$merc_nr] = '';
      continue;
    }
    elseif ($key == 'Bloodweiser Babes') { $ind_babes = (int) $val; continue; }
    elseif ($key == 'Bribes') { $ind_bribes = (int) $val; continue; }
    elseif ($key == 'Card') { $ind_card = (int) str_replace('k','000',$val); continue; }
    elseif ($key == 'Extra Training') { $ind_rr = (int) $val; continue; }
    elseif ($key == 'Halfling Master Chef') { $ind_chef = (int) $val; continue; }
    elseif ($key == 'Igor') { $ind_igor = (int) $val; continue; }
    elseif ($key == 'Wandering Apothecaries') { $ind_apo = (int) $val; continue; }
    elseif ($key == 'Hireling Sports-Wizard') { $ind_wiz = (int) $val; continue; }
    //	elseif ($key == '###') { $ind_### = (int) $val; continue; }
    //  added BB2016 - Spike inducements	
    elseif ($key == 'Horatio X Schottenheim') { $ind_hxs = (int) $val; continue; }
    elseif ($key == 'Fink Da Fixer') { $ind_fdf = (int) $val; continue; }
    elseif ($key == 'Papa Skullbones') { $ind_psb = (int) $val; continue; }
    elseif ($key == 'Galandril Silverwater') { $ind_gsw = (int) $val; continue; }
    elseif ($key == 'Krot Shockwhisker') { $ind_ksw = (int) $val; continue; }
    elseif ($key == 'Kari Coldsteel') { $ind_kcs = (int) $val; continue; }
    elseif ($key == 'Chaos Sorcerer') { $ind_csr = (int) $val; continue; }
    elseif ($key == 'Specialist Assisstant Coaches') { $ind_sac = (int) $val; continue; }
    elseif ($key == 'Temp Agency Cheerleaders') { $ind_tem = (int) $val; continue; }
    elseif ($key == 'Weather Mage') { $ind_wxm = (int) $val; continue; }
    elseif ($key == 'Druchii Sports Sorceress') { $ind_dss = (int) $val; continue; }
    elseif ($key == 'Plague Doctors') { $ind_pdr = (int) $val; continue; }
    elseif ($key == 'Cavorting Nurglings') { $ind_cvn = (int) $val; continue; }
    elseif ($key == 'Plague Horticulturalist of Nurgle') { $ind_phn = (int) $val; continue; }
    elseif ($key == 'Joseph Bugman, Dwarf Master Brewer') { $ind_dmb = (int) $val; continue; }
    elseif ($key == 'Sports Necrotheurge') { $ind_spn = (int) $val; continue; }
    elseif ($key == 'Halfling Hot-Pot') { $ind_hhp = (int) $val; continue; }
    elseif ($key == 'Bottle of Heady Brew') { $ind_bhb = (int) $val; continue; }
    elseif ($key == 'Slann Mage-Priest') { $ind_smp = (int) $val; continue; }
    elseif ($key == 'Riotous Rookies') { $ind_rok = (int) $val; continue; }
    elseif ($key == 'Firebelly') { $ind_fbl = (int) $val; continue; }
    elseif ($key == 'Night Goblin Sports Shaman') { $ind_ngs = (int) $val; continue; }
  }

  // Printing stars first
  if (isset($star_array_tmp[1])) {
    unset($star_array_tmp[0]);
    foreach ($star_array_tmp as $sid) {
      $s = new Star($sid);
      $s->setSkills(true);
      $ss = array('name'=>utf8_decode($s->name), 'ma'=>$s->ma, 'st'=>$s->st, 'ag'=>$s->ag, 'av'=>$s->av, 'skills'=>utf8_decode($s->skills),
            'cp'=>$s->mv_cp, 'td'=>$s->mv_td, 'int'=>$s->mv_intcpt, 'cas'=>$s->mv_cas, 'mvp'=>$s->mv_mvp, 'spp'=>$s->mv_spp, 'value'=>$pdf->Mf($s->cost));
      $currenty+=$pdf->print_srow($ss, $currentx, $currenty, $h, $bgc, DEFLINECOLOR, 0.5, 8);
      $ind_cost += $s->cost;
      if (array_key_exists($sid, $starpairs)) {
          // Parent Star selected
          $sid = $starpairs[$sid];
          $s = new Star($sid);
          $s->setSkills(true);
          $ss = array('name'=>utf8_decode($s->name), 'ma'=>$s->ma, 'st'=>$s->st, 'ag'=>$s->ag, 'av'=>$s->av, 'skills'=>utf8_decode($s->skills),
          'cp'=>$s->mv_cp, 'td'=>$s->mv_td, 'int'=>$s->mv_intcpt, 'cas'=>$s->mv_cas, 'mvp'=>$s->mv_mvp, 'spp'=>$s->mv_spp, 'value'=>$pdf->Mf($s->cost));
          $currenty+=$pdf->print_srow($ss, $currentx, $currenty, $h, $bgc, DEFLINECOLOR, 0.5, 8);
      }
    }
  }

  // Then Mercs
  if (is_array($merc_array_tmp)) {
    unset($merc[0]);
    $r=$team->f_rname;
    $i=0;
    unset($merc_array_tmp[0]);
    foreach ($merc_array_tmp as $mpos) {
      $i++;
      $m['name'] = 'Mercenary '.$mpos;
      $m['ma'] = $DEA[$r]['players'][$mpos]['ma'];
      $m['st'] = $DEA[$r]['players'][$mpos]['st'];
      $m['ag'] = $DEA[$r]['players'][$mpos]['ag'];
      $m['av'] = $DEA[$r]['players'][$mpos]['av'];
      $m['skillarr'] = $DEA[$r]['players']["$mpos"]['def'];
      if (!in_array(99, $m['skillarr'])) array_unshift($m['skillarr'], 99);	// Adding Loner unless already in array
      $m['skills'] = implode(', ', skillsTrans($m['skillarr']));
      $m['cost'] = $DEA[$r]['players'][$mpos]['cost'] + MERC_EXTRA_COST;
      if (isset($postvars["Extra$i"])) {
        $m['cost'] += MERC_EXTRA_SKILL_COST;
        $m['extra'] = $postvars["Extra$i"];
        
        if ($m['skills'] == '') $m['skills'] = $m['extra']; 
        else $m['skills'] = $m['skills'] . ', ' . $m['extra'];
      }
      $ss = array('name'=>utf8_decode($m['name']), 'ma'=>$m['ma'], 'st'=>$m['st'], 'ag'=>$m['ag'], 'av'=>$m['av'], 'skills'=>utf8_decode($m['skills']),
            'cp'=>' ', 'td'=>' ', 'int'=>' ', 'cas'=>' ', 'mvp'=>' ', 'spp'=>' ', 'value'=>$pdf->Mf($m['cost']));
      $currenty+=$pdf->print_srow($ss, $currentx, $currenty, $h, $bgc, DEFLINECOLOR, 0.5, 8);
      $ind_cost += $m['cost'];
    }
  }
}
$h = 13;

// Printing lower part of roster
$currentx = MARGINX;
$currenty = 435;
$ind_count = 0;

// Checking if Wandering Apothecary should be replaced with Igor
$r=$team->f_rname;
if (($r == 'Nurgle') || ($r == 'Khemri') || ($r == 'Necromantic') || ($r == 'Undead')) {
  $apo_igor = 'Igor (0-1):';
  unset($inducements['Wandering Apothecaries']);
  if (isset($ind_igor)) { 
    $ind_apo_igor_cost = $ind_igor*$inducements['Igor']['cost'];
    // $ind_cost += $ind_igor*$ind_apo_igor_cost; CAN REMOVE
    $ind_apo_igor = $ind_igor;
  }
  else { $ind_apo_igor = '__'; $ind_apo_igor_cost = $inducements['Igor']['cost']; }
}
else {
  $apo_igor = 'Wandering Apothecaries (0-2):';
  unset($inducements['Igor']);
  if (isset($ind_apo)) { 
    $ind_apo_igor_cost = $inducements['Wandering Apothecaries']['cost'];
    // $ind_cost += $ind_apo*$ind_apo_igor_cost; CAN REMOVE
    $ind_apo_igor = $ind_apo;
  }
  else { $ind_apo_igor = '__'; $ind_apo_igor_cost = $inducements['Wandering Apothecaries']['cost']; }
}
// Checking game data if cheaper Chef for Halfling
$chef_cost = $inducements['Halfling Master Chef'][(($r == 'Halfling') ? 'reduced_cost' : 'cost')];
// Checking game data if cheaper bribes for Goblin and Snotling
$bribe_cost = $inducements['Bribes'][(($r == 'Goblin') ? 'reduced_cost' : 'cost')];
$bribe_cost = $inducements['Bribes'][(($r == 'Snotling') ? 'reduced_cost' : 'cost')];

//calculate inducement costs
if (isset($ind_igor)) { $ind_cost += $ind_igor*$inducements['Igor']['cost']; $ind_count += 1; }
if (isset($ind_apo)) { $ind_cost += $ind_apo*$inducements['Wandering Apothecaries']['cost']; $ind_count += 1; }
if (isset($ind_babes)) { $ind_cost += $ind_babes*$inducements['Bloodweiser Babes']['cost']; $ind_count += 1; }
if (isset($ind_bribes)) { $ind_cost += $ind_bribes*$bribe_cost; $ind_count += 1; }
if (isset($ind_card)) { $ind_cost += $ind_card; $ind_count += 1; }
if (isset($ind_rr)) { $ind_cost += $ind_rr*$inducements['Extra Training']['cost']; $ind_count += 1; }
if (isset($ind_chef)) { $ind_cost += $ind_chef*$chef_cost; $ind_count += 1;}
if (isset($ind_wiz)) { $ind_cost += $ind_wiz*$inducements['Hireling Sports-Wizard']['cost']; $ind_count += 1; }
//	added BB2016 - Spike inducements
//if (isset($ind_###)) { $ind_cost += $ind_###*$inducements['#######']['cost']; }
if (isset($ind_hxs)) { $ind_cost += $ind_hxs*$inducements['Horatio X Schottenheim']['cost']; $ind_count += 1; }
if (isset($ind_fdf)) { $ind_cost += $ind_fdf*$inducements['Fink Da Fixer']['reduced_cost']; $ind_count += 1; }
if (isset($ind_psb)) { $ind_cost += $ind_psb*$inducements['Papa Skullbones']['reduced_cost']; $ind_count += 1; }
if (isset($ind_gsw)) { $ind_cost += $ind_gsw*$inducements['Galandril Silverwater']['reduced_cost']; $ind_count += 1; }
if (isset($ind_ksw)) { $ind_cost += $ind_ksw*$inducements['Krot Shockwhisker']['reduced_cost']; $ind_count += 1; }
if (isset($ind_kcs)) { $ind_cost += $ind_kcs*$inducements['Kari Coldsteel']['reduced_cost']; $ind_count += 1; }
if (isset($ind_csr)) { $ind_cost += $ind_csr*$inducements['Chaos Sorcerer']['reduced_cost']; $ind_count += 1; }
if (isset($ind_sac)) { $ind_cost += $ind_sac*$inducements['Specialist Assisstant Coaches']['cost']; $ind_count += 1; }
if (isset($ind_tem)) { $ind_cost += $ind_tem*$inducements['Temp Agency Cheerleaders']['cost']; $ind_count += 1; }
if (isset($ind_wxm)) { $ind_cost += $ind_wxm*$inducements['Weather Mage']['cost']; $ind_count += 1; }
if (isset($ind_dss)) { $ind_cost += $ind_dss*$inducements['Druchii Sports Sorceress']['reduced_cost']; $ind_count += 1; }
if (isset($ind_pdr)) { $ind_cost += $ind_pdr*$inducements['Plague Doctors']['reduced_cost']; $ind_count += 1; }
if (isset($ind_cvn)) { $ind_cost += $ind_cvn*$inducements['Cavorting Nurglings']['reduced_cost']; $ind_count += 1; }
if (isset($ind_phn)) { $ind_cost += $ind_phn*$inducements['Plague Horticulturalist of Nurgle']['reduced_cost']; $ind_count += 1; }
if (isset($ind_dmb)) { $ind_cost += $ind_dmb*$inducements['Joseph Bugman, Dwarf Master Brewer']['reduced_cost']; $ind_count += 1; }
if (isset($ind_spn)) { $ind_cost += $ind_spn*$inducements['Sports Necrotheurge']['reduced_cost']; $ind_count += 1; }
if (isset($ind_hhp)) { $ind_cost += $ind_hhp*$inducements['Halfling Hot-Pot']['reduced_cost']; $ind_count += 1; }
if (isset($ind_bhb)) { $ind_cost += $ind_bhb*$inducements['Bottle of Heady Brew']['reduced_cost']; $ind_count += 1; }
if (isset($ind_smp)) { $ind_cost += $ind_smp*$inducements['Slann Mage-Priest']['reduced_cost']; $ind_count += 1; }
if (isset($ind_rok)) { $ind_cost += $ind_rok*$inducements['Riotous Rookies']['reduced_cost']; $ind_count += 1; }
if (isset($ind_fbl)) { $ind_cost += $ind_fbl*$inducements['Firebelly']['reduced_cost']; $ind_count += 1; }
if (isset($ind_ngs)) { $ind_cost += $ind_ngs*$inducements['Night Goblin Sports Shaman']['reduced_cost']; $ind_count += 1; }

//print_box($x, $y, $w, $h, $bgcolor='#FFFFFF', $bordercolor='#000000', $linewidth=1, $borderstyle, $fontsize, $font, $bold=false, $align, $text)
$h = 13; // Height of cells

if ($ind_count > 0 ) {
  $pdf->print_box($currentx, $currenty, 170, $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 0, 0, 8, 'Tahoma', true, 'R', 'Inducements ');
  $pdf->print_box(($currentx += 170), $currenty, 120, $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 0, 0, 8, 'Tahoma', false, 'L', '(for next match)');
  $pdf->print_box(($currentx = 630), $currenty, 40, $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 0, 0, 8, 'Tahoma', true, 'R', 'Team Goods'); // 156 to margin
}

$currentx = MARGINX;
$currenty = 435;
$ind_display_counter = 0;

  // print_inducements($x, $y, $h, $bgcol, $linecol, $fontsize, $ind_name, $ind_amount, $ind_value)
  if (isset($ind_babes)) { $pdf->print_inducements($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 8, 'Bloodweiser Babes (0-2):', $ind_babes, $pdf->Mf($inducements['Bloodweiser Babes']['cost']));}
  if (isset($ind_babes)) { $ind_display_counter += 1;}
  if ($ind_display_counter == 9 ) {$currentx += 250; $currenty = 435;} 
  if (isset($ind_bribes)) { $pdf->print_inducements($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 8, 'Bribes (0-3):', $ind_bribes, $pdf->Mf($bribe_cost));}
  if (isset($ind_bribes)) { $ind_display_counter += 1;}
  if ($ind_display_counter == 9 ) {$currentx += 250; $currenty = 435;} 
  if (isset($ind_rr)) { $pdf->print_inducements($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 8, 'Extra Training (0-4):', $ind_rr, $pdf->Mf($inducements['Extra Training']['cost']));}
  if (isset($ind_rr)) { $ind_display_counter += 1;}
  if ($ind_display_counter == 9 ) {$currentx += 250; $currenty = 435;} 
  if (isset($ind_chef)) { $pdf->print_inducements($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 8, 'Halfling Master Chef (0-1):', $ind_chef, $pdf->Mf($chef_cost));}
  if (isset($ind_chef)) { $ind_display_counter += 1;}
  if ($ind_display_counter == 9 ) {$currentx += 250; $currenty = 435;}
  if (isset($ind_igor) or isset($ind_apo)) { $pdf->print_inducements($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 8, $apo_igor, $ind_apo_igor, $pdf->Mf($ind_apo_igor_cost));}
  if (isset($ind_igor) or isset($ind_apo)) { $ind_display_counter += 1;}
  if ($ind_display_counter == 9 ) {$currentx += 250; $currenty = 435;}
  if (isset($ind_wiz)) { $pdf->print_inducements($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 8, 'Hireling Sports-Wizard (0-1):', $ind_wiz, $pdf->Mf($inducements['Hireling Sports-Wizard']['cost']));}
  if (isset($ind_wiz)) { $ind_display_counter += 1;}
  if ($ind_display_counter == 9 ) {$currentx += 250; $currenty = 435;}
  //	added BB2016 - Spike inducements
  if (isset($ind_hxs)) { $pdf->print_inducements($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 8, 'Horatio X Schottenheim (0-1):', $ind_hxs, $pdf->Mf($inducements['Horatio X Schottenheim']['cost']));}
  if (isset($ind_hxs)) { $ind_display_counter += 1;}
  if ($ind_display_counter == 9 ) {$currentx += 250; $currenty = 435;}
  if (isset($ind_fdf)) { $pdf->print_inducements($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 8, 'Fink Da Fixer (0-1):', $ind_fdf, $pdf->Mf($inducements['Fink Da Fixer']['reduced_cost']));}
  if (isset($ind_fdf)) { $ind_display_counter += 1;}
  if ($ind_display_counter == 9 ) {$currentx += 250; $currenty = 435;}
  if (isset($ind_psb)) { $pdf->print_inducements($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 8, 'Papa Skullbones (0-1):', $ind_rr, $pdf->Mf($inducements['Papa Skullbones']['reduced_cost']));}
  if (isset($ind_psb)) { $ind_display_counter += 1;}
  if ($ind_display_counter == 9 ) {$currentx += 250; $currenty = 435;}
  if (isset($ind_gsw)) { $pdf->print_inducements($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 8, 'Galandril Silverwater (0-1):', $ind_psb, $pdf->Mf($inducements['Galandril Silverwater']['reduced_cost']));}
  if (isset($ind_gsw)) { $ind_display_counter += 1;}
  if ($ind_display_counter == 9 ) {$currentx += 250; $currenty = 435;}
  if (isset($ind_ksw)) { $pdf->print_inducements($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 8, 'Krot Shockwhisker (0-1):', $ind_ksw, $pdf->Mf($inducements['Krot Shockwhisker']['reduced_cost']));}
  if (isset($ind_ksw)) { $ind_display_counter += 1;}
  if ($ind_display_counter == 9 ) {$currentx += 250; $currenty = 435;}
  if (isset($ind_kcs)) { $pdf->print_inducements($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 8, 'Kari Coldsteel (0-1):', $ind_kcs, $pdf->Mf($inducements['Kari Coldsteel']['reduced_cost']));}
  if (isset($ind_kcs)) { $ind_display_counter += 1;}
  if ($ind_display_counter == 9 ) {$currentx += 250; $currenty = 435;}
  if (isset($ind_csr)) { $pdf->print_inducements($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 8, 'Chaos Sorcerer (0-1):', $ind_csr, $pdf->Mf($inducements['Chaos Sorcerer']['reduced_cost']));}
  if (isset($ind_csr)) { $ind_display_counter += 1;}
  if ($ind_display_counter == 9 ) {$currentx += 250; $currenty = 435;}
  if (isset($ind_sac)) { $pdf->print_inducements($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 8, 'Specialist Assisstant Coaches (0-20):', $ind_sac, $pdf->Mf($inducements['Specialist Assisstant Coaches']['cost']));}
  if (isset($ind_sac)) { $ind_display_counter += 1;}
  if ($ind_display_counter == 9 ) {$currentx += 250; $currenty = 435;}
  if (isset($ind_tem)) { $pdf->print_inducements($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 8, 'Temp Agency Cheerleaders (0-20):', $ind_tem, $pdf->Mf($inducements['Temp Agency Cheerleaders']['cost']));}
  if (isset($ind_tem)) { $ind_display_counter += 1;}
  if ($ind_display_counter == 9 ) {$currentx += 250; $currenty = 435;}
  if (isset($ind_wxm)) { $pdf->print_inducements($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 8, 'Weather Mage (0-1):', $ind_wxm, $pdf->Mf($inducements['Weather Mage']['cost']));}
  if (isset($ind_wxm)) { $ind_display_counter += 1;}
  if ($ind_display_counter == 9 ) {$currentx += 250; $currenty = 435;}
  if (isset($ind_dss)) { $pdf->print_inducements($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 8, 'Druchii Sports Sorceress (0-1):', $ind_dss, $pdf->Mf($inducements['Druchii Sports Sorceress']['reduced_cost']));}
  if (isset($ind_dss)) { $ind_display_counter += 1;}
  if ($ind_display_counter == 9 ) {$currentx += 250; $currenty = 435;}
  if (isset($ind_pdr)) { $pdf->print_inducements($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 8, 'Plague Doctors (0-1):', $ind_pdr, $pdf->Mf($inducements['Plague Doctors']['reduced_cost']));}
  if (isset($ind_pdr)) { $ind_display_counter += 1;}
  if ($ind_display_counter == 9 ) {$currentx += 250; $currenty = 435;}
  if (isset($ind_cvn)) { $pdf->print_inducements($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 8, 'Cavorting Nurglings (0-1):', $ind_cvn, $pdf->Mf($inducements['Cavorting Nurglings']['reduced_cost']));}
  if (isset($ind_cvn)) { $ind_display_counter += 1;}
  if ($ind_display_counter == 9 ) {$currentx += 250; $currenty = 435;}
  if (isset($ind_phn)) { $pdf->print_inducements($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 8, 'Plague Horticulturalist of Nurgle (0-1):', $ind_phn, $pdf->Mf($inducements['Plague Horticulturalist of Nurgle']['reduced_cost']));}
  if (isset($ind_phn)) { $ind_display_counter += 1;}
  if ($ind_display_counter == 9 ) {$currentx += 250; $currenty = 435;}
  if (isset($ind_dmb)) { $pdf->print_inducements($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 8, 'Joseph Bugman, Dwarf Master Brewer (0-1):', $ind_dmb, $pdf->Mf($inducements['Joseph Bugman, Dwarf Master Brewer']['reduced_cost']));}
  if (isset($ind_dmb)) { $ind_display_counter += 1;}
  if ($ind_display_counter == 9 ) {$currentx += 250; $currenty = 435;}
  if (isset($ind_spn)) { $pdf->print_inducements($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 8, 'Sports Necrotheurge (0-1):', $ind_spn, $pdf->Mf($inducements['Sports Necrotheurge']['reduced_cost']));}
  if (isset($ind_spn)) { $ind_display_counter += 1;}
  if ($ind_display_counter == 9 ) {$currentx += 250; $currenty = 435;}
  if (isset($ind_hhp)) { $pdf->print_inducements($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 8, 'Halfling Hot-Pot (0-1):', $ind_hhp, $pdf->Mf($inducements['Halfling Hot-Pot']['reduced_cost']));}
  if (isset($ind_hhp)) { $ind_display_counter += 1;}
  if ($ind_display_counter == 9 ) {$currentx += 250; $currenty = 435;}
  if (isset($ind_bhb)) { $pdf->print_inducements($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 8, 'Bottle of Heady Brew (0-3):', $ind_bhb, $pdf->Mf($inducements['Bottle of Heady Brew']['reduced_cost']));}
  if (isset($ind_bhb)) { $ind_display_counter += 1;}
  if ($ind_display_counter == 9 ) {$currentx += 250; $currenty = 435;}
  if (isset($ind_smp)) { $pdf->print_inducements($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 8, 'Slann Mage-Priest (0-1):', $ind_smp, $pdf->Mf($inducements['Slann Mage-Priest']['reduced_cost']));}
  if (isset($ind_smp)) { $ind_display_counter += 1;}
  if ($ind_display_counter == 9 ) {$currentx += 250; $currenty = 435;}
  if (isset($ind_rok)) { $pdf->print_inducements($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 8, 'Riotous Rookies (0-1):', $ind_rok, $pdf->Mf($inducements['Riotous Rookies']['reduced_cost']));}
  if (isset($ind_rok)) { $ind_display_counter += 1;}
  if ($ind_display_counter == 9 ) {$currentx += 250; $currenty = 435;}
  if (isset($ind_fbl)) { $pdf->print_inducements($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 8, 'Firebelly (0-1):', $ind_fbl, $pdf->Mf($inducements['Firebelly']['reduced_cost']));}
  if (isset($ind_fbl)) { $ind_display_counter += 1;}
  if ($ind_display_counter == 9 ) {$currentx += 250; $currenty = 435;}
  if (isset($ind_ngs)) { $pdf->print_inducements($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 8, 'Night Goblin Sports Shaman (0-1):', $ind_ngs, $pdf->Mf($inducements['Night Goblin Sports Shaman']['reduced_cost']));}
  if (isset($$ind_ngs)) { $ind_display_counter += 1;}
  if ($ind_display_counter == 9 ) {$currentx += 250; $currenty = 435;}
  //  end of BB2016 - Spike inducements
  if (isset($ind_card)) { $pdf->print_inducements(MARGINX, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 8, 'Card budget:', ' ', $pdf->Mf($ind_card));}
  if (isset($ind_card)) { $ind_display_counter += 1;}
  if ($ind_display_counter == 9 ) {$currentx += 250; $currenty = 435;}

$currenty=435;
$currentx=630;
// print_team_goods($x, $y, $h, $bgcol, $linecol, $perm_name, $perm_nr, $perm_value, $perm_total_value, $bold=false)
$pdf->print_team_goods($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 'Rerolls:', ($team->rerolls), $pdf->Mf($rerollcost), $pdf->Mf($team->rerolls * $rerollcost), false);
$pdf->print_team_goods($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 'Fan Factor:', ($team->rg_ff), $pdf->Mf($rules['cost_fan_factor']), $pdf->Mf($team->rg_ff * $rules['cost_fan_factor']), false);
$pdf->print_team_goods($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 'Assistant Coaches:', ($team->ass_coaches), $pdf->Mf($rules['cost_ass_coaches']), $pdf->Mf($team->ass_coaches * $rules['cost_ass_coaches']), false);
$pdf->print_team_goods($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 'Cheerleaders:', ($team->cheerleaders), $pdf->Mf($rules['cost_cheerleaders']), $pdf->Mf($team->cheerleaders * $rules['cost_cheerleaders']), false);
if ($r == 'Undead' || $r == 'Necromantic') // Swap Apothecary for Necromancer
  $pdf->print_team_goods($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 'Necromancer:', 1, 0, 0, false);
elseif ($r == 'Nurgle' || $r == 'Khemri')  // Remove Apothecary
  $currenty+=$h;
else  // Normal case
  $pdf->print_team_goods($currentx, ($currenty+=$h), $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 'Apothecary:', ($team->apothecary), $pdf->Mf($rules['cost_apothecary']), $pdf->Mf($team->apothecary * $rules['cost_apothecary']), false);
$pdf->print_box($currentx+=70, ($currenty+=$h), 40, $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 0, 0, 8, 'Tahoma', false, 'R', 'Treasury:' );
$pdf->print_box($currentx+=40, ($currenty), 65, $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 0, 0, 8, 'Tahoma', false, 'R', $pdf->Mf($team->treasury));

// Team Value, Inducements Value, Match Value
$h=13;
$pdf->print_box($currentx-=40, ($currenty+=$h), 40, $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 0, 0, 8, 'Tahoma', true, 'R', 'Team Value (incl MNGs value):');
$pdf->print_box($currentx+=40, ($currenty), 65, $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 0, 0, 8, 'Tahoma', true, 'R', $pdf->Mf($team->value+$sum_p_missing_value));
$pdf->print_box($currentx-=40, ($currenty+=$h), 40, $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 0, 0, 8, 'Tahoma', true, 'R', 'Induced Value:');
$pdf->print_box($currentx+=40, ($currenty), 65, $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 0, 0, 8, 'Tahoma', true, 'R', $pdf->Mf($ind_cost));
$pdf->print_box($currentx-=40, ($currenty+=$h), 40, $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 0, 0, 8, 'Tahoma', true, 'R', 'Match Value (TV for match):');
$pdf->print_box($currentx+=40, ($currenty), 65, $h, COLOR_ROSTER_NORMAL, DEFLINECOLOR, 0, 0, 8, 'Tahoma', true, 'R', $pdf->Mf($team->value + $ind_cost));

// Drawing a rectangle around inducements
$pdf->SetLineWidth(0.6);
$pdf->RoundedRect(MARGINX+6, 435, 792, 130, 5, 'D');

global $settings;
if ($settings['enable_pdf_logos']) {
    // Team logo
    // Comment out if you dont have GD 2.x installed, or if you dont want the logo in roster.
    // Not tested with anything except PNG images that comes with OBBLM.
    if ($ind_count < 10) {
    $img = new ImageSubSys(IMGTYPE_TEAMLOGO,$team->team_id);
    $pdf->Image($img->getPath(),346,436,128,128,'','',false,0);
    }
    // OBBLM text lower left corner as a pic - removed due issues with it appearing multiple places
    // $pdf->Image('modules/pdf/OBBLM_pdf_logo.png', MARGINX+12, 534, 60, 28, '', '', false, 0);
}

// Color legends
$pdf->SetFont('Tahoma', '', 8);
$currentx = MARGINX+16;
$currenty = 572;
$pdf->SetFillColorBB($pdf->hex2cmyk(COLOR_ROSTER_MNG));
$pdf->SetXY($currentx, $currenty);
$pdf->Rect($currentx, $currenty, 5, 5, 'DF');
$pdf->SetXY($currentx+=5, $currenty-=1);
$pdf->Cell(20, 8, 'MNG', 0, 0, 'L', false);
$pdf->SetFillColorBB($pdf->hex2cmyk(COLOR_ROSTER_JOURNEY));
$pdf->Rect($currentx+=22+5, $currenty+=1, 5, 5, 'DF');
$pdf->SetXY($currentx+=5, $currenty-=1);
$pdf->Cell(45, 8, 'Journeyman', 0, 0, 'L', false);
$pdf->SetFillColorBB($pdf->hex2cmyk(COLOR_ROSTER_JOURNEY_USED));
$pdf->Rect($currentx+=47+5, $currenty+=1, 5, 5, 'DF');
$pdf->SetXY($currentx+=5, $currenty-=1);
$pdf->Cell(45, 8, 'Used journeyman', 0, 0, 'L', false);
$pdf->SetFillColorBB($pdf->hex2cmyk(COLOR_ROSTER_NEWSKILL));
$pdf->Rect($currentx+=67+5, $currenty+=1, 5, 5, 'DF');
$pdf->SetXY($currentx+=5, $currenty-=1);
$pdf->Cell(70, 8, 'New skill available', 0, 0, 'L', false);
$pdf->SetFillColorBB($pdf->hex2cmyk(COLOR_ROSTER_CHR_GTP1));
$pdf->Rect($currentx+=70+5, $currenty+=1, 5, 5, 'DF');
$pdf->SetXY($currentx+=5, $currenty-=1);
$pdf->Cell(50, 8, 'Stat upgrade', 0, 0, 'L', false);
$pdf->SetFillColorBB($pdf->hex2cmyk(COLOR_ROSTER_CHR_LTM1));
$pdf->Rect($currentx+=50+5, $currenty+=1, 5, 5, 'DF');
$pdf->SetXY($currentx+=5, $currenty-=1);
$pdf->Cell(50, 8, 'Stat downgrade', 0, 0, 'L', false);

$pdf->SetFont('Tahoma', '', 7);
$pdf->SetFillColorBB($pdf->hex2cmyk(COLOR_ROSTER_NORMAL));
$pdf->SetXY($currentx+160, $currenty+1);        
$donate = "Please consider donating to the OBBLM project if you enjoy this software and wish to support\n further development and maintenance. For more information visit nicholasmr.dk";
$pdf->Multicell(300, 8, $donate, 0, 0, 'L', false);

// Output the PDF document
$pdf->Output(utf8_decode($team->name) . date(' Y-m-d') . '.pdf', 'I');

}
}