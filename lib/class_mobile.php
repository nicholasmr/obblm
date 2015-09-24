<?php

/*
 *   Released under GPL.
 */

class Mobile {
    function isMobile($isMobile) {
		if(isset($isMobile))
			$_SESSION['isMobile'] = $isMobile;
		
		return $_SESSION['isMobile'];
    }
}

