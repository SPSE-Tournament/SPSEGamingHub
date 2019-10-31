<?php
  class BracketManager {

      public function generateMatches(array $eventTeams) {
            $matches = array();
            shuffle($eventTeams);
          	$closestExponentOf2 = pow(2,floor(log(count($teams), 2)));
          	$firstRoundMatches = count($teams) - $closestExponentOf2;
          	$freeWins = $closestExponentOf2 - $firstRoundMatches;
          	$teamsToPlay = array_slice($teams, 0,(2*$firstRoundMatches));
          	$teamsToWait = array_slice($teams,(2*$firstRoundMatches));
          	 for($i = 0; $i < count($teamsToPlay); $i+=2) {
          	    $matches[] = array('firstTeam' => $teamsToPlay[$i]['name'], 'secondTeam' => $teamsToPlay[$i+1]['name']);
          	 }
          	 foreach($teamsToWait as $teamName) {
          		  $matches[] = array('firstTeam'=>$teamName['name'], 'secondTeam' => 'freewin');
          	  }
        return $matches;
      }

  }
?>
