var PlayerDialogViewModel = function() {
    var self = this;
    
    self.player = ko.observable({});
    
    self.name = ko.computed(function() { return self.player().name; });
    self.position = ko.computed(function() { return self.player().position; });
    
    self.statString = ko.computed(function() { 
        var player = self.player();
        return player.ma + '/' + player.st + '/' + player.ag + '/' + player.av; 
    });
    
    self.skillsString = ko.computed(function() { return self.player().current_skills; });
    self.spp = ko.computed(function() { return self.player().mv_spp; });
};