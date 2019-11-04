<?php
  class BracketManager {

      public function generateMatches(array $teams, $numOfSafeRounds) {
       $matches = array();
       if ($numOfSafeRounds == 0) {
         shuffle($teams);
         $closestExponentOf2 = pow(2,floor(log(count($teams), 2)));
         $firstRoundMatches = count($teams) - $closestExponentOf2;
         $freeWins = $closestExponentOf2 - $firstRoundMatches;
         $teamsToPlay = array_slice($teams, 0,(2*$firstRoundMatches));
         $teamsToWait = array_slice($teams,(2*$firstRoundMatches));
        for($i = 0; $i < count($teamsToPlay); $i+=2) {
             $matches[] = array('match_first_team' => $teamsToPlay[$i]['name'], 'match_second_team' => $teamsToPlay[$i+1]['name'], 'match_round' => 'B1', 'match_first_team_score'=>'0', 'match_second_team_score'=>'0');
          }
          foreach($teamsToWait as $teamName) {
             $matches[] = array('match_first_team'=>$teamName['name'], 'match_second_team' => 'freewin', 'match_round' => 'B1','match_first_team_score'=>'1', 'match_second_team_score'=>'0');
           }
        $curNumTeams = $closestExponentOf2/2;
        for($i = 0; $i < log($closestExponentOf2, 2); $i++) {
        for ($j = 0; $j < $curNumTeams; $j++) {
        $matches[] = array('match_first_team'=>'TBD', 'match_second_team' => 'TBD', 'match_round' => 'B' . ($i+2), 'match_first_team_score'=>'0', 'match_second_team_score'=>'0');
        }
        $curNumTeams /= 2;
        }
        return $matches;
       }
      }

      public function insertMatches(array $matches, $eventId, array $teamsInEvent) {
        try {
          $teamsInEventParsed = array();
          foreach ($teamsInEvent as $team) {
            $teamsInEventParsed[] = $team['name'];
          }
          $seedIncrement = 1;
          foreach ($matches as $match) {
            if (in_array($match['match_first_team'],$teamsInEventParsed) && in_array($match['match_second_team'],$teamsInEventParsed)) {
              Db::insert('matches', array('match_first_team' => $match['match_first_team'],'match_second_team' => $match['match_second_team'],'match_round' => $match['match_round'],
              'event_id' => $eventId, 'match_first_team_score'=>$match['match_first_team_score'],'match_second_team_score'=>$match['match_second_team_score'],
              'match_first_team_seed'=>($seedIncrement), 'match_second_team_seed'=>($seedIncrement+1)));
              $seedIncrement+=2;
            } else if (in_array($match['match_first_team'],$teamsInEventParsed) && !in_array($match['match_second_team'],$teamsInEventParsed)) {
              Db::insert('matches', array('match_first_team' => $match['match_first_team'],'match_second_team' => $match['match_second_team'],'match_round' => $match['match_round'],
              'event_id' => $eventId, 'match_first_team_score'=>$match['match_first_team_score'],'match_second_team_score'=>$match['match_second_team_score'],
              'match_first_team_seed'=>($seedIncrement)));
              $seedIncrement+=1;
            } else if (!in_array($match['match_first_team'],$teamsInEventParsed) && in_array($match['match_second_team'],$teamsInEventParsed)) {
              Db::insert('matches', array('match_first_team' => $match['match_first_team'],'match_second_team' => $match['match_second_team'],'match_round' => $match['match_round'],
              'event_id' => $eventId, 'match_first_team_score'=>$match['match_first_team_score'],'match_second_team_score'=>$match['match_second_team_score'],
              'match_second_team_seed'=>($seedIncrement)));
              $seedIncrement+=1;
            } else {
              Db::insert('matches', array('match_first_team' => $match['match_first_team'],'match_second_team' => $match['match_second_team'],'match_round' => $match['match_round'],
              'event_id' => $eventId, 'match_first_team_score'=>$match['match_first_team_score'],'match_second_team_score'=>$match['match_second_team_score']));
            }
          }
        } catch (PDOException $e) {
          throw New UserError($e);
        }
      }

      public function returnParsedMatchesInEvent($eventId) {
        $parsedMatches = array();
        $matches = Db::multiQuery("SELECT match_id, match_first_team, match_second_team, match_round, match_first_team_score, match_second_team_score,match_first_team_seed, match_second_team_seed, event_id from matches
        where event_id = ? order by match_id asc", array($eventId));
        $numOfRounds = Db::query("SELECT match_round from matches where event_id = ? group by match_round", array($eventId));
        for ($i=0; $i < $numOfRounds; $i++) {
          $roundMatches = Db::multiQuery("SELECT match_id, match_first_team, match_second_team, match_round, match_first_team_score, match_second_team_score,match_first_team_seed, match_second_team_seed, event_id from matches
          where event_id = ? and match_round = ? order by match_id asc", array($eventId,"B".($i+1)));
          foreach ($roundMatches as $roundMatch) {
            $parsedMatches[$i][] = $roundMatch;
          }
        }
        return $parsedMatches;
      }


  }
?>
