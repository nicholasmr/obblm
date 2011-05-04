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
            'author'     => 'Daniel Henriksson',
            'moduleName' => 'PDF match report',
            'date'       => '2010',
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

        define("MARGINX", 20);
        define("MARGINY", 20);
        define("DEFLINECOLOR", '#000000');
        define("HEADLINEBGCOLOR", '#999999');

        define('T_PDF_ROSTER_SET_EMPTY_ON_ZERO', true); # Prints cp, td etc. as '' (empty string) when field = 0.

        $pdf=new BB_PDF('P','pt','A4'); // Creating a new PDF doc. Portrait, scale=pixels, size A4
        $pdf->SetAutoPageBreak(false, 20); // No auto page break to mess up layout

        $pdf->SetAuthor('Daniel Straalman, Daniel Henriksson');
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
        $pdf->RoundedRect($currentx, $currenty, 542, 20, 6, 'DF'); // Filled rectangle around Team headline
        $pdf->SetDrawColorBB($pdf->hex2cmyk(DEFLINECOLOR));

        // Text in headline
        $pdf->SetXY($currentx+30,$currenty);
        $pdf->SetFont('Tahoma','',12);
        $pdf->Cell(310, 20, 'OBBLM Match Report', 0, 0, 'R', false, '');


        //Printing game info rounded box
        $currenty+=40;

        $pdf->SetFillColorBB($pdf->hex2cmyk('#c6c6c6'));
        $pdf->SetDrawColorBB($pdf->hex2cmyk('#000000'));
        $pdf->SetFontSize(1);
        $pdf->SetLineWidth(0.6);
        $pdf->RoundedRect($currentx, $currenty, 542, 80, 5, 'D'); 

        //Score boxes
        $pdf->SetLineWidth(0.4);
        $scoreboxOffset = 20;
        $pdf->RoundedRect($currentx + 15, $currenty + $scoreboxOffset, 50, 50, 5, 'D'); 
        $pdf->RoundedRect($currentx + 475, $currenty + $scoreboxOffset, 50, 50, 5, 'D'); 

        //VS text
        $currentx += 80;
        $currenty += 20;
        $pdf->SetXY($currentx,$currenty);
        $pdf->SetFont('Tahoma','',18);
        $pdf->Cell(390, 50, '__________________ VS. __________________', 0, 0, 'R', false, '');

        //Gate text
        $currenty += 26;
        $pdf->SetXY($currentx,$currenty);
        $pdf->SetFont('Tahoma','',10);
        $pdf->Cell(210, 50, 'Gate:      k', 0, 0, 'R', false, '');

        //Printing headers for player rows. Do all this twice
        $smallFieldSize = 25;
        $mediumFieldSize = 40;
        $nameFieldSize = 140;


        //Print two team boxes
        $i = 0;
        while ($i < 2) {
            $i++;

            $currenty += 80;
            $currentx = MARGINX + 6;
            $pdf->SetXY($currentx,$currenty);

            $pdf->SetFillColorBB($pdf->hex2cmyk('#c6c6c6'));
            $pdf->SetDrawColorBB($pdf->hex2cmyk('#000000'));
            $pdf->SetFontSize(1);
            $pdf->SetLineWidth(0.6);
            $pdf->RoundedRect($currentx, $currenty, 542, 260, 5, 'D'); 

            $currenty += $scoreboxOffset;
            $currentx += 15;
            $pdf->SetXY($currentx,$currenty);

            //Print Home / Away Images
            if ($i == 1) {
                $pdf->Image('modules/pdfmatchreport/home.png', $currentx, $currenty + 25, 40, 163, '', '', false, 0);
            } else {
                $pdf->Image('modules/pdfmatchreport/away.png', $currentx, $currenty + 25, 40, 161, '', '', false, 0);
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
            $pdf->SetFontSize(1);
            $pdf->SetLineWidth(0.6);

            // Printing player rows
            $j=0;

            while ($j < 10) {
                $j++;

                $pdf->Cell($smallFieldSize, $h, '', 1, 0, 'C', true, '');
                $pdf->Cell($nameFieldSize, $h, '', 1, 0, 'L', true, '');
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

        //Print footer text
        $pdf->SetFont('Tahoma', '', 8);
        $currentx = MARGINX + 400;
        $currenty = 780;
        $pdf->SetXY($currentx, $currenty);
        $pdf->Cell(20, 8, 'Created by Daniel Henriksson, 2010', 0, 0, 'L', false);

        // Output the PDF document
        $pdf->Output("Match Report.pdf", 'I');
    }
}

?>
