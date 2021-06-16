<?php
class BracketManager
{
  public function generateMatches(array $teams, $numOfSafeRounds)
  {
    $matches = array();
    if ($numOfSafeRounds == 0) {
      shuffle($teams);
      $closestExponentOf2 = pow(2, floor(log(count($teams), 2)));
      $firstRoundMatches = count($teams) - $closestExponentOf2;
      $freeWins = $closestExponentOf2 - $firstRoundMatches;
      $teamsToPlay = array_slice($teams, 0, (2 * $firstRoundMatches));
      $teamsToWait = array_slice($teams, (2 * $firstRoundMatches));
      $curNumTeams = $closestExponentOf2 / 2;
      $bracketSeed = 0;

      for ($i = 0; $i < count($teamsToPlay); $i += 2) {
        $matches[] = array(
          'match_first_team' => $teamsToPlay[$i]['id'],
          'match_second_team' => $teamsToPlay[$i + 1]['id'],
          'match_round' => 'B1', 'match_first_team_score' => '0',
          'match_second_team_score' => '0',
          'match_status' => 'live'
        );
      }

      foreach ($teamsToWait as $teamId) {
        $matches[] = array(
          'match_first_team' => $teamId['id'],
          'match_second_team' => 'freewin',
          'match_round' => 'B1', 'match_first_team_score' => '1',
          'match_second_team_score' => '0',
          'match_status' => 'finished'
        );
      }

      for ($i = 0; $i < count($matches); $i++) {
        $matches[$i]['match_bracket_seed'] = ($i + 1);
        $bracketSeed++;
      }

      for ($i = 0; $i < log($closestExponentOf2, 2); $i++) {
        for ($j = 0; $j < $curNumTeams; $j++) {
          if ($curNumTeams == (1 || 2 || 4)) {
            if ($curNumTeams == 4) {
              $matches[] = array(
                'match_first_team' => 'TBD',
                'match_second_team' => 'TBD',
                'match_round' => 'B' . ($i + 2),
                'match_first_team_score' => '0',
                'match_second_team_score' => '0',
                'match_bracket_seed' => ($bracketSeed + 1),
                'match_status' => 'scheduled',
                'match_description' => 'Quarterfinal'
              );
            }
            if ($curNumTeams == 2) {
              $matches[] = array(
                'match_first_team' => 'TBD',
                'match_second_team' => 'TBD',
                'match_round' => 'B' . ($i + 2),
                'match_first_team_score' => '0',
                'match_second_team_score' => '0',
                'match_bracket_seed' => ($bracketSeed + 1),
                'match_status' => 'scheduled',
                'match_description' => 'Semifinal'
              );
            }
            if ($curNumTeams == 1) {
              $matches[] = array(
                'match_first_team' => 'TBD',
                'match_second_team' => 'TBD',
                'match_round' => 'B' . ($i + 2),
                'match_first_team_score' => '0',
                'match_second_team_score' => '0',
                'match_bracket_seed' => ($bracketSeed + 1),
                'match_status' => 'scheduled',
                'match_description' => 'Final'
              );
            }
          } else {
            $matches[] = array(
              'match_first_team' => 'TBD',
              'match_second_team' => 'TBD',
              'match_round' => 'B' . ($i + 2),
              'match_first_team_score' => '0',
              'match_second_team_score' => '0',
              'match_bracket_seed' => ($bracketSeed + 1),
              'match_status' => 'scheduled'
            );
          }

          $bracketSeed++;
        }
        $curNumTeams /= 2;
      }
      return $matches;
    }
  }

