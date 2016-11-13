<?php

/*************************
 * Local settings for league with ID = 1
 *************************/

/*********************
 *   General
 *********************/

$settings['league_name']     = 'Liga Profesional de Bloodbowl de Getafe'; // Name of the site or the league name if only one league is being managed.
$settings['league_url']      = 'https://www.facebook.com/groups/1541547409400799/'; // URL of league home page/forum, if you have such. If not then leave this empty, that is = '' (two quotes only), which will disable the button. 
$settings['league_url_name'] = 'Facebook';   // Button text for league URL.
$settings['stylesheet']      = 1;                  // Default is 1. OBBLM CSS stylesheet for non-logged in guests. Currently stylesheet 1 is the only existing stylesheet.
$settings['lang']            = 'es-ES';            // Default language. Existing: en-GB, es-ES, de-DE, fr-FR, it-IT.
$settings['fp_links']        = true;               // Default is true. Generate coach, team and player links on the front page?
$settings['welcome']         = 'Bienvenido a la liga de Bloodbowl mas violenta de Getafe.<br>Estadisticas, reports, resultados, y mucho mas.';
$settings['rules']           = 'En esta liga se utilizan las reglas de competicion vigentes del manual LRB 6.0<br>
<a target="_blank" href=http://bbgetafe.obblm.com/archivos/LRB6.pdf>Ver manual de reglas en pdf</a><br>
<br>1- ORGANIZACION DEL CAMPEONATO<br>
Este campeonato va a estar formado por 10 equipos que se enfrentaran en una liga regular de ida y vuelta en un total de 18 encuentros.
Concluida la liga regular los 4 primeros clasificados se enfrentaran en unos PLAY OFFs al mejor de 3 encuentros, tanto en la fase de semifinales como en la final.<br><br>
2- SISTEMA DE PUNTUACION<br>
VICTORIA- 3pts.<br>
EMPATE. 1pt.<br>
DERROTA- 0PTS.<br><br>
IMPORTANTE.<br> En caso de que dos equipos al final de la temporada queden empatados a puntos, se desempatara siguiendo los siguientes criterios:<br>
Primero golaverage global, despues diferencia de castigos global, y si el empate persiste, se comparara el elo del equipo.<br><br>
3- REGLAS DE LOS PLAY OFFS<br>
A- Los cuatro primeros clasificados de la liga regular se enfrentaran  primero en una semifinal al mejor de 3 encuentros, <br> y consecutivamente los 2 ganadores se enfrentaran igualmente al mejor de 3 partidos en LA GRAN FINAL DE CAMPEONATO.<br>
B- Los 2 equipos que no lleguen a la final jugaran 3 y 4 puesto.<br>
C- El dinero de los incentivos y la tesoreria podra utilizarse para contratar mercenarios o jugadores estrella en estos partidos.<br>
D- Los equipos obtendran el doble de hinchas y el doble de recaudacion durante los PLAY OFFs.<br>
E- Si durante los PLAY OFFs hay algun empate se procedera directamente a la tanda de penaltis, lanzando un total de 5 por cada equipo. ( tiradas enfrentadas con 1d6 sin posibilidad de sumar fama ni +1 de segunda oportunidad, en caso de empate en la tirada se volvera a lanzar el dado).<br>
LA GRAN FINAL. El campeon del campeonato recibira el gran trofeo de campeon, que le servira de Segunda Oportunidad para el siguiente campeonato sumandose a su valoracion.<br>
El segundo y tercer puesto tambien tendra trofeo fisico. Para adquirir estos trofeos, los participantes abonaran 5 euros al inicio de la liga<br>
NOTA. Los emparejamientos de los PLAY OFF seran: el 1 contra el 4, y el 2 contra el 3.<br><br>
PUNTOS DE INTERES<br>
1- Cualquier regla que no se refleje en este documento seguira las reglas de la version 6.0.<br>
2- La fecha oficial de comienzo de la liga es el dia 30 de Septiembre de 2016.<br>
3- Conceder un partido estara sancionado, salvo por imposibilidad de colocar suficientes jugadores en el campo, como reflejan las reglas.<br>La sancion supondra la perdida de 1 punto en la clasificacion<br>
4- Los comisarios de la liga son MIGUEL HERREROS y ANGEL GARCIA.<br>
5- Es IMPORTANTE cumplir los plazos de jugar minimo un partido cada dos semanas ( si no hay razon muy justificada se procedera a sancionar el retraso como estimen los comisarios).<br>
6- Todos los jugadores deberan  abonar la inscripcion de 5 euros lo antes posible para la compra del trofeo del ganador del torneo.<br><br>
SE BUSCA<br>
Durante esta liga, estara disponible la opcion de ofrecer recompensas por jugadores rivales. <br>Si durante un partido, por cualquier motivo, muriese un jugador con recompensa, el rival obtendra dicha recompensa<br><br>
ESTADISTICAS DE TIRADAS DE DADOS EN BLOODBOWL<br><br>
<img src="http://bbgetafe.obblm.com/archivos/StatsBB.jpg" alt="BBStats"><br><br>
VIDEOTUTORIALES DE USO Y FUNCIONAMIENTO DE LA WEB:<br><br>
Tutorial 1 - Descripcion general<br>
<iframe width="560" height="315" src="https://www.youtube.com/embed/OxXi1-ZUgr4" frameborder="0" allowfullscreen></iframe><br><br>
Tutorial 2 - Gestionar equipo<br>
<iframe width="560" height="315" src="https://www.youtube.com/embed/_Z_q7RyCkNY" frameborder="0" allowfullscreen></iframe><br><br>
Tutorial 3 - Reportar partidos<br>
<iframe width="560" height="315" src="https://www.youtube.com/embed/y0BVNCH7ojs" frameborder="0" allowfullscreen></iframe><br><br><br>
Tutorial especial - Comisario de liga<br>
<iframe width="560" height="315" src="https://www.youtube.com/embed/or_KRc7xI6w" frameborder="0" allowfullscreen></iframe>';
$settings['tourlist_foldup_fin_divs'] = false; // Default is false. If true the division nodes in the tournament lists section will automatically be folded up if all child tournaments in that division are marked as finished.
$settings['tourlist_hide_nodes'] = array('league', 'division', 'tournament'); // Default is array('league', 'division', 'tournament'). In the section tournament lists these nodes will be hidden if their contents (children) are finished. Example: If 'division' is chosen here, and all tours in a given division are finished, then the division entry will be hidden.

