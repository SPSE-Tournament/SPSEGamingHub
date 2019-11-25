<?php
  class TeamManager {

    public function returnTeams():array {
      return Db::multiQuery("SELECT teams.team_id, team_name, team_captain_id, teams.game_id, games.game_name, (select count(*) from teamparticipation where teamparticipation.team_id = teams.team_id) as team_usercount from teams
         join teamparticipation on teamparticipation.team_id = teams.team_id
         join games on games.game_id = teams.game_id group by team_name");
    }

    public function returnUserTeams($userId):array {
      return Db::multiQuery("SELECT teams.team_id, team_name, team_captain_id, teams.game_id, games.game_name,games.game_icon, (select count(*) from teamparticipation where teamparticipation.team_id = teams.team_id) as team_usercount from teams
         join teamparticipation on teamparticipation.team_id = teams.team_id
         join games on games.game_id = teams.game_id
          where teamparticipation.user_id = ? order by team_usercount desc", array($userId));
    }

    public function returnUserTeamsCount($userId):int {
      return (int)Db::query("SELECT teams.team_id, team_name, team_captain_id, game_id from teams join teamparticipation on
        teamparticipation.team_id = teams.team_id where teamparticipation.user_id = ?", array($userId));
    }

    public function returnUsersInATeam($teamId):array{
      return Db::multiQuery("SELECT users.user_id, users.name as uname, users.user_verified as user_verified from teamparticipation
        join users on users.user_id = teamparticipation.user_id
      where team_id = ?", array($teamId));
    }

    public function returnUsersInATeamCount($teamId):int{
      return (int)Db::query("SELECT user_id from teamparticipation
      where team_id = ?", array($teamId));
    }

    public function returnUserTeamsWithPlayers($userId):array {
      $userTeams = $this->returnUserTeams($userId);
      $userTeamsWithPlayers = array();
      foreach ($userTeams as $team) {
        $usersInATeam = $this->returnUsersInATeam($team['team_id']);
        $users = array();
        foreach ($usersInATeam as $user) {
            $users[] = $user['uname'];
          }
          $userTeamsWithPlayers[] = array('id' => $team['team_id'],'name' => $team['team_name'], 'game' => $team['game_name'],'game_icon'=>$team['game_icon'], 'players' => $users);
      }
      return $userTeamsWithPlayers;
    }

    public function returnTeamById($teamId){
      return Db::singleQuery("SELECT team_id, team_name, team_captain_id, teams.game_id as game_id, games.game_name as game_name from teams
        join games on games.game_id = teams.game_id
      where team_id = ?", array($teamId));
    }

    public function teamExists($teamId):int{
      return Db::query("SELECT team_id, team_name, team_captain_id, game_id from teams
      where team_id = ?", array($teamId));
    }

    public function returnTeamsInEvent(array $teamIds):array {
        $teams = array();
        foreach ($teamIds as $teamId) {
          $team = $this->returnTeamById($teamId['team_id']);
          $usersInATeam = $this->returnUsersInATeam($teamId['team_id']);
          $players = array();
          foreach ($usersInATeam as $user) {
            $players[] = $user['uname'];
          }
          $teams[] = array('id'=>$team['team_id'],'name' => $team['team_name'], 'players' => $players, 'teamCaptain' => $team['team_captain_id']);
        }
        return $teams;
    }

    public function liveSearchTeams($str):array {
        $strL = mb_strtolower($str);
        if (strlen($strL) >= 3) {
            $teams = $this->returnTeams();
            $teamsToReturn = array();
            foreach ($teams as $team) {
              if (mb_strtolower(substr($team['team_name'], 0, strlen($strL))) == $strL)
                  $teamsToReturn[] = array("name"=>$team['team_name'], "id"=>$team['team_id']);
            }
            if (count($teamsToReturn) > 0)
              return $teamsToReturn;
              else {
              $teamsToReturn[] = array("name"=>"No hint suggested");
              return $teamsToReturn;
          }
        } else {
          $teamsToReturn[] = array("name"=> " ");
          return $teamsToReturn;
        }
    }


    public function insertTeam($teamName, $captainId, $gameId):void {
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

    public function verifyTeam($teamId):void {
      $users = $this->returnUsersInATeam($teamId);
      foreach ($users as $user) {
        Db::edit("users", array('user_verified'=>1),"where user_id = ?",array($user['user_id']));
      }
    }

    public function insertTeamParticipation($userId, $teamId):void {
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

    public function removeTeam($teamId):void {
      Db::query("DELETE from teams where team_id = ?", array($teamId));
    }


  }

 ?>