  public function checkMatches($eventId)
  {
    $matches = Db::multiQuery("SELECT match_id, match_first_team, match_second_team, match_round, match_first_team_score, match_second_team_score,match_first_team_seed, match_second_team_seed, event_id, match_bracket_seed, match_description, match_status from matches
        where event_id = ? order by match_id asc", array($eventId));
    foreach ($matches as $match) {
      if ($match['match_first_team'] == 'TBD' || $match['match_second_team'] == 'TBD') {
        if ($match['match_first_team'] == 'TBD') {
          $firstSeed = Db::singleQuery("SELECT match_id, match_status, match_bracket_seed,
                  match_first_team, match_second_team, match_round, match_first_team_score, match_second_team_score,match_first_team_seed, match_second_team_seed, event_id from matches
                  where event_id = ? and match_bracket_seed = ?", array($eventId, $match['match_first_team_seed']));
          if ($firstSeed['match_status'] == 'finished') {
            $winner = ($firstSeed['match_first_team_score'] > $firstSeed['match_second_team_score']) ? $firstSeed['match_first_team'] : $firstSeed['match_second_team'];
            try {
              Db::edit('matches', array('match_first_team' => $winner), 'where event_id = ? and match_bracket_seed = ?', array($eventId, $match['match_bracket_seed']));
            } catch (PDOException $e) {
              throw new UserError($e);
            }
          }
        }
        if ($match['match_second_team'] == 'TBD') {
          $secondSeed = Db::singleQuery("SELECT match_id, match_status, match_bracket_seed,
                match_first_team, match_second_team, match_round, match_first_team_score, match_second_team_score,match_first_team_seed, match_second_team_seed, event_id from matches
                where event_id = ? and match_bracket_seed = ?", array($eventId, $match['match_second_team_seed']));

          if ($secondSeed['match_status'] == 'finished') {
            $winner = ($secondSeed['match_first_team_score'] > $secondSeed['match_second_team_score']) ? $secondSeed['match_first_team'] : $secondSeed['match_second_team'];
            try {
              Db::edit('matches', array('match_second_team' => $winner), 'where event_id = ? and match_bracket_seed = ?', array($eventId, $match['match_bracket_seed']));
            } catch (PDOException $e) {
              throw new UserError($e);
            }
          }
        }
      } elseif ($match['match_description'] == "Final" && $match['match_status'] == 'finished') {
        $winner = ($match['match_first_team_score'] > $match['match_second_team_score']) ? $match['match_first_team'] : $secondSeed['match_second_team'];
        $event = Db::singleQuery("SELECT event_timestamp, event_winner from events where event_id = ?", array($eventId));
        if (!isset($event['event_winner'])) {
          try {
            Db::edit("events", array("event_winner" => $winner, "event_timestamp" => $event['event_timestamp'], "event_status" => "finished"), 'where event_id = ?', array($eventId));
          } catch (PDOException $e) {
            throw new UserError($e);
          }
        }
      }
    }
  }

  public function insertMatches(array $matches, $eventId, array $teamsInEvent)
  {
    try {
      $teamsInEventParsed = array();
      foreach ($teamsInEvent as $team) {
        $teamsInEventParsed[] = $team['id'];
      }
      $seedIncrement = 1;
      foreach ($matches as $match) {
        $match['match_description'] = (isset($match['match_description'])) ? $match['match_description'] : '';
        if (!in_array($match['match_first_team'], $teamsInEventParsed) && !in_array($match['match_second_team'], $teamsInEventParsed)) {
          Db::insert('matches', array(
            'match_first_team' => $match['match_first_team'], 'match_second_team' => $match['match_second_team'], 'match_round' => $match['match_round'], 'match_status' => $match['match_status'],
            'event_id' => $eventId, 'match_first_team_score' => $match['match_first_team_score'], 'match_second_team_score' => $match['match_second_team_score'], 'match_bracket_seed' => $match['match_bracket_seed'], 'match_description' => $match['match_description'],
            'match_first_team_seed' => $seedIncrement, 'match_second_team_seed' => ($seedIncrement + 1)
          ));
          $seedIncrement += 2;
        } else {
          Db::insert('matches', array(
            'match_first_team' => $match['match_first_team'], 'match_second_team' => $match['match_second_team'], 'match_round' => $match['match_round'], 'match_bracket_seed' => $match['match_bracket_seed'], 'match_round' => $match['match_round'], 'match_status' => $match['match_status'],
            'match_description' => $match['match_description'], 'event_id' => $eventId, 'match_first_team_score' => $match['match_first_team_score'], 'match_second_team_score' => $match['match_second_team_score']
          ));
        }
      }
    } catch (PDOException $e) {
      throw new UserError($e);
    }
  }

  public function returnMatchesInEvent($eventId)
  {
    return Db::multiQuery("SELECT match_id, (select team_name from teams where team_id = match_first_team) as match_first_team_name,(select team_name from teams where team_id = match_second_team) as match_second_team_name,
      match_first_team, match_second_team, match_round, match_first_team_score, match_second_team_score,match_first_team_seed, match_second_team_seed, match_status, event_id, match_description from matches
      where event_id = ? order by match_id asc", array($eventId));
  }

  public function returnRoundMatches($eventId, string $roundIdentifier = "B1")
  {
    return Db::multiQuery("SELECT match_id, (select team_name from teams where team_id = match_first_team) as match_first_team_name,(select team_name from teams where team_id = match_second_team) as match_second_team_name,
    match_first_team, match_second_team, match_round, match_first_team_score, match_second_team_score,match_first_team_seed, match_second_team_seed, match_status, event_id, match_description from matches
    where event_id = ? and match_round = ? order by match_id asc", array($eventId, $roundIdentifier));
  }

  public function returnParsedMatchesInEvent($eventId)
  {
    $parsedMatches = array();
    $matches = $this->returnMatchesInEvent($eventId);
    $numOfRounds = Db::query("SELECT match_round from matches where event_id = ? group by match_round", array($eventId));
    for ($i = 0; $i < $numOfRounds; $i++) {
      $roundId = "B" . ($i + 1);
      $roundMatches = $this->returnRoundMatches($eventId, $roundId);
      foreach ($roundMatches as $roundMatch) {
        if ($roundMatch['match_second_team'] == "freewin") {
          $roundMatch['match_second_team_name'] = "freewin";
        }
        $parsedMatches[$i][] = $roundMatch;
      }
    }
    return $parsedMatches;
  }

  public function dropBracket($eventId)
  {
    Db::query("DELETE from matches where event_id = ?", array($eventId));
  }

  public function bracketInEvent($eventId)
  {
    return (Db::query("SELECT match_id from matches where event_id = ?", array($eventId)) > 0) ? true : false;
  }

  public function returnMatchIds()
  {
    $realMatchIds = array();
    $matchIds = Db::multiQuery("SELECT match_id from matches");
    foreach ($matchIds as $match) {
      $realMatchIds[] = $match['match_id'];
    }
    return $realMatchIds;
  }

  public function returnMatchById($matchId)
  {
    return Db::singleQuery("SELECT match_id, (select team_name from teams where team_id = match_first_team) as match_first_team_name,(select team_name from teams where team_id = match_second_team) as match_second_team_name,
        match_first_team, match_second_team, match_round, match_first_team_score, match_second_team_score,match_first_team_seed, match_second_team_seed, match_status, event_id, match_description from matches
        where match_id = ?", array($matchId));
  }

  public function editMatch($matchId, $matchFirstTeamScore, $matchSecondTeamScore, $matchStatus)
  {
    Db::edit('matches', array('match_first_team_score' => $matchFirstTeamScore, 'match_second_team_score' => $matchSecondTeamScore, 'match_status' => $matchStatus), 'where match_id = ?', array($matchId));
  }

  public function concludeWinner($eventId, $winnerId)
  {
    Db::edit('events', array('event_winner' => $winnerId), 'where event_id = ?', array($eventId));
  }
}
