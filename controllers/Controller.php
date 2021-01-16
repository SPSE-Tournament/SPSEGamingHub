<?php
abstract class Controller
{
  protected $data = array();
  protected $view = "";
  protected $header = array('page_title' => '', 'page_keywords' => '', 'page_desc' => '');

  abstract function parse($params);

  #Sanitizes all view variables to htmlspecialchars, if want == nosanitize then use prefix $_
  private function sanitize($x = null)
  {
    if (!isset($x)) {
      return null;
    } elseif (is_string($x)) {
      return htmlspecialchars($x, ENT_QUOTES);
    } elseif (is_array($x)) {

      foreach ($x as $key => $v) {
        $x[$key] = $this->sanitize($v);
      }
      return $x;
    } else {
      return $x;
    }
  }

  public function showView()
  {
    if ($this->view) {
      extract($this->sanitize($this->data));
      extract($this->data, EXTR_PREFIX_ALL, "");
      require("views/" . $this->view . ".phtml");
    }
  }

  protected function refresh()
  {
    $loc = $_SERVER['REQUEST_URI'];
    header("Location: $loc");
    header("Connection: close");
    exit;
  }

  protected function redir($where)
  {
    header("Location: /$where");
    header("Connection: close");
    exit;
  }

  protected function addMessage($message)
  {
    if (isset($_SESSION['messages'])) {
      $_SESSION['messages'][] = $message;
    } else {
      $_SESSION['messages'] = array($message);
    }
  }

  protected static function returnMessages()
  {
    if (isset($_SESSION['messages'])) {
      $messages = $_SESSION['messages'];
      unset($_SESSION['messages']);
      return $messages;
    } else {
      return array();
    }
  }

  protected function authenticateUser($admin = false)
  {
    $userManager = new UserManager();
    $usr = $userManager->returnUser();
    if (!$usr or ($admin and !$usr['admin'])) {
      $this->addMessage("Nedostatečná oprávnění.");
      $this->redir('login');
    }
  }

  protected function log($msg, $type)
  {
    $logMan = new LogManager();
    $date = new DateTime();
    $logMan->log($_SESSION['user']['user_id'], $msg, $type, $date->format('Y-m-d H:i:s'), $_SERVER['REMOTE_ADDR']);
    if ($_SERVER['REMOTE_ADDR'] != "127.0.0.1" && $_SERVER['REMOTE_ADDR'] != $_ENV['ADMIN_IP']) {
      $this->logDiscord($_SESSION['user']['user_id'], $_SESSION['user']['name'], $msg, $type);
    }
  }

  protected function logDifferentUser($userId, $usrname, $msg, $type)
  {
    $logMan = new LogManager();
    $date = new DateTime();
    $logMan->log($userId, $msg, $type, $date->format('Y-m-d H:i:s'), $_SERVER['REMOTE_ADDR']);
    if ($_SERVER['REMOTE_ADDR'] != "127.0.0.1" && $_SERVER['REMOTE_ADDR'] != $_ENV['ADMIN_IP']) {
      $this->logDiscord($userId, $usrname, $msg, $type);
    }
  }

  protected function logDiscord($userId, $usrName, $msg, $type)
  {
    $disCid = $_ENV['DISCORD_CID'];
    $disToken = $_ENV['DISCORD_TOKEN'];
    $disUsername = "WebLog";
    $URL = "https://discordapp.com/api/webhooks/" . $disCid . "/" . $disToken;
    $PD = array();
    $PD["username"] = $disUsername;
    $PD["content"] = "```\n" . date('D M d, Y G:i:s a') . "\n" .
      "User: " . $userId . " ($usrName)" . "\n" .
      "Log_type: " . $type . "\n" .
      "Message: " . $msg . "\n" .
      "User_ip: " . $_SERVER['REMOTE_ADDR'] . "\n" .
      "```";
    $PD = json_encode($PD);
    $HTTP = array(
      'http' =>
      array(
        'method'  => 'POST',
        'header'  => 'Content-type: application/json; charset=UTF-8',
        'content' => $PD
      )
    );
    $context = stream_context_create($HTTP);
    file_get_contents($URL, false, $context);
  }

  protected function checkLogged()
  {
    if (isset($_SESSION['user'])) {
      $_SESSION['logged'] = true;
      return true;
    } else {
      $_SESSION['logged'] = false;
      return false;
    }
  }

  protected function checkAdmin()
  {
    if (isset($_SESSION['user']) && $_SESSION['user']['admin'] == 1) {
      $_SESSION['admin'] = true;
    } else {
      $_SESSION['admin'] = false;
    }
  }

  protected function isParam($params, $paramIndex, $paramQuery)
  {
    return (!empty($params) && $params[$paramIndex] == $paramQuery);
  }
}
