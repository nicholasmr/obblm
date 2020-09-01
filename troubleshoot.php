<?php

require('header.php');
HTMLOUT::frame_begin(false);
title("OBBLM Troubleshooting");

?>
<h1>Leagues</h1>
<table>
    <thead>
        <th>League ID</th>
        <th>Tie Teams to Divisions</th>
        <th>Name</th>
        <th>Date Created</th>
        <th>Location</th>
        <th>Divisions</th>
    </thead>
    <tbody style="text-align: center">
        <?php
        foreach(League::getLeagues() as $league) {
            ?>
            <tr>
                <td><?php echo $league->lid; ?></td>
                <td><?php echo $league->tie_teams; ?></td>
                <td><?php echo $league->name; ?></td>
                <td><?php echo $league->date; ?></td>
                <td><?php echo $league->location; ?></td>
                <td><?php json_encode($league->getDivisions()); ?>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>

<h1>Tournaments</h1>
<table>
    <thead>
        <th>Tour ID</th>
        <th>Division ID</th>
        <th>Name</th>
        <th>Type</th>
        <th>Date Created</th>
        <th>RS</th>
        <th>Locked</th>
        <th>Allow Scheduling</th>
        <th>Winner</th>
        <th>Finished? ('finished')</th>
        <th>Empty? ('empty')</th>
        <th>Begun? ('is_begin')</th>
        <th>Empty? ('empty')</th>
        <th>Begun? ('begun')</th>
        <th>Finished? ('finished')</th>
    </thead>
    <tbody style="text-align: center">
        <?php
        foreach(Tour::getTours() as $tour) {
            ?>
            <tr>
                <td><?php echo $tour->tour_id; ?></td>
                <td><?php echo $tour->f_did; ?></td>
                <td><?php echo $tour->name; ?></td>
                <td><?php echo $tour->type; ?></td>
                <td><?php echo $tour->date_created; ?></td>
                <td><?php echo $tour->rs; ?></td>
                <td><?php echo $tour->locked; ?></td>
                <td><?php echo $tour->allow_sched; ?></td>
                <td><?php echo $tour->winner; ?></td>
                <td><?php echo $tour->finished; ?></td>
                <td><?php echo $tour->empty; ?></td>
                <td><?php echo $tour->begun; ?></td>
                <td><?php echo $tour->empty; ?></td>
                <td><?php echo $tour->begun; ?></td>
                <td><?php echo $tour->finished; ?></td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>

<?php
HTMLOUT::frame_end();