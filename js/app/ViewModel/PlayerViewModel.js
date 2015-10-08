var PlayerViewModel = function(player) {
    var self = this;

    self.number = player.nr;
    self.id = player.player_id;
    self.name = player.name;
    self.position = player.position;
    self.ma = player.ma;
    self.st = player.st;
    self.ag = player.ag;
    self.av = player.av;
    self.skills = player.current_skills;
    self.spp = player.mv_spp;
    self.missNextGame = player.is_mng;
    self.nigglingInjuryCount = player.inj_ni;
    self.mayBuyNewSkill = player.may_buy_new_skill > 0;
    
    self.statsString = player.ma + '/' + player.st + '/' + player.ag + '/' + player.av;
};