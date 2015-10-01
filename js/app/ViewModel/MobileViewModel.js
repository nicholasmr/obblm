var MobileViewModel = function() {
    var self = this;
    
    self.openPlayerDialog = function(playerViewModel, event) {
        var playerId = playerViewModel.id;
        var player = _.find(playersOnSelectedTeam, function(player) {
            return player.player_id === playerId;
        });
        
        self.playerDialogViewModel.player(player);
        $('#PlayerDialog').dialog({modal: true});
    };
    
    self.openMatchDialog = function() {
        var matchId = $(event.target).attr('data-match-id');
        var match = _.find(matches, function(match) {
            return match.match_id === matchId;
        });
        
        // These values are loaded from the database as "10000", but saved as "10" so we convert before displaying.
        // This condition is a bit of a hack -- incomes are only higher than 100 if they are "10,000" format, so we haven't converted anything yet.
        if(match.income1 > 100) {
            match.income1 /= 1000;        
            match.income2 /= 1000;
            match.gate /= 1000;
            match.tv1 /= 1000;
            match.tv2 /= 1000;
        }
        
        self.matchDialogViewModel.match(match);
        // opens after AJAX call in MatchDialogViewModel
    }
    
    self.teamViewModel = new TeamViewModel();
    self.playerDialogViewModel = new PlayerDialogViewModel();
    self.matchDialogViewModel = new MatchDialogViewModel();
};