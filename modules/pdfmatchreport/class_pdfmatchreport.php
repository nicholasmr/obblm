<?php
/*
 * Code for generating Match reports in PDF format, for use in table-top settings 
 *
 * Author Daniel Henriksson, 2010
 * based on work by Daniel Straalman
 *
 */
 
require_once('modules/pdf/bb_pdf_class.php');

class PDFMatchReport implements ModuleInterface
{

    public static function getModuleAttributes()
    {
        return array(
            'author'     => 'Daniel Henriksson & Nicholas Rathmann',
            'moduleName' => 'PDF match report',
            'date'       => '2010-2012',
            'setCanvas'  => false, 
        );
    }

    public static function getModuleTables(){return array();}

    public static function getModuleUpgradeSQL(){return array();}

    public static function triggerHandler($type, $argv){}

    /**
     * Called by Module::run, renders the actual PDF
     */
    public static function main($argv)
    {

        global $pdf;
        global $DEA;
        global $skillarray;
        global $rules;
        global $lng;
        global $inducements;

        $FILLED = false;

        if (!empty($argv)) {
            $team1 = new Team($argv[0]);
            $team2 = new Team($argv[1]);
            $match = new Match($argv[2]);
            if (!is_null($team1) && !is_null($team2)) {
                $FILLED = true;
            }
        }
        else {
            $team1 = null;
            $team2 = null;
            $match = null;
        }

        define("MARGINX", 20);
        define("MARGINY", 20);
        define("DEFLINECOLOR", '#000000');
        define("HEADLINEBGCOLOR", '#c3c3c3');
        
        # player statuses
        define('COLOR_ROSTER_NORMAL',   COLOR_HTML_NORMAL);
        define('COLOR_ROSTER_READY',    COLOR_HTML_READY);
        define('COLOR_ROSTER_MNG',      COLOR_HTML_MNG);
        define('COLOR_ROSTER_DEAD',     COLOR_HTML_DEAD);
        define('COLOR_ROSTER_SOLD',     COLOR_HTML_SOLD);
        define('COLOR_ROSTER_STARMERC', COLOR_HTML_STARMERC);
        define('COLOR_ROSTER_JOURNEY',  COLOR_HTML_JOURNEY);

        define('T_PDF_ROSTER_SET_EMPTY_ON_ZERO', true); # Prints cp, td etc. as '' (empty string) when field = 0.

        $pdf=new BB_PDF('P','pt','A4'); // Creating a new PDF doc. Portrait, scale=pixels, size A4
        $pdf->SetAutoPageBreak(false, 20); // No auto page break to mess up layout

        $pdf->SetAuthor('Daniel Straalman, Daniel Henriksson, Nicholas Rathmann');
        $pdf->SetCreator('OBBLM');

        $pdf->SetTitle($lng->getTrn('name', 'PDFMatchReport'));
        $pdf->SetSubject($lng->getTrn('name', 'PDFMatchReport'));

        $pdf->AddFont('Tahoma','','tahoma.php');  // Adding regular font Tahoma which is in font dir
        $pdf->AddFont('Tahoma','B','tahomabd.php');  // Adding Tahoma Bold

        // Initial settings
        $pdf->SetFont('Tahoma','B',14);
        $pdf->AddPage();
        $pdf->SetLineWidth(1.5);
        $currentx = MARGINX + 6;
        $currenty = MARGINY + 6;


        //Real PDF fill starts here
        $pdf->SetFillColorBB($pdf->hex2cmyk(HEADLINEBGCOLOR));
#        $pdf->RoundedRect($currentx, $currenty, 542, 20, 6, 'DF'); // Filled rectangle around Team headline
        $pdf->SetDrawColorBB($pdf->hex2cmyk(DEFLINECOLOR));

        // Text in headline
        $pdf->SetXY($currentx+30,$currenty);
        $pdf->SetFont('Tahoma','B',22);
        $pdf->Cell(360, 20, 'OBBLM Match Report', 0, 0, 'R', false, '');


        //Printing game info rounded box
        $currenty+=15;

        $pdf->SetFillColorBB($pdf->hex2cmyk('#c6c6c6'));
        $pdf->SetDrawColorBB($pdf->hex2cmyk('#000000'));
        $pdf->SetFontSize(1);
        $pdf->SetLineWidth(0.6);
        #$pdf->RoundedRect($currentx, $currenty, 542, 80, 5, 'D'); 

        //Score boxes
        $pdf->SetLineWidth(0.4);
        $scoreboxOffset = 20;
#        $pdf->RoundedRect($currentx + 15, $currenty + $scoreboxOffset, 50, 50, 5, 'D'); 
#        $pdf->RoundedRect($currentx + 475, $currenty + $scoreboxOffset, 50, 50, 5, 'D'); 
        if ($FILLED) {
            $img1 = new ImageSubSys(IMGTYPE_TEAMLOGO,$team1->team_id);
            $img2 = new ImageSubSys(IMGTYPE_TEAMLOGO,$team2->team_id);
            $pdf->Image($img1->getPath(),$currentx -10,  $currenty + $scoreboxOffset, 60,60,'','',false,0);
            $pdf->Image($img2->getPath(),$currentx + 495, $currenty + $scoreboxOffset, 60,60,'','',false,0);
        }

        //VS text
        $currentx += 80;
        $currenty += 20;
        $pdf->SetXY($currentx,$currenty);
        $pdf->SetFont('Tahoma','',16);
        $noname = '__________________';
        
        $pdf->Cell(390, 50, (!$FILLED ? $noname : $team1->name).' - '.(!$FILLED ? $noname : $team2->name), 0, 0, 'C', false, '');

        // Gate + score text
        $pdf->SetFont('Tahoma','',11);
        $space = '        ';
        $currenty += 26;
        $pdf->SetXY($currentx,$currenty);
        $pdf->Cell(210, 50, "Score:       -", 0, 0, 'R', false, '');
        $currenty += 18;
        $pdf->SetXY($currentx,$currenty);
        $pdf->Cell(210, 50, "Gate:${space}k", 0, 0, 'R', false, '');

        //Printing headers for player rows. Do all this twice
        $smallFieldSize = 25;
        $mediumFieldSize = 40;
        $nameFieldSize = 140;


        //Print two team boxes
        
        $currenty += 20;
        $i = 0;
        while ($i < 2) {
            $i++;

            $currenty += 20;
            $currentx = MARGINX + 6;
            $pdf->SetXY($currentx,$currenty);

            $pdf->SetFillColorBB($pdf->hex2cmyk('#c6c6c6'));
            $pdf->SetDrawColorBB($pdf->hex2cmyk('#000000'));
            $pdf->SetFontSize(1);
            $pdf->SetLineWidth(0.6);
            #$pdf->RoundedRect($currentx, $currenty, 542, 315, 5, 'D'); 

            $currenty += $scoreboxOffset;
            $currentx += 15;
            $pdf->SetXY($currentx,$currenty);

            //Print Home / Away Images
            if ($i == 1) {
                $pdf->Image('modules/pdfmatchreport/home.png', $currentx-10, $currenty + 90, 40, 163, '', '', false, 0);
            } else {
                $pdf->Image('modules/pdfmatchreport/away.png', $currentx-10, $currenty + 90, 40, 161, '', '', false, 0);
            }

            
            $currentx += 45;
            $pdf->SetXY($currentx,$currenty);
   
            $h = 20; 
            $pdf->SetFillColorBB($pdf->hex2cmyk(HEADLINEBGCOLOR));
            $pdf->SetDrawColorBB($pdf->hex2cmyk(DEFLINECOLOR));
            $pdf->SetFont('Tahoma','B',8);
            $pdf->setLineWidth(1.5);

            // Printing headline for team table
            $pdf->Cell(50, $h, 'Winnings', 1, 0, 'L', true, '');
            $pdf->Cell(60, $h, 'Fan Factor', 1, 0, 'C', true, '');
            $pdf->Cell(70, $h, 'Total team CAS', 1, 0, 'C', true, '');
            $pdf->Cell(35, $h, 'FAME', 1, 0, 'C', true, '');
            
            if (1 && $FILLED) {
                    $pdf->SetFont('Tahoma','B',11);
                    $pdf->Cell(150, $h, '   '.${"team$i"}->name, 0, 0, 'L', false, '');
            }         
            
            $currenty += 23;
            $pdf->SetXY($currentx,$currenty);

            $h=15;  // Row/cell height for team table row
            $pdf->SetFillColorBB($pdf->hex2cmyk('#FFFFFF'));
            $pdf->SetDrawColorBB($pdf->hex2cmyk('#000000'));
            $pdf->SetFontSize(1);
            $pdf->SetLineWidth(0.6);

            //Team table row
            $pdf->Cell(50, $h, '', 1, 0, 'L', true, '');

            //Fan factor box
            $pdf->SetFont('Tahoma', '', 8);
            $boxx = $currentx +50;
            $boxy = $currenty;
            $pdf->SetXY($boxx += 4, $boxy += 5);
            $pdf->Rect($boxx, $boxy, 5, 5, 'DF');
            $pdf->SetXY($boxx += 5, $boxy -=1);
            $pdf->Cell(20, 8, '+1', 0, 0, 'L', false);

            $pdf->SetXY($boxx += 16, $boxy +=1);
            $pdf->Rect($boxx, $boxy, 5, 5, 'DF');
            $pdf->SetXY($boxx += 5, $boxy -=1);
            $pdf->Cell(20, 8, '0', 0, 0, 'L', false);
                
            $pdf->SetXY($boxx += 12, $boxy += 1);
            $pdf->Rect($boxx, $boxy, 5, 5, 'DF');
            $pdf->SetXY($boxx += 5, $boxy -=1);
            $pdf->Cell(20, 8, '-1', 0, 0, 'L', false);
                
            //Total team cas, FAME
            $pdf->SetXY($currentx + 110, $currenty);
            $pdf->Cell(70, $h, '', 1, 0, 'C', true, '');
            $pdf->Cell(35, $h, '', 1, 0, 'C', true, '');

            if (1 && $FILLED) {
                    $t = ${"team$i"};
                    $pdf->SetFont('Tahoma','',8);
                   $statsstr = sprintf('TV: %uk  -  Played: %u  -  Win pct.: %1.0f  -  ELO: %1.0f  -  CAS inflicted: %u', $t->tv/1000, $t->mv_played, $t->rg_win_pct, $t->rg_elo, $t->mv_cas);
#                    $statsstr = sprintf('TV: %uk', $t->tv/1000);
                    $pdf->Cell(250, $h, '    '.$statsstr, 0, 0, 'L', false, '');
            }         

            $currenty += $h + 10;
            $pdf->SetXY($currentx,$currenty);
            
            //Headers for player table
            $h = 20;
            $pdf->SetFillColorBB($pdf->hex2cmyk(HEADLINEBGCOLOR));
            $pdf->SetDrawColorBB($pdf->hex2cmyk(DEFLINECOLOR));
            $pdf->SetFont('Tahoma','B',8);
            $pdf->setLineWidth(1.5);

            // Printing headline for player table
            $pdf->Cell($smallFieldSize, $h, 'Nr', 1, 0, 'C', true, '');
            $pdf->Cell($nameFieldSize, $h, 'Name', 1, 0, 'L', true, '');
            $pdf->Cell($smallFieldSize, $h, 'MVP', 1, 0, 'C', true, '');
            $pdf->Cell($smallFieldSize, $h, 'Cp', 1, 0, 'C', true, '');
            $pdf->Cell($smallFieldSize, $h, 'Td', 1, 0, 'C', true, '');
            $pdf->Cell($smallFieldSize, $h, 'Int', 1, 0, 'C', true, '');
            $pdf->Cell($smallFieldSize, $h, 'BH', 1, 0, 'C', true, '');
            $pdf->Cell($smallFieldSize, $h, 'SI', 1, 0, 'C', true, '');
            $pdf->Cell($smallFieldSize, $h, 'Ki', 1, 0, 'C', true, '');
            $pdf->Cell($mediumFieldSize, $h, 'IR D1', 1, 0, 'C', true, '');
            $pdf->Cell($mediumFieldSize, $h, 'IR D2', 1, 0, 'C', true, '');
            $pdf->Cell($mediumFieldSize, $h, 'Inj', 1, 0, 'C', true, '');

            $currenty+=23;

            $pdf->SetXY($currentx,$currenty);
            $pdf->SetFont('Tahoma', '', 8);
            $h=15;  // Row/cell height for player table
            $pdf->SetFillColorBB($pdf->hex2cmyk('#FFFFFF'));
            $pdf->SetDrawColorBB($pdf->hex2cmyk('#000000'));
            $pdf->SetFontSize(10);
            $pdf->SetLineWidth(0.6);

            // Printing player rows            
            $tmp_players = array();
            if ($FILLED) {
                $players = ${"team$i"}->getPlayers();
                foreach ($players as $p) {
                    if (!Match::player_validation($p, $match))
                        continue;
                    array_push($tmp_players, $p);
                }
            }
            $players = $tmp_players;
            
            $j=0;
            while ($j < 14) {
                $j++;

                $nr = '';
                $name = '';
                $bgc = COLOR_ROSTER_NORMAL;
                if (count($players) >= $j) {
                  $p = $players[$j-1];
                  
                  $name = $p->name;
                  $bgc = COLOR_ROSTER_NORMAL;
                  if ($p->is_journeyman) {
                       $name = "$p->name [J]";
                       $bgc = COLOR_ROSTER_JOURNEY;
                  }
                  if ($p->is_mng) {
                       $name = "$p->name [MNG]";
                       $bgc = COLOR_ROSTER_MNG;
                  }

                  $nr = $p->nr;
                }
                
                $pdf->SetFillColorBB($pdf->hex2cmyk($bgc));
                
                $pdf->Cell($smallFieldSize, $h, $nr, 1, 0, 'C', true, '');
                $pdf->Cell($nameFieldSize, $h, $name, 1, 0, 'L', true, '');
                
                
                
                $pdf->Cell($smallFieldSize, $h, '', 1, 0, 'C', true, '');
                $pdf->Cell($smallFieldSize, $h, '', 1, 0, 'C', true, '');
                $pdf->Cell($smallFieldSize, $h, '', 1, 0, 'C', true, '');
                $pdf->Cell($smallFieldSize, $h, '', 1, 0, 'C', true, '');
                $pdf->Cell($smallFieldSize, $h, '', 1, 0, 'C', true, '');
                $pdf->Cell($smallFieldSize, $h, '', 1, 0, 'C', true, '');
                $pdf->Cell($smallFieldSize, $h, '', 1, 0, 'C', true, '');
                $pdf->Cell($mediumFieldSize, $h, '', 1, 0, 'C', true, '');
                $pdf->Cell($mediumFieldSize, $h, '', 1, 0, 'C', true, '');
                $pdf->Cell($mediumFieldSize, $h, '', 1, 0, 'C', true, '');

                $currenty+=$h;
                $pdf->SetXY($currentx,$currenty);
            }
        }

        // end
        $pdf->SetFont('Tahoma', '', 7);
        $currenty = 800;
        $pdf->SetXY(MARGINX, $currenty);        
        $donate = "Please consider donating to the OBBLM project if you enjoy this software and wish to support\n further development and maintenance. For more information visit nicholasmr.dk";
        $pdf->Multicell(300, 7, $donate, 0, 0, 'L', false);

        $currentx = MARGINX + 330;
        $pdf->SetXY($currentx, $currenty);
#        $pdf->Cell(20, 8, 'Created by Daniel Henriksson & Nicholas Rathmann, 2010-2012', 0, 0, 'L', false);

        // Output the PDF document
        $pdf->Output("Match Report.pdf", 'I');
    }
}

?>
