var SelectedPlayerViewModel = function() {
    var self = this,
        onUpdatePlayerValue = function(playerId, valueType, value) {};
    
    self.selectedPlayerEntry = ko.observable({});
    
    var selectedPlayerId = ko.computed(function() { return self.selectedPlayerEntry().player_id; });
    
    function makePropertyObject(propKey, databasePrefix) {
        return {
            read: function() {
                return self.selectedPlayerEntry()[propKey];
            },
            write: function(value) {
                self.selectedPlayerEntry()[propKey] = value;
            }
        };
    }
    
    self.mvp = ko.computed(makePropertyObject("mvp", "mvp_"));
    self.completions = ko.computed(makePropertyObject("cp", "cp_"));
    self.touchdowns = ko.computed(makePropertyObject("td", "td_"));
    self.interceptions = ko.computed(makePropertyObject("intcpt", "intcpt_"));
    self.badlyHurt = ko.computed(makePropertyObject("bh", "bh_"));
    self.sustainedInjury = ko.computed(makePropertyObject("si", "si_"));
    self.killed = ko.computed(makePropertyObject("ki", "ki_"));
    self.injured = ko.computed(function() { return "fix me"; }); // db prefix: "inj_"
};