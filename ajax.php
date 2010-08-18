<?

// Functions
function page($url) {
	$menuArr = array(
		'default' =>	'<a href=".">Main page</a><a href="about.html">About KB CMS</a>',
		'loggedin' =>	'<a href=".#main">Main page</a>'.
			'<a href=".#pages">Pages</a>'.
			'<a href=".#settings">Settings</a>'.
			'<a href="about.html">About KB CMS</a>'
	);
	switch($url) {
		case 'main':
			if(!isset($_SESSION['user'])) return json_encode(array('redirect','.'));
			
			$content = '<h1>Main page</h1>You\'re logged in to KB CMS.<br/><br/><a href="#" onclick="ajax({a:\'logout\'})">Log out</a>';
			$menu = $menuArr['loggedin'];
			break;
		case 'pages':
			if(!isset($_SESSION['user'])) return json_encode(array('redirect','.'));
			
			$content = '<h1>Pages</h1>Feature not supported, yet.';
			$menu = $menuArr['loggedin'];
			break;
		case 'settings':
			if(!isset($_SESSION['user'])) return json_encode(array('redirect','.'));
			
			$content = '<h1>Settings</h1><h2>Change password</h2>'.
				'<form><label>New password:</label><input type="text" name="pass"/><br/><label>Repeat new password:</label><input type="text" name="pass2"/><br/><br/><input type="submit" value="Submit"/></form>';
			$menu = $menuArr['loggedin'];
			break;
		default:
			KBTB::req(false, 'Invalid input (page: "'.$url.'")');
			break;
	}
	return json_encode(array('page',$url,$content,$menu));
}

function main() {
	session_start();
	$cfg = getCfg();
	
	switch($_POST['a']) {
		case 'logout':
			unset($_SESSION['user']);
			echo(json_encode(array('redirect','.')));
			break;
		case 'checklogin':
			if(isset($_SESSION['user'])) echo(page('main'));
			else echo(json_encode(array('callbackCustom','loginFocus')));
			break;
		case 'login':
			$fieldErrs = array();
			
			$login = false;
			foreach($cfg['users'] as $user) if(!$login && $user['user']==$_POST['user'] && crypt($_POST['pass'],$user['pass'])==$user['pass']) $login=$user['user'];
			
			if($login===false) echo(json_encode(array('fieldErrs',array('user'=>'Incorrect username and/or password.','pass'=>'Incorrect username and/or password.'))));
			else {
				$_SESSION['user'] = $user['user'];
				echo(page('main'));
			}
			break;
		case 'page':
			echo(page($_POST['p']));
			break;
		default:
			KBTB::req(false,'Invalid input (a).');
			break;
	}
	die();
}

function getCfg() {
	if(!function_exists('cfg')) {
		$file = 'config.php';
		if(is_file($file) && is_readable($file)) {
			require_once($file);
		}else return array(
			'users' =>	array(
				array(
					'user' =>	'admin',
					'pass' =>	crypt('changeme')
				)
			)
		);
	}
	return cfg();
}


// Classes
class KBTB { // Toolbox
	function valid($types,$var,$var2=null,$var3=null) {
		$valid = true;
		foreach(explode(',',$types) as $type) {
			switch($type) {
				case 'int':
					$valid = $valid && $var!==true && (string)$var==(string)(int)$var;
					break;
				case '>':
					$valid = $valid && $var>$var2;
					break;
				case '<':
					$valid = $valid && $var<$var2;
					break;
				case '><':
					$valid = $valid && $var>$var2 && $var<$var3;
					break;
				case 'usbi': //mysql unsigned bigint
					$valid = $valid && KBTB::valid('int',$var) && $var>=0 && $var<10000000000000000000;
					break;
				case 'strlen':
					$valid = $valid && strlen($var)>$var2 && strlen($var)<$var3;
					break;
				case 'email': // valid e-mail, and less than var2 in length if set
					$valid = $valid && (is_null($var2) || strlen($var)<$var2) && (strlen($var) > 5 && preg_match('/^([.0-9a-z_-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i', $var) !== false);
					break;
				case 'IP':
					$valid = $valid && preg_match('/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/', $var);
					break;
				case 'url':
					$regex = '/^((https?|ftp)\:\/\/)?'; // SCHEME 
					$regex .= '([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?'; // User and Pass 
					$regex .= '([a-z0-9-.]*)\.([a-z]{2,3})'; // Host or IP 
					$regex .= '(\:[0-9]{2,5})?'; // Port 
					$regex .= '(\/([a-z0-9+\$_-]\.?)+)*\/?'; // Path 
					$regex .= '(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?'; // GET Query 
					$regex .= '(#[a-z_.-][a-z0-9+\$_.-]*)?$/i'; // Anchor 
					$valid = $valid && preg_match($regex, $var);
					break;
			}
		}
		return $valid;
	}
	function req($value,$errMsg = false) {
		if(!$value) {
			while(ob_get_clean());
			throw new Exception($errMsg ? $errMsg : 'Unknown error');
		}else return $value;
	}
	function debug($value, $continue = false) {
		header('Content-type: text/plain');
		while(ob_get_level()>0) ob_end_clean();
		var_dump($value);
		if(!$continue) die();
	}
	function html_encode($var) {
		return htmlentities($var, ENT_QUOTES, 'UTF-8');
	}
	function attr_encode($var) {
		return htmlspecialchars($var, ENT_QUOTES, 'UTF-8');
	}
	function inpath($path) {
		return ereg('^'.addslashes(realpath('.').DIRECTORY_SEPARATOR),realpath($path));
	}
}



// Init
main();


?>
