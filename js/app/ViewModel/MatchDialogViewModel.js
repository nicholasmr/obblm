var MatchDialogViewModel = function(playersOnSelectedTeam) {
    var self = this,
        match = new Match(playersOnSelectedTeam);
            
    match.onMatchLoaded(function() {
        $('#MatchDialog').dialog({modal: true});
    });
    
    // Just copy straight from Match
    self.serverMatch = match.serverMatch;
    self.myTeamId = match.myTeamId;
    self.matchIsLocked = match.matchIsLocked;
    self.name = match.name;
    self.myScore = match.myScore;
    self.theirScore = match.theirScore;
    self.treasuryChange = match.treasuryChange;
    self.fanFactorChange = match.fanFactorChange;
    self.selectedPlayer = match.selectedPlayer;
    self.playersInMatch = match.playersInMatch;
    self.selectedPlayerViewModel = match.selectedPlayerViewModel;
    
    self.saveMatch = function() {
        match.saveMatch().done(function() {
            // reload page so updates are made
            window.location.reload(true);
        });
    };
    
    self.close = function() {
        $('#MatchDialog').dialog('close');
    };
};