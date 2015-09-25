<?php
class Mobile_HTMLOUT {
    public static function sec_mobile_main() {
        global $coach;
        
        $teams = $coach->getTeams();
        $selectedTeamId = isset($_POST["SelectedTeam"]) ? $_POST["SelectedTeam"] : $teams[0]->team_id;

        foreach($teams as $team) {
            if($team->team_id == $selectedTeamId)
                $selectedTeam = $team;
        }
        
        $playersOnSelectedTeam = $selectedTeam->getPlayers();
        
        list($recentMatches, $pages) = Stats::getMatches(T_OBJ_TEAM, $selectedTeamId, false, false, false, false, array(), true, false);
        list($upcomingMatches, $pages) = Stats::getMatches(T_OBJ_TEAM, $selectedTeamId, false, false, false, false, array(), true, true);
        $allMatches = array_merge($recentMatches, $upcomingMatches);
        ?>
        <script type="text/javascript">
            var playersOnSelectedTeam = <?php echo json_encode($playersOnSelectedTeam); ?>;
            var matches = <?php echo json_encode($allMatches); ?>;
        
            $(document).ready(function() {
                $('#tabs').tabs();
                $('#SelectedTeam').change(function() {
                    this.form.submit();
                });

                var mobileViewModel = new MobileViewModel();
                mobileViewModel.matchDialogViewModel.myTeamId(<?php echo $selectedTeamId; ?>);
                mobileViewModel.matchDialogViewModel.playersOnTeam(playersOnSelectedTeam);
                
                ko.applyBindings(mobileViewModel);
            });
        </script>
        <div class="main">
            <form method="post" action="<?php echo getFormAction(); ?>">
                 <select id="SelectedTeam" name="SelectedTeam">
                    <?php
                        foreach($teams as $team) {
                            $isThisTeam = ($team->team_id == $selectedTeamId);
                            echo '<option value="' . $team->team_id . '"' . ($isThisTeam ? ' selected="selected"' : '') . '>' . $team->name . '</option>';
                        }
                    ?>
                </select>
                <span class="button-panel">
                    <a href="<?php echo getFormAction() . '?logout=1'; ?>">Logout</a>
                </span>
                <div id="tabs">
                    <ul>
                        <li><a href="#Teams">Teams</a></li>
                        <li><a href="#Games">Games</a></li>
                    </ul>
                    <div id="Teams">
                        <table id="Players">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Position</th>
                                    <th>Stats</th>
                                    <th>Skills</th>
                                    <th>SPP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($playersOnSelectedTeam as $player) { ?>
                                    <tr>
                                        <td><?php echo '<a href="#" data-bind="click: openPlayerDialog" data-player-id="' . $player->player_id . '">' . $player->name .'</a>'; ?></td>
                                        <td><?php echo $player->position; ?></td>
                                        <td><?php echo $player->ma . '/' . $player->st . '/' . $player->ag . '/' . $player->av; ?></td>
                                        <td><?php echo $player->current_skills; ?></td>
                                        <td><?php echo $player->mv_spp; ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <div id="PlayerDialog" data-bind="with: playerDialogViewModel">
                            <table>
                                <tbody>
                                    <tr><td>Player name:</td><td class="data" data-bind="text: name"></td></tr>
                                    <tr><td>Position:</td><td class="data" data-bind="text: position"></td></tr>
                                    <tr><td>MA/ST/AG/AV:</td><td class="data" data-bind="text: statString"></td></tr>
                                    <tr><td>Skills:</td><td class="data" data-bind="html: skillsString"></td></tr>
                                    <tr><td>SPP:</td><td class="data" data-bind="text: spp"></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="Games">
                        <div>Recent:</div>
                        <table>
                            <tbody>
                            <?php
                                foreach($allMatches as $match) {
                                    $dateCreated = date('Y-m-d', strtotime($match->date_created));
                                    
                                    echo '<tr>';
                                    echo '<td class="date"><a data-bind="click: openMatchDialog" href="#" data-match-id="' . $match->match_id . '">' . $dateCreated . '</td>';
                                    echo '<td class="team-name">' . $match->team1_name . '</td>';
                                    echo '<td>v.</td>';
                                    echo '<td class="team-name">' . $match->team2_name . '</td>';
                                    echo '</tr>';
                                }
                            ?>
                            </tbody>
                        </table>
                        
                        <div id="MatchDialog" data-bind="with: matchDialogViewModel">
                            <div>
                                <span class="label">Team:</span>
                                <span class="data" data-bind="text: name"></span>
                            </div>
                            <div>
                                <span class="label">Score:</span>
                                <span class="data" data-bind="text: score"></span>
                            </div>
                            <div>
                                <span class="label">Treasury:</span>
                                <span class="data" data-bind="text: treasuryChange"></span>
                            </div>
                            <div>
                                <span class="label">Fan Factor:</span>
                                <span class="data" data-bind="text: fanFactorChange"></span>
                            </div>
                            <div id="SelectedPlayer">
                                <span>Player:</span>
                                <select data-bind="value: selectedPlayer, options: playersOnTeam, optionsText: 'name'"></select>
                                <div data-bind="with: selectedPlayerViewModel">
                                    <div>
                                        <span class="label">MVP:</span>
                                        <span class="data" data-bind="text: mvp"></span>
                                    </div>
                                    <div>
                                        <span class="label">Completions:</span>
                                        <span class="data" data-bind="text: completions"></span>
                                    </div>
                                    <div>
                                        <span class="label">Touchdowns:</span>
                                        <span class="data" data-bind="text: touchdowns"></span>
                                    </div>
                                    <div>
                                        <span class="label">Interceptions:</span>
                                        <span class="data" data-bind="text: interceptions"></span>
                                    </div>
                                    <div>
                                        <span class="label">Badly hurt:</span>
                                        <span class="data" data-bind="text: badlyHurt"></span>
                                    </div>
                                    <div>
                                        <span class="label">Sustained injury:</span>
                                        <span class="data" data-bind="text: sustainedInjury"></span>
                                    </div>
                                    <div>
                                        <span class="label">Killed:</span>
                                        <span class="data" data-bind="text: killed"></span>
                                    </div>
                                    <div>
                                        <span class="label">Injured:</span>
                                        <span class="data" data-bind="text: injured"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php   
    }
}