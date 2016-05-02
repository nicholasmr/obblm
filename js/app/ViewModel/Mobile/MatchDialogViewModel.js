var MatchDialogViewModel = function(playersOnSelectedTeam) {
    var self = this,
        match = new Match(playersOnSelectedTeam);
            
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
    
    self.openMatch = function(serverMatch) {
        self.serverMatch(serverMatch);
        match
            .load(serverMatch)
            .then(function() {
                $('#MatchDialog').dialog({modal: true});
            });
    };
    
    self.saveMatch = function() {
        match.saveMatch().done(function() {
            // reload page so updates are made
            window.location.search = '?mobile=1&lastMatchId=' + match.getMatchId();
        });
    };
    
    self.close = function() {
        $('#MatchDialog').dialog('close');
    };
};