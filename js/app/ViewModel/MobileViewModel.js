var MobileViewModel = function() {
    var self = this;
    
    self.openPlayerDialog = function(viewModel, event) {
        var playerId = $(event.target).attr('data-player-id');
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
        
        self.matchDialogViewModel.match(match);
        $('#MatchDialog').dialog({modal: true});
    }
    
    self.playerDialogViewModel = new PlayerDialogViewModel();
    self.matchDialogViewModel = new MatchDialogViewModel();
};