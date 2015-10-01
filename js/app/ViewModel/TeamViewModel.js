var TeamViewModel = function() {
    var self = this;
    
    self.playersOnTeam = ko.observable({});
    
    self.players = ko.computed(function() {
        return _.map(self.playersOnTeam(), function(player) {
            return new PlayerViewModel(player);
        });
    });
};