var MatchDialogViewModel = function(playersOnSelectedTeam) {
    var self = this,
        playerEntries = ko.observable({});
    
    self.match = ko.observable({});
    self.myTeamId = ko.observable(-1);
    self.selectedPlayer = ko.observable({});
        
    var matchTeamOneId = ko.computed(function() {
        return parseInt(self.match().team1_id, 10);
    });
    
    function isTeamOne() {
        return matchTeamOneId() === self.myTeamId();
    }
    
    function makePropertyObject(team1PropKey, team2PropKey) {
        return {
            read: function() {
                return isTeamOne() ? self.match()[team1PropKey] : self.match()[team2PropKey];
            },
            write: function(value) {
                self.match()[(isTeamOne() ? team1PropKey : team2PropKey)] = value;
                self.match.valueHasMutated();
            }
        };
    }
    
    self.name = ko.computed(makePropertyObject('team1_name', 'team2_name'));
    self.myScore = ko.computed(makePropertyObject('team1_score', 'team2_score'));
    self.theirScore = ko.computed(makePropertyObject('team2_score', 'team1_score'));
    self.treasuryChange = ko.computed(makePropertyObject('income1', 'income2'));
    self.fanFactorChange = ko.computed(makePropertyObject('ffactor1', 'ffactor2'));
        
    self.matchIsLocked = ko.computed(function() {
        return self.match().locked;
    });
    
    self.playersInMatch = ko.computed(function() {
        var playerMatchEntries = playerEntries();
        return _.filter(playersOnSelectedTeam, function(player) {
            var inMatch = _.find(playerMatchEntries, function(playerEntry, playerId) {
                return playerId === player.player_id;
            });
            player.numberAndName = '#' + player.nr + ' ' + player.name;
            return inMatch ? player : null;
        });
    });

    self.match.subscribe(function(newMatch) {
        self.selectedPlayer(_.first(playersOnSelectedTeam));
        
        $.ajax({
            type: 'GET',
            url: 'match_webservice.php',
            data: {
                'action': 'getplayerentries',
                'match_id': self.match().match_id,
                'team_id': self.myTeamId()
            },
            success: function(result) {
                playerEntries(JSON.parse(result));
                self.selectedPlayer.valueHasMutated();
                $('#MatchDialog').dialog({modal: true});
            }
        });
    });
    
    self.selectedPlayerViewModel = new SelectedPlayerViewModel();

    self.selectedPlayer.subscribe(function(newSelectedPlayer) {
        var entry = _.find(playerEntries(), function(entry, playerId) {
            return playerId === newSelectedPlayer.player_id;
        });
        
        // Happens on first load -- if it happens after that, there's a problem!
        if(entry === undefined)
            return;
        
        self.selectedPlayerViewModel.selectedPlayerEntry(entry);
    });

    self.saveMatch = function() {
        var match = self.match();
        var data = { 
            match_id : match.match_id,
            action: 'update'            
        };
        
        // Save these values without changing them
        data['stadium'] = match.stadium;
        data['gate'] = match.gate;
        data['fans'] = match.fans;
        data['smp1'] = match.smp1;
        data['smp2'] = match.smp2;
        data['tcas1'] = match.tcas1;
        data['tcas2'] = match.tcas2;
        data['fame1'] = match.fame1;
        data['fame2'] = match.fame2;
        data['tv1'] = match.tv1;
        data['tv2'] = match.tv2;        
        
        // Put back the values we already had.
        data[isTeamOne() ? 'ff2' : 'ff1'] = match[isTeamOne() ? 'ffactor2' : 'ffactor1'];
        data[isTeamOne() ? 'inc2' : 'inc1'] = match[isTeamOne() ? 'income2' : 'income1'];
        
        data[isTeamOne() ? 'ff1' : 'ff2'] = self.fanFactorChange();
        data[isTeamOne() ? 'inc1' : 'inc2'] = self.treasuryChange();
        data[isTeamOne() ? 'result1' : 'result2'] = self.myScore();
        data[isTeamOne() ? 'result2' : 'result1'] = self.theirScore();
     
        data['team_id'] = self.myTeamId();
        
        _.each(playerEntries(), function(playerEntry, playerId) {
            _.each(playerEntry, function(value, key) {
                data[key + '_' + playerId] = value;
            });
        });
        
        $.ajax({
            type: "POST",
            url: 'match_webservice.php',
            data: data,
            success: function() {
                // reload page so updates are made
                window.location.href = window.location.href;
            }
        });
    };
    
    self.close = function() {
        $('#MatchDialog').dialog('close');
    };
};