/*********************
 *   Rules
 *********************/

// Please use the boolean values "true" and "false" wherever default values are boolean.

$rules['max_team_players']      = 16;       // Default is 16.
$rules['static_rerolls_prices'] = false;    // Default is "false". "true" forces re-roll prices to their un-doubled values.
$rules['player_refund']         = 0;        // Player sell value percentage. Default is 0 = 0%, 0.5 = 50%, and so on.
$rules['journeymen_limit']      = 11;       // Until a team can field this number of players, it may fill team positions with journeymen.
$rules['post_game_ff']          = false;    // Default is false. Allows teams to buy and drop fan factor even though their first game has been played.

$rules['initial_treasury']      = 1000000;  // Default is 1000000.
$rules['initial_rerolls']       = 0;        // Default is 0.
$rules['initial_fan_factor']    = 0;        // Default is 0.
$rules['initial_ass_coaches']   = 0;        // Default is 0.
$rules['initial_cheerleaders']  = 0;        // Default is 0.

// For the below limits, the following applies: -1 = unlimited. 0 = disabled.
$rules['max_rerolls']           = -1;       // Default is -1.
$rules['max_fan_factor']        = 9;        // Default is 9.
$rules['max_ass_coaches']       = -1;       // Default is -1.
$rules['max_cheerleaders']      = -1;       // Default is -1.

/*********************
 *   Standings pages
 *********************/

$settings['standings']['length_players'] = 30;  // Number of entries on the general players standings table.
$settings['standings']['length_teams']   = 30;  // Number of entries on the general teams   standings table.
$settings['standings']['length_coaches'] = 30;  // Number of entries on the general coaches standings table.

/*********************
 *   Front page messageboard
 *********************/

