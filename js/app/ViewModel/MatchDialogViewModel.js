var MatchDialogViewModel = function() {
    var self = this;
    
    self.match = ko.observable({});
    self.playersOnTeam = ko.observable({});
    self.myTeamId = ko.observable(-1);
    self.selectedPlayer = ko.observable({});
    
    var matchTeamOneId = ko.computed(function() {
        return parseInt(self.match().team1_id, 10);
    });
    
    self.name = ko.computed(function() { 
        return matchTeamOneId() === self.myTeamId() ? self.match().team1_name : self.match().team2_name;
    });
    
    self.score = ko.computed(function() { 
        return matchTeamOneId() === self.myTeamId() ? self.match().team1_score : self.match().team2_score;
    });
    
    self.treasuryChange = ko.computed(function() { 
        return matchTeamOneId() === self.myTeamId() ? self.match().income1 : self.match().income2;
    });
    
    self.fanFactorChange = ko.computed(function() { 
        return matchTeamOneId() === self.myTeamId() ? self.match().ffactor1 : self.match().ffactor2;
    });
    
    self.selectedPlayerViewModel = new SelectedPlayerViewModel();
    self.selectedPlayerViewModel.selectedPlayer(self.selectedPlayer());
    
    self.selectedPlayer.subscribe(function(newPlayer) {
        self.selectedPlayerViewModel.selectedPlayer(newPlayer);
    });
};