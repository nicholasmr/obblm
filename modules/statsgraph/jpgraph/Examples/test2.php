<?php

require_once("jpgraph/jpgraph.php");
require_once("jpgraph/jpgraph_bar.php");

// We need some data
$datay=array(4,''); // this works and displays graph OK
//$datay=array(4); // this fails with error message JPGraph Error: 25068

// Setup the graph.
$graph = new Graph(200,400);
$graph->SetScale("textlin");
$graph->img->SetMargin(30,30,30,80); // needed to fix a PHP error of too few arguments

$graph->title->Set('"GRAD_MIDVER"');
$graph->title->SetColor('darkred');

// Setup font for axis
$graph->xaxis->SetFont(FF_FONT1);
$graph->yaxis->SetFont(FF_FONT1);
//$graph->xaxis->SetTickLabels(array('05000DPF', '05000ESP'));
$graph->xaxis->SetTickLabel(array('05000DPF', '05000ESP'));
$graph->xaxis->SetLabelAngle('90');

// Create the bar pot
$bplot = new BarPlot($datay);
//$bplot->SetWidth(0.6);
$bplot->Clear();

// Setup color for gradient fill style
$bplot->SetFillGradient("navy","lightsteelblue",GRAD_MIDVER);

// Set color for the frame of each bar
$bplot->SetColor("navy");
$graph->Add($bplot);

// Finally send the graph to the browser
$graph->Stroke();
?>