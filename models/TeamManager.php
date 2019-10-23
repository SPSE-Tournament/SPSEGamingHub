<?php
  class TeamManager {
    public function returnUserTeams($userId) {
      return Db::multiQuery("SELECT teams.team_id, team_name, team_captain_id, teams.game_id, games.game_name from teams
         join teamparticipation on teamparticipation.team_id = teams.team_id
         join games on games.game_id = teams.game_id
          where teamparticipation.user_id = ?", array($userId));
    }

    public function returnUserTeamsCount($userId) {
      return Db::query("SELECT teams.team_id, team_name, team_captain_id, game_id from teams join teamparticipation on
        teamparticipation.team_id = teams.team_id where teamparticipation.user_id = ?", array($userId));
    }


    public function insertTeam($teamName, $captainId, $gameId) {
      $team = array(
        'team_name' => $teamName,
        'team_captain_id' => $captainId,
        'game_id' => $gameId
      );
      try {
        Db::insert('teams', $team);
      } catch(PDOException $e) {
        throw new UserError($e);
      }

    }

    public function insertTeamParticipation($userId, $teamId) {
      $teamPar = array(
        'user_id' => $userId,
        'team_id' => $teamId
      );
      try {
        Db::insert('teamparticipation', $teamPar);
      } catch(PDOException $e) {
        throw new UserError($e);
      }

    }


  }

 ?>