$settings['fp_messageboard']['length']               = 12;    // Number of entries on the front page message board.
$settings['fp_messageboard']['show_team_news']       = true; // Default is true. Show team news on the front page message board.
$settings['fp_messageboard']['show_match_summaries'] = true; // Default is true. Show match summaries on the front page message board.

/*********************
 *   Front page boxes
 *********************/

/*
    The below settings define which boxes to show on the right side of the front page.

    Note, every box MUST have a UNIQUE 'box_ID' number.
    The box IDs are used to determine the order in which the boxes are shown on the front page.
    The box with 'box_ID' = 1 is shown at the top of the page, the box with 'box_ID' = 2 is displayed underneath it and so forth.
*/


/*********************
 *   Front page: tournament standings boxes
 *********************/

$settings['fp_standings'] = array(
    # This will display a standings box of the top 12 teams in node (league, division or tournament) with ID = 1
    array(
        'id'     => 6, # Node ID
        'box_ID' => 10,
        // Please note: 'type' may be either one of: 'league', 'division' or 'tournament'
        'type'   => 'tournament', # This sets the node to be a tournament. I.e. this will make a standings box for the tournament with ID = 1
        'infocus' => false, # If true a random team from the standings will be selected and its top players displayed.
        /*
            The house ranking system (HRS) NUMBER to sort the table against.
            Note, this is ignored for "type = tournament", since tours have an assigned HRS.
            Also note that using HRSs with fields such as points (pts) for leagues/divisions standings makes no sense as they are tournament specific fields (i.e. it makes no sense to sum the points for teams across different tours to get the teams' "league/division points", as the points definitions for tours may vary).
        */
        'HRS'    => 4, # Note: this must be a existing and valid HRS number from the main settings.php file.
        'title'  => 'Clasificacion de Liga 2015-16', # Table title
        'length' => 12, # Number of entries in table
        # Format: "Displayed table column name" => "OBBLM field name". For the OBBLM fields available see http://nicholasmr.dk/obblmwiki/index.php?title=Customization
        'fields' => array('EQUIPO' => 'name', 'ENTRENADOR' => 'f_cname', 'RAZA' => 'f_rname', 'Puntos' => 'pts', 'ValorEquipo' => 'tv'),
    ),array(
        'id'     => 13, # Node ID
        'box_ID' => 2,
        // Please note: 'type' may be either one of: 'league', 'division' or 'tournament'
        'type'   => 'tournament', # This sets the node to be a tournament. I.e. this will make a standings box for the tournament with ID = 1
        'infocus' => false, # If true a random team from the standings will be selected and its top players displayed.
        /*
            The house ranking system (HRS) NUMBER to sort the table against.
            Note, this is ignored for "type = tournament", since tours have an assigned HRS.
            Also note that using HRSs with fields such as points (pts) for leagues/divisions standings makes no sense as they are tournament specific fields (i.e. it makes no sense to sum the points for teams across different tours to get the teams' "league/division points", as the points definitions for tours may vary).
        */
        'HRS'    => 4, # Note: this must be a existing and valid HRS number from the main settings.php file.
        'title'  => 'Clasificacion de Liga 2016-17', # Table title
        'length' => 10, # Number of entries in table
        # Format: "Displayed table column name" => "OBBLM field name". For the OBBLM fields available see http://nicholasmr.dk/obblmwiki/index.php?title=Customization
        'fields' => array('EQUIPO' => 'name', 'ENTRENADOR' => 'f_cname', 'RAZA' => 'f_rname', 'PT' => 'pts', 'J' => 'played', 'G' => 'won', 'E' => 'draw', 'P' => 'lost', 'GF' => 'gf', 'GC' => 'ga', 'CAS' => 'cas', 'VE' => 'tv'),
    ),
);

/*********************
 *   Front page: leaders boxes
 *********************/

