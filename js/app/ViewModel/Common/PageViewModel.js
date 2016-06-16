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
    
    self.updatePlayerNumber = function(newNumber, playerId) {
        newNumber = parseInt(newNumber, 10);
        if(isNaN(newNumber))
            return;
        
        return $.ajax({
            type: 'POST',
            url: 'team_webservice.php',
            data: {
                'type': 'renumber_player',
                'player': playerId,
                'number': newNumber
            }
        });
    };
}