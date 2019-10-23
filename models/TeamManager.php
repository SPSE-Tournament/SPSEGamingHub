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

    public function returnUsersInATeam($teamId){
      return Db::multiQuery("SELECT users.user_id, users.name as uname from teamparticipation
        join users on users.user_id = teamparticipation.user_id
      where team_id = ?", array($teamId));
    }

    public function returnUserTeamsWithPlayers($userId) {
      $userTeams = $this->returnUserTeams($userId);
      $userTeamsWithPlayers = array();
      foreach ($userTeams as $team) {
        $usersInATeam = $this->returnUsersInATeam($team['team_id']);
        $users = array();
        foreach ($usersInATeam as $user) {
            $users[] = $user['uname'];
          }
          $userTeamsWithPlayers[] = array('name' => $team['team_name'], 'game' => $team['game_name'], 'players' => $users);
      }
      return $userTeamsWithPlayers;
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
