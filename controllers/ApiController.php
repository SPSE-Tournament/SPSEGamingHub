<?php
class ApiController extends Controller
{
  public function parse($params)
  {
    $bracketManager = new BracketManager();
    $eventManager = new EventManager();
    $teamManager = new TeamManager();
    $messageManager = new MessageManager();
    $userManager = new UserManager();
    $gameManager = new GameManager();
    $logManager = new LogManager();

    if (!empty($params)) {
      switch ($params[0]) {
        default:
          $this->data['response'] = ['status' => 400, 'error' => "Bad endpoint"];
          break;
        case "match":

          $matchIds = $bracketManager->returnMatchIds();
          if (!empty($params[1]) && in_array($params[1], $matchIds)) {
            if (!$userManager->checkPL("watchman")) {
              $this->data['response'] = ['status' => 403, 'error' => "Forbidden"];
            }
            $match = $bracketManager->returnMatchById($params[1]);
            $this->data['response'] = $match;
          } else {
            $this->data['response'] = ['status' => 400, 'error' => "Bad endpoint"];
          }


          break;

        case "bracket":

          if (!empty($params[1])) {
            $event = $eventManager->returnEventById($params[1]);

            if ($event && $event['bracket_status'] == 'live') {
              $bracketManager->checkMatches($event['event_id']);
              $this->data['hasBrackets'] = true;
              $this->data['response'] = $bracketManager->returnParsedMatchesInEvent($event['event_id']);
            } else {
              $this->data['response'] = ["status" => "no bracket"];
            }
            $this->data['event'] = $event;
          } else {
            return;
          }


          break;
        case "messages":

          if (!$userManager->checkPL("user")) {
            $this->data['response'] = ['status' => 403, 'error' => "Forbidden"];
          }

          $this->data['response'] = $messageManager->returnMessages($_SESSION['user']['user_id']);
          if (!empty($params[1]) && !empty($params[2])) {
            $m = $messageManager;
            switch ($params[1]) {
              case "id":
                $this->data['response'] = $m->returnMessageById($params[2]);
                break;
              case "type":
                $this->data['response'] = $m->returnMessageByType($params[2]);
                break;
            }
          }

          break;

        case "teams":

          if (!$userManager->checkPL("user")) {
            $this->data['response'] = ['status' => 403, 'error' => "Forbidden"];
          }
          if (!empty($params[1]) && !empty($params[2])) {
            switch ($params[1]) {
              case "id":
                $this->data['response'] = $teamManager->returnTeamById($params[2]);
                $this->data['response']['players'] = $teamManager->returnUsersInATeam($params[2]);
                break;
              case "livesearch":
                $this->data['response'] = $teamManager->liveSearchTeams($params[2]);
                break;
            }
          } else {
            $this->data['response'] = $teamManager->returnTeams();
          }

          break;
        case "games":

          if (!$userManager->checkPL("watchman")) {
            $this->data['response'] = ['status' => 403, 'error' => "Forbidden"];
          }
          if (!empty($params[1])) {
            if (!empty($params[2])) {
              switch ($params[1]) {
                default:
                  $this->data['response'] = ['status' => 400, 'error' => "Bad endpoint"];
                  break;
                case "id":
                  $this->data['response'] = $gameManager->returnGameById($params[2]);
                  break;
              }
            } else {
              $this->data['response'] = ['status' => 400, 'error' => "Bad endpoint"];
            }
          } else {
            $this->data['response'] = $gameManager->returnGames();
          }

          break;
        case "users":

          if (!empty($params[1]) && !empty($params[2])) {
            switch ($params[1]) {
              default:
                $this->data['response'] = ['status' => 400, 'error' => "Bad endpoint"];
                break;
              case "name":
                ["name" => $name, "user_hexid" => $hex] = $userManager->selectUser($params[2]);
                $this->data['response'] = ["name" => $name, "hexid" => $hex];
                break;
              case "livesearch":
                $this->data['response'] = $userManager->liveSearchUsers($params[2]);
                break;
              case "checkpl":
                $this->data['response'] = ["response" => $userManager->checkPL("admin")];
                break;
            }
          } elseif ($userManager->checkPL("admin")) {
            $this->data['response'] = $userManager->returnUsers();
          } else {
            $this->data['response'] = ['status' => 403, 'error' => "Forbidden"];
          }

          break;
        case "logs":

          if ($userManager->checkPL("admin")) {
            if (!empty($params[1])) {
              switch ($params[1]) {
                default:
                  $this->data['response'] = ['status' => 400, 'error' => "Bad endpoint"];
                  break;
                case "id":
                  $log = $logManager->returnLogById($params[2]);
                  $this->data['response'] = ($log) ? $log : ['status' => 404, 'error' => "Log not found"];
                  break;
                case "filter":
                  if (!empty($_GET)) {
                    if (isset($_GET['order']) && in_array($_GET['order'], $logManager->columns)) {
                      $this->data['response'] = (isset($_GET['order_direction']) && $_GET['order_direction'] == "asc")
                        ? $logManager->returnLogsOrderBy($_GET['order'], "asc") : $logManager->returnLogsOrderBy($_GET['order']);
                    }
                  } else {
                    $this->data['response'] = ['status' => 400, 'error' => "Bad endpoint"];
                  }
                  break;
              }
            } else {
              $this->data['response'] = $logManager->returnLogs();
            }
          } else {
            $this->data['response'] = ['status' => 403, 'error' => "Forbidden"];
          }

          break;
      }
    } else {
      HTTP::status(404);
      $this->redir("status/404");
    }
    $this->data['dump'] = $bracketManager->returnParsedMatchesInEvent(23);
    $this->view = "raw";
  }
}
