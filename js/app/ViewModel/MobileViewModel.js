var MobileViewModel = function(playersOnSelectedTeam, matches) {
    var self = this;
                        
    // Matches come from the database as "30000", but we deal with them as "30". Convert before we do anything else.
    _.each(matches, function(match) {
        match.income1 /= 1000;        
        match.income2 /= 1000;
        match.gate /= 1000;
        match.tv1 /= 1000;
        match.tv2 /= 1000;
    });
    
    self.openPlayerDialog = function(playerViewModel, event) {
        var playerId = playerViewModel.id;
        var player = _.find(playersOnSelectedTeam, function(player) {
            return player.player_id === playerId;
        });
        
        self.playerDialogViewModel.player(player);
        $('#PlayerDialog').dialog({modal: true});
    };
    
    self.openMatchDialog = function(playerViewModel, event) {
        var matchId = $(event.target).attr('data-match-id');
        var match = _.find(matches, function(match) {
            return match.match_id === matchId;
        });

        self.matchDialogViewModel.match(match);
        // opens after AJAX call in MatchDialogViewModel
    }
	
	self.isMenuVisible = ko.observable(false);
	
	self.showMenu = function(viewModel, event) {
		self.isMenuVisible(true);
	}
    
    $('html').click(function(event) {
        if(!$(event.target).is('#menu') && !$(event.target).is('#open-menu')) {
            self.isMenuVisible(false);
        }
    });
    
    self.teamViewModel = new TeamViewModel(playersOnSelectedTeam);
    self.playerDialogViewModel = new PlayerDialogViewModel();
    self.matchDialogViewModel = new MatchDialogViewModel(playersOnSelectedTeam);
};