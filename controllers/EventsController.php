<?php
  class EventsController extends Controller {
    public function parse($params) {
      $eventManager = new EventManager();
      $gameManager = new GameManager();
      $teamMan = new TeamManager();
      $bracketManager = new BracketManager();
      $events = $eventManager->returnEvents();
      $eventUrls = array();

      for ($i=0; $i < count($events); $i++) {
            $eventUrls[] = $events[$i]['event_url'];
      }
      $this->data['admin'] = $_SESSION['admin'];

      //Routing
      if (!empty($params[0])) {
        if (in_array($params[0], $eventUrls)) {
          $event = $eventManager->returnEventByUrl($params[0]);
          $eventTeamIds = $eventManager->returnTeamIdsInEvent($event['event_id']);
          $eventTeams = $teamMan->returnTeamsInEvent($eventTeamIds);
          $this->data['event'] = $event;
          $this->data['teams'] = $eventTeams;
          $this->data['eventIds'] = $eventTeamIds;
          if ($event['bracket_status'] == 'live')
            $this->data['hasBrackets'] = true;
          else
            $this->data['hasBrackets'] = false;
          $this->header['page_title'] = $event['event_name'];
          $this->view = "event";
        } else if ($params[0] == 'edit' && !empty($params[1]) && in_array($params[1], $eventUrls)) {
              if (!UserManager::authAdmin()) {
                $this->addMessage("Admin rights needed.");
                $this->redir("home");
              }
              $this->data['event'] = $eventManager->returnEventByUrl($params[1]);
              $this->data['games'] = $gameManager->returnGames();
              $this->view = "editevent";
        } else if ($params[0] == "getmatch") {
          $matchIds = $bracketManager->returnMatchIds();
            if (!empty($params[1]) && in_array($params[1],$matchIds)) {
              if (!UserManager::authAdmin()) {
                $this->addMessage("Admin rights needed.");
                $this->redir("home");
              }
              $match = $bracketManager->returnMatchById($params[1]);
              $this->data['match'] = $match;
              $this->view = 'matchpreview';
            }
        } else if ($params[0] == "refreshmatches") {
            if (!empty($params[1])) {
              $match = $bracketManager->checkMatches($params[1]);
            }
        } else if ($params[0] == "getbracket") {
            if (!empty($params[1])) {
              if (!UserManager::authAdmin()) {
                $this->redir("events");
              }
              $event = $eventManager->returnEventById($params[1]);
              $this->data['event'] = $event;
              if ($event['bracket_status'] == 'live') {
                $this->data['hasBrackets'] = true;
                $this->data['matches'] = $bracketManager->returnParsedMatchesInEvent($event['event_id']);
                $this->view = "bracket";
              }
            }
        } else {
          $this->redir("events");
        }
      } else {
        $this->data['events'] = $events;
        $this->data['user'] = $_SESSION['user'];
        $this->data['userTeams'] = $teamMan->returnUserTeams($_SESSION['user']['user_id']);
        $this->header['page_title'] = "Events";
        $this->view = "events";
      }

      //Handling POST
      if ($_POST) {
        if (isset($_POST['event-join'])) {
          try {
            //Validate event join, compare playerlimit vs numplayers in team, compare games
              $game = $gameManager->returnGameById($_POST['game-id']);
              $numPlayers = $teamMan->returnUsersInATeamCount($_POST['team-id']);
              $team = $teamMan->returnTeamById($_POST['team-id']);
              $event = $eventManager->returnEventById($_POST['event-id']);
              if ($numPlayers == $game['game_playerlimitperteam']) {
                if ($team['game_id'] == $_POST['game-id']) {
                  $usersInATeam = $teamMan->returnUsersInATeam($_POST['team-id']);
                  $verifiedPlayers = 0;
                  foreach ($usersInATeam as $user) {
                    if ($user['user_verified'] == 1) {
                      $verifiedPlayers++;
                    }
                  }
                  if ($verifiedPlayers == $game['game_playerlimitperteam']) {
                    foreach ($usersInATeam as $user) {
                        $eventManager->insertEventParticipation($user['user_id'], $_POST['event-id'], $_POST['team-id']);
                        $this->logDifferentUser($user['user_id'],
                        'User has joined an event: ('.$_POST['event-id'].')' . ' ' . $event['event_name'] . ' with a team: (' . $_POST['team-id'] . ')' . ' '. $team['team_name']
                        ,'event_join');
                    }
                  } else {
                    $this->addMessage("Users in that team are not verified.");
                    $this->redir("events");
                  }
                  $this->addMessage("Event joined succesfully!");
                  $this->redir("events/".$_POST['event-url']);
                } else {
                  $this->addMessage("Wrong game!");
                  $this->redir("events");
                }
              } else {
                $this->addMessage("Team has a wrong amount of players!");
                $this->redir("events");
              }
          } catch (PDOException $e) {
            $this->addMessage($e);
          }
        }
        if (isset($_POST['event-edit'])) {
            $eventManager->updateEvent($_POST['eventName'], $_POST['eventGame'], $_POST['eventDate'], $_POST['eventTime'], $_POST['eventPL'], $_POST['eventUrl'], $params[1]);
            $this->addMessage("Event has been updated");
            $this->log("Event has been updated", 'event_edit');
            $this->redir("events");
        }
        if (isset($_POST['bracket-generate'])) {
          try {
            if (!$bracketManager->bracketInEvent($_POST['event-id'])) {
              $eventTeams = $teamMan->returnTeamsInEvent($eventManager->returnTeamIdsInEvent($_POST['event-id']));
              $bracketManager->insertMatches($bracketManager->generateMatches($eventTeams,0),$_POST['event-id'],$eventTeams);
              $eventManager->setLiveBracketStatus($_POST['event-id']);
              $this->addMessage("Bracket created!");
              $this->log("Bracket has been generated", 'bracket_creation');
              $this->redir("events/".$params[0]);
            } else {
              $this->addMessage("Event already has bracket!");
              $this->redir("events/".$params[0]);
            }

          } catch (PDOException $e) {
            $this->addMessage($e);
          }
        }
        if (isset($_POST['bracket-drop'])) {
          try {
            $bracketManager->dropBracket($_POST['event-id']);
            $this->addMessage("Bracket dropped!");
            $this->log("Bracket has been dropped", 'bracket_drop');
            $this->redir("events/".$params[0]);
          } catch (PDOException $e) {
            $this->addMessage($e);
          }
        }
        if (isset($_POST['match-score-write'])) {
          try {
            $match = $bracketManager->returnMatchById($_POST['match-id']);
              $bracketManager->editMatch($_POST['match-id'],$_POST['first-score'],$_POST['second-score'],$_POST['match-status']);
              $this->addMessage("Match edited.");
              $this->redir("events/".$params[0]);
          } catch (PDOException $e) {
            $this->addMessage($e);
          }
        }
      }

    }
  }

?>
