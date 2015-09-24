var SelectedPlayerViewModel = function() {
    var self = this;
    
    self.selectedPlayer = ko.observable({});
    
    self.mvp = ko.computed(function() { return self.selectedPlayer().mv_mvp; });
    self.completions = ko.computed(function() { return self.selectedPlayer().mv_cp; });
    self.touchdowns = ko.computed(function() { return self.selectedPlayer().mv_td; });
    self.interceptions = ko.computed(function() { return self.selectedPlayer().mv_intcpt; });
    self.badlyHurt = ko.computed(function() { return self.selectedPlayer().mv_bh; });
    self.sustainedInjury = ko.computed(function() { return self.selectedPlayer().mv_si; });
    self.killed = ko.computed(function() { return self.selectedPlayer().mv_ki; });
    self.injured = ko.computed(function() { return "fix me"; });
};