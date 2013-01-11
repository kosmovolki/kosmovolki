<?php




define('INSIDE'  , true);
define('INSTALL' , false);
define('HIDE_MENU'  , true);

$InLogin = true;


$ugamela_root_path = './';
include($ugamela_root_path . 'extension.inc');
include($ugamela_root_path . 'common.' . $phpEx);
  includeLang('login');
	

	
	if ($_POST) {

		$login = doquery("SELECT * FROM {{table}} WHERE `username` = '" . ($_POST['username']) . "' LIMIT 1", "users", true);

		if ($login) {
			if ($login['password'] == md5($_POST['password'])) {
				if (isset($_POST["rememberme"])) {
					$expiretime = time() + 31536000;
					$rememberme = 1;
				} else {
					$expiretime = 0;
					$rememberme = 0;
				}

				@include('config.php');
				$cookie = $login["id"] . "/%/" . $login["username"] . "/%/" . md5($login["password"] . "--" . $dbsettings["secretword"]) . "/%/" . $rememberme;
				setcookie($game_config['COOKIE_NAME'], $cookie, $expiretime, "/", "", 0);

				$q="insert into `game_sid` (`time`,`uid`,`sid`, `ip`, `agent`) VALUE('".time()."','".$login['id']."','".intval($_POST['sid'])."', '{$_SERVER['HTTP_X_REAL_IP']}', '{$_SERVER['HTTP_USER_AGENT']}')";
				doquery($q);
				
				unset($dbsettings);
				header("Location: ./overview.php");
				exit;
			} else {
				message($lang['Login_FailPassword'], $lang['Login_Error']);
			}
		} else {
			message($lang['Login_FailUser'], $lang['Login_Error']);
		}
	} elseif(!empty($_COOKIE[$game_config['COOKIE_NAME']])) {
			//die('Технические работы'); умри ваще нахер
		$cookie = explode('/%/',$_COOKIE[$game_config['COOKIE_NAME']]);
		$login = doquery("SELECT * FROM {{table}} WHERE `username` = '" . mysql_escape_string($cookie[1]) . "' LIMIT 1", "users", true);
		if ($login) {
			@include('config.php');
			if (md5($login["password"] . "--" . $dbsettings["secretword"]) == $cookie[2]) {
				unset($dbsettings);
				header("Location: ./overview.php");
				exit;
			} 
		}
	} 
	
	$parse = $lang;
	$query = doquery('SELECT username FROM {{table}} ORDER BY register_time DESC', 'users', true);
	$parse['last_user'] = $query['username'];
	$query = doquery("SELECT COUNT(DISTINCT(id)) as `cnt` FROM {{table}} WHERE onlinetime>" . (time()-900), 'users', true);	
	$parse['online_users'] = $query['cnt'];
	$query = doquery("SELECT COUNT(DISTINCT(id)) as `cnt` FROM {{table}} ", 'users', true);	
//	$parse['users_amount'] = $game_config['users_amount'];
	$parse['users_amount'] = $query['cnt'];
	$parse['servername'] = $game_config['game_name'];
	$parse['forum_url'] = $game_config['forum_url'];
	$parse['PasswordLost'] = $lang['PasswordLost'];
	$parse['test'] = isset($_GET['test'])?'true':'false';
	$parse['counters'] = gettemplate('counter_body');


	$page = parsetemplate(gettemplate('login_body'), $parse);
	display($page, $lang['title']);


 


?>