$settings['fp_leaders'] = array(
    # Please note: You can NOT make expressions out of leader fields e.g.: 'field' => 'cas+td'
    # This will display a 'most CAS' player leaders box for the node (league, division or tournament) with ID = 1
    array(
        'id'        => 6, # Node ID
        'box_ID'    => 3,
        // Please note: 'type' may be either one of: 'league', 'division' or 'tournament'
        'type'      => 'division', # This sets the node to be a tournament. I.e. this will make a leaders box for the tournament with ID = 1
        'title'     => 'Los mas violentos de la liga', # Table title
        'field'     => 'cas', # For the OBBLM fields available see http://nicholasmr.dk/obblmwiki/index.php?title=Customization
        'length'    => 5, # Number of entries in table
        'show_team' => true, # Show player's team name?
    ),
    # This will display a 'most TD' player leaders box for the node (league, division or tournament) with ID = 1
    array(
        'id'        => 6, # Node ID
        'box_ID'    => 4,
        // Please note: 'type' may be either one of: 'league', 'division' or 'tournament'
        'type'      => 'division', # This sets the node to be a tournament. I.e. this will make a leaders box for the tournament with ID = 1
        'title'     => 'Top 5 anotadores', # Table title
        'field'     => 'td', # For the OBBLM fields available see http://nicholasmr.dk/obblmwiki/index.php?title=Customization
        'length'    => 5, # Number of entries in table
        'show_team' => true, # Show player's team name?
    ), array(
        'id'        => 6, # Node ID
        'box_ID'    => 5,
        // Please note: 'type' may be either one of: 'league', 'division' or 'tournament'
        'type'      => 'division', # This sets the node to be a tournament. I.e. this will make a leaders box for the tournament with ID = 1
        'title'     => 'Top 5 lanzadores', # Table title
        'field'     => 'cp', # For the OBBLM fields available see http://nicholasmr.dk/obblmwiki/index.php?title=Customization
        'length'    => 5, # Number of entries in table
        'show_team' => true, # Show player's team name?
    ), array(
        'id'        => 6, # Node ID
        'box_ID'    => 6,
        // Please note: 'type' may be either one of: 'league', 'division' or 'tournament'
        'type'      => 'division', # This sets the node to be a tournament. I.e. this will make a leaders box for the tournament with ID = 1
        'title'     => 'Top 5 estrellas', # Table title
        'field'     => 'spp', # For the OBBLM fields available see http://nicholasmr.dk/obblmwiki/index.php?title=Customization
        'length'    => 5, # Number of entries in table
        'show_team' => true, # Show player's team name?
    )
);


/*********************
 *   Front page: event boxes
 *********************/

$settings['fp_events'] = array(
    /*
        Event boxes can show for any league, division or tournament the following:
            dead        - recent dead players
            sold        - recent sold players
            hired       - recent hired players
            skills      - recent player skill picks
    */
    array(
        'id'        => 1, # Node ID
        'box_ID'    => 7,
        // Please note: 'type' may be either one of: 'league', 'division' or 'tournament'
        'type'      => 'league', # This sets the node to be a tournament. I.e. this will make an event box for the tournament with ID = 1
        'title'     => 'Killed In Action', # Table title
        'content'   => 'dead', # Event type
        'length'    => 5, # Number of entries in table
    ),
array(
        'id'        => 1, # Node ID
        'box_ID'    => 8,
        // Please note: 'type' may be either one of: 'league', 'division' or 'tournament'
        'type'      => 'league', # This sets the node to be a tournament. I.e. this will make an event box for the tournament with ID = 1
        'title'     => 'Fichajes recientes', # Table title
        'content'   => 'hired', # Event type
        'length'    => 5, # Number of entries in table
    ),array(
        'id'        => 1, # Node ID
        'box_ID'    => 9,
        // Please note: 'type' may be either one of: 'league', 'division' or 'tournament'
        'type'      => 'league', # This sets the node to be a tournament. I.e. this will make an event box for the tournament with ID = 1
        'title'     => 'Subidas recientes', # Table title
        'content'   => 'skills', # Event type
        'length'    => 8, # Number of entries in table
    ));

/*********************
 *   Front page: latest games boxes
 *********************/

$settings['fp_latestgames'] = array(
    # This will display a latest games box for the node (league, division or tournament) with ID = 1
    array(
        'id'     => 1, # Node ID
        'box_ID' => 1,
        // Please note: 'type' may be either one of: 'league', 'division' or 'tournament'
        'type'   => 'league', # This sets the node to be a league. I.e. this will make a latest games box for the league with ID = 1
        'title'  => 'Ultimos partidos disputados', # Table title
        'length' => 10, # Number of entries in table
    ),
);

