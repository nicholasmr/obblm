var SelectedPlayerViewModel = function() {
    var self = this,
        onUpdatePlayerValue = function(playerId, valueType, value) {};
    
    self.selectedPlayerEntry = ko.observable({});
    self.injuryTable = ko.observable({});
    
    var selectedPlayerId = ko.computed(function() { return self.selectedPlayerEntry().player_id; });
    
    function makePropertyObject(propKey) {
        return {
            read: function() {
                return self.selectedPlayerEntry()[propKey];
            },
            write: function(value) {
                self.selectedPlayerEntry()[propKey] = value;
            }
        };
    }
    
    self.mvp = ko.computed(makePropertyObject("mvp"));
    self.completions = ko.computed(makePropertyObject("cp"));
    self.touchdowns = ko.computed(makePropertyObject("td"));
    self.interceptions = ko.computed(makePropertyObject("intcpt"));
    self.badlyHurt = ko.computed(makePropertyObject("bh"));
    self.sustainedInjury = ko.computed(makePropertyObject("si"));
    self.killed = ko.computed(makePropertyObject("ki"));
    self.injured = ko.computed(makePropertyObject("inj"));
    
    self.injuryText = ko.computed(function() {
        var injuryTable = self.injuryTable(),
            injuryId = self.injured();
            
        if(!injuryTable[injuryId])
            return injuryTable["1"];
            
        return injuryTable[injuryId];
    });
    
    self.injuries = ko.computed(function() {
        var i = _.map(self.injuryTable(), function(injuryName, injuryId) {
            return {id: injuryId, name: injuryName};
        });
        return i;
    });
};