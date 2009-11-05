<?php

/*
 *  Copyright (c) Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2009. All Rights Reserved.
 *
 *
 *  This file is part of OBBLM.
 *
 *  OBBLM is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  OBBLM is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

class Team_export extends Team implements ModuleInterface
{

    public static function main($argv) {
        $t = new Team_export(array_shift($argv));
        echo $t->xmlExport();
    }
    
    public static function getModuleAttributes()
    {
        return array(
            'author'     => 'Nicholas Mossor Rathmann',
            'moduleName' => 'Team XML export',
            'date'       => '2009',
            'setCanvas'  => false,
        );
    }

    public static function getModuleTables()
    {
        return array();
    }
    
    public static function getModuleUpgradeSQL()
    {
        return array();
    }
    
    public static function triggerHandler($type, $argv){}

    public function xmlExport()
    {
        /* 
            Exports a team by the using the same fields as the import XML schema uses.
        */
        
        $ELORanks = ELO::getRanks(false);
        $this->elo = $ELORanks[$this->team_id] + $this->elo_0;
        
        $dom = new DOMDocument();
        $dom->formatOutput = true;

        $el_root = $dom->appendChild($dom->createElement('xmlimport'));
        
        $el_root->appendChild($dom->createElement('coach', htmlspecialchars($this->coach_name, ENT_NOQUOTES, "UTF-8")));
        $el_root->appendChild($dom->createElement('name', htmlspecialchars($this->name, ENT_NOQUOTES, "UTF-8")));
        $el_root->appendChild($dom->createElement('race', $this->f_rname));
        $el_root->appendChild($dom->createElement('treasury', $this->treasury));
        $el_root->appendChild($dom->createElement('apothecary', $this->apothecary));
        $el_root->appendChild($dom->createElement('rerolls', $this->rerolls));
        $el_root->appendChild($dom->createElement('fan_factor', $this->fan_factor));
        $el_root->appendChild($dom->createElement('ass_coaches', $this->ass_coaches));
        $el_root->appendChild($dom->createElement('cheerleaders', $this->cheerleaders));
        
        $el_root->appendChild($dom->createElement('won_0', $this->mv_won));
        $el_root->appendChild($dom->createElement('lost_0', $this->mv_lost));
        $el_root->appendChild($dom->createElement('draw_0', $this->mv_draw));
        $el_root->appendChild($dom->createElement('sw_0', $this->rg_swon));
        $el_root->appendChild($dom->createElement('sl_0', $this->rg_slost));
        $el_root->appendChild($dom->createElement('sd_0', $this->rg_sdraw));
        $el_root->appendChild($dom->createElement('wt_0', $this->wt_cnt));
        $el_root->appendChild($dom->createElement('gf_0', $this->mv_gf));
        $el_root->appendChild($dom->createElement('ga_0', $this->mv_ga));
        $el_root->appendChild($dom->createElement('tcas_0', $this->mv_tcas));
        $el_root->appendChild($dom->createElement('elo_0', $this->rg_elo));

        foreach ($this->getPlayers() as $p) {
            $status = Player::theDoctor($p->getStatus(-1));
            if ($status == 'none') {$status = 'ready';}
            if ($p->is_sold) {$status = 'sold';}

            $ply = $el_root->appendChild($dom->createElement('player'));
            $ply->appendChild($dom->createElement('name', htmlspecialchars($p->name, ENT_NOQUOTES, "UTF-8")));
            $ply->appendChild($dom->createElement('position', $p->pos));
            $ply->appendChild($dom->createElement('status', $status));
            $ply->appendChild($dom->createElement('stats', "$p->mv_cp/$p->mv_td/$p->mv_intcpt/$p->mv_bh/$p->mv_si/$p->mv_ki/$p->mv_mvp"));
            $ply->appendChild($dom->createElement('injs', "$p->inj_ma/$p->inj_st/$p->inj_ag/$p->inj_av/$p->inj_ni"));
        }
        
        return $dom->saveXML();
    }
}

?>
