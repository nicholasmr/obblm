var PageViewModel = function() {
    var self = this;
        
    self.updatePlayerName = function(newName, playerId) {
        return $.ajax({
            type: 'POST',
            url: 'team_webservice.php',
            data: {
                'type': 'rename_player',
                'player': playerId,
                'name': newName
            }
        });
    };
}