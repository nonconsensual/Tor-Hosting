<?php
include('../common.php');
try{
	$db=new PDO('mysql:host=' . DBHOST . ';dbname=' . DBNAME, DBUSER, DBPASS, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_WARNING, PDO::ATTR_PERSISTENT=>PERSISTENT]);
}catch(PDOException $e){
	die('No Connection to MySQL database!');
}
header('Content-Type: text/html; charset=UTF-8');
session_start();
if(!empty($_SESSION['hosting_username'])){
	header('Location: home.php');
	exit;
}
echo '<!DOCTYPE html><html><head>';
echo '<title>Wowmee\'s Hosting - Register</title>';
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
echo '<meta name="author" content="Wowmee TheGreat">';
echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
echo "<!DOCTYPE html>\n";
echo "<html>\n";
echo "<title>Wowmee Web Hosting | http://hostingv5fypnk3u.onion/ Wowmee Hosting</title>\n";
echo "<meta charset=\"UTF-8\">\n";
echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n";
echo "<meta name=\"Keywords\" content=\"onion,HTML5,CSS,Web,Dark,Hosting,Deep,anonymity,bitcoin,donate,easy,domain,domin,host,freebie,design,photoshop,Wowmee Hosting.\">\n";
echo "<meta name=\"Description\" content=\"hostingv5fypnk3u.onion. Tor hosting onion\">\n";
echo "<link rel=\"icon\" href=\"/favicon.ico\" type=\"image/x-icon\">\n";
echo "<link rel=\"stylesheet\" href=\"/css/st.css\">\n";
echo "<link rel=\"stylesheet\" href=\"/css/tb.css\">\n";
echo "<link rel=\"stylesheet\" href=\"/css/font-awesome/css/font-awesome.min.css\">\n";
echo "\n";
echo "<body class=\"w3-theme-bd\">\n";
echo "<div class=\"w3-top w3-bar w3-teal\">\n";
echo "<a class=\"w3-hide-medium w3-hide-large w3-bar-item w3-button w3-theme-hm\" href=\"/\" title=\"Wowmee Hosting\"><i class=\"fa fa-home\"></i></a>\n";
echo "<a href=\"/\" class=\"w3-hide-small w3-bar-item w3-button w3-theme-hm\" title=\"Wowmee Hosting\"><i class=\"fa fa-home w3-margin-right\"></i>Wowmee Hosting</a>\n";
echo "<a href=\"/register.php\" class=\"w3-bar-item w3-gre w3-button\" title=\"Register\">Register</a>\n";
echo "<a href=\"/login.php\" class=\"w3-bar-item w3-button\" title=\"Login\">Login</a>\n";
echo "<a href=\"/list.php\" class=\"w3-hide-small w3-bar-item w3-button\" title=\"List of hosted sites\">List</a>\n";
echo "<a class=\"w3-bar-item w3-hide-medium w3-hide-large w3-button\" href=\"/list/\" title=\"List of hosted sites\"><i class=\"fa fa-sticky-note\"></i></a>\n";
echo "<a href=\"/faq.php\" class=\"w3-hide-small w3-bar-item w3-button\" title=\"FAQ\">FAQ</a>\n";
echo "<a class=\"w3-bar-item w3-hide-medium w3-hide-large w3-button w3-gre\" href=\"/faq\" title=\"FAQ\"><i class=\"fa fa-book\"></i></a>\n";
echo "<a href=\"/contact.php\" class=\"w3-bar-item w3-button w3-hide-small\" title=\"Contact me\">Contact</a>\n";
echo "<a class=\"w3-bar-item w3-button w3-hide-medium w3-hide-large\" href=\"/contact/\" title=\"Contact me\"><i class=\"fa fa-envelope\"></i></a>\n";
echo "</div>\n";
echo "<div class=\"w3-row-padding w3-padding-64\">\n";
echo "<div class=\"w3-container\">\n";
echo "<header class=\"w3-container w3-black\">\n";
echo "<h2 class=\"w3-center\">Wowmee Hosting</h2>\n";
echo "</header>\n";
echo "<div class=\"w3-container w3-grey\">\n";
echo "<p class=\"w3-left w3-tag w3-small\">All Systems Active..</p>\n";
echo "<div class=\"w3-display-container w3-container\">\n";
echo '</head><body>';
echo '<h1>Hosting - Register</h1>';
if($_SERVER['REQUEST_METHOD']==='POST'){
	$ok=true;
	$onion='';
	$public=0;
	$php=0;
	$autoindex=0;
	$hash='';
	$priv_key='';
	if($error=check_captcha_error()){
		echo "<p style=\"color:red;\">$error</p>";
		$ok=false;
	}elseif(empty($_POST['pass'])){
		echo '<p style="color:red;">Error: password empty.</p>';
		$ok=false;
	}elseif(empty($_POST['passconfirm']) || $_POST['pass']!==$_POST['passconfirm']){
		echo '<p style="color:red;">Error: password confirmation does not match.</p>';
		$ok=false;
	}elseif(empty($_POST['username'])){
		echo '<p style="color:red;">Error: username empty.</p>';
		$ok=false;
	}elseif(preg_match('/[^a-z0-9\-_\.]/', $_POST['username'])){
		echo '<p style="color:red;">Error: username may only contain characters that are in the rage of a-z (lower case) - . _ and 0-9.</p>';
		$ok=false;
	}elseif(strlen($_POST['username'])>50){
		echo '<p style="color:red;">Error: username may not be longer than 50 characters.</p>';
		$ok=false;
	}else{
		$stmt=$db->prepare('SELECT null FROM users WHERE username=?;');
		$stmt->execute([$_POST['username']]);
		if($stmt->fetch(PDO::FETCH_NUM)){
			echo '<p style="color:red;">Error: this username is already registered.</p>';
			$ok=false;
		}
	}
	if($ok){
		$check=$db->prepare('SELECT null FROM users WHERE onion=?;');
		if(isset($_REQUEST['private_key']) && !empty(trim($_REQUEST['private_key']))){
			$priv_key=trim($_REQUEST['private_key']);
			if(($pkey=openssl_pkey_get_private($priv_key))!==false){
				$details=openssl_pkey_get_details($pkey);
				if($details['bits']!==1024){
					echo '<p style="color:red;">Error: private key not of bitsize 1024.</p>';
					$ok=false;
				}else{
					$onion=get_onion($pkey);
					$check->execute([$onion]);
					if($check->fetch(PDO::FETCH_NUM)){
						echo '<p style="color:red;">Error onion already exists.</p>';
						$ok=false;
					}
				}
				openssl_pkey_free($pkey);
			}else{
				echo '<p style="color:red;">Error: private key invalid.</p>';
				$ok=false;
			}
		}else{
			do{
				$pkey=openssl_pkey_new(['private_key_bits'=>1024, 'private_key_type'=>OPENSSL_KEYTYPE_RSA]);
				openssl_pkey_export($pkey, $priv_key);
				$onion=get_onion($pkey);
				openssl_pkey_free($pkey);
				$check->execute([$onion]);
			}while($check->fetch(PDO::FETCH_NUM));
		}
		if(isset($_POST['public']) && $_POST['public']==1){
			$public=1;
		}
		if(isset($_POST['php']) && in_array($_POST['php'], [1, 2, 3])){
			$php=$_POST['php'];
		}
		if(isset($_POST['autoindex']) && $_POST['autoindex']==1){
			$autoindex=1;
		}
		$priv_key=trim(str_replace("\r", '', $priv_key));
		$hash=password_hash($_POST['pass'], PASSWORD_DEFAULT);
	}
	$check=$db->prepare('SELECT null FROM users WHERE dateadded>?;');
	$check->execute([time()-60]);
	if($ok && $check->fetch(PDO::FETCH_NUM)){
		echo '<p style="color:red;">To prevent abuse a site can only be registered every 60 seconds, but one has already been registered within the last 60 seconds. Please try again.</p>';
		$ok=false;
	}elseif($ok){
		$stmt=$db->prepare('INSERT INTO users (username, password, onion, private_key, dateadded, public, php, autoindex) VALUES (?, ?, ?, ?, ?, ?, ?, ?);');
		$stmt->execute([$_POST['username'], $hash, $onion, $priv_key, time(), $public, $php, $autoindex]);
		$create_user=$db->prepare("CREATE USER '$onion.onion'@'%' IDENTIFIED BY ?;");
		$create_user->execute([$_POST['pass']]);
		$db->exec("CREATE DATABASE IF NOT EXISTS `$onion`;");
		$db->exec("GRANT ALL PRIVILEGES ON `$onion`.* TO '$onion.onion'@'%';");
		$db->exec('FLUSH PRIVILEGES;');
		$stmt=$db->prepare('INSERT INTO new_account (onion, password) VALUES (?, ?);');
		$stmt->execute([$onion, get_system_hash($_POST['pass'])]);
		if(EMAIL_TO!==''){
			$title="A new hidden service $onion has been created";
			$msg="A new hidden service http://$onion.onion has been created";
			$headers="From: www-data <www-data>\r\nContent-Type: text/plain; charset=UTF-8\r\n";
			mail(EMAIL_TO, $title, $msg, $headers);
		}
		echo "<p style=\"color:green;\">Your onion domain <a href=\"http://$onion.onion\" target=\"_blank\">$onion.onion</a> has successfully been created. Please wait up to one minute until the changes have been processed. You can then login <a href=\"login.php\">here</a>.</p>";
	}
}
echo '<form method="POST" action="register.php"><table>';
echo '<tr><td>Username</td><td><input type="text" name="username" value="';
if(isset($_POST['username'])){
	echo htmlspecialchars($_POST['username']);
}
echo '" required autofocus></td></tr>';
echo '<tr><td>Password</td><td><input type="password" name="pass" required></td></tr>';
echo '<tr><td>Confirm password</td><td><input type="password" name="passconfirm" required></td></tr>';
send_captcha();
if($_SERVER['REQUEST_METHOD']!=='POST' || (isset($_POST['public']) && $_POST['public']==1)){
	$public=' checked';
}else{
	$public='';
}
if(isset($_POST['autoindex']) && $_POST['autoindex']==1){
	$autoindex=' checked';
}else{
	$autoindex='';
}
$nophp='';
$php70='';
$php71='';
$php72='';
if(isset($_POST['php']) && $_POST['php']==0){
	$nophp=' selected';
}elseif(isset($_POST['php']) && $_POST['php']==1){
	$php70=' selected';
}elseif(isset($_POST['php']) && $_POST['php']==2){
	$php71=' selected';
}else{
	$php72=' selected';
}
echo '<tr><td>PHP version</td><td><select name="php"><option value="0"'.$nophp.'>None</option><option value="1" '.$php70.'>PHP 7.0</option><option value="2"'.$php71.'>PHP 7.1</option><option value="3"'.$php72.'>PHP 7.2</option></select></td></tr>';
echo '<tr><td colspan=2><label><input type="checkbox" name="public" value="1"'.$public.'>Publish site on list of hosted sites</label></td></tr>';
echo '<tr><td colspan=2><label><input type="checkbox" name="autoindex" value="1"'.$autoindex.'>Enable autoindex (listing of files)</label></td></tr>';
echo '<tr><td>Custom private key (vanity url)<br>(eg, yournamehere.onion)</td><td><textarea name="private_key" rows="5" cols="28">';
if(isset($_REQUEST['private_key'])){
	echo htmlspecialchars($_REQUEST['private_key']);
}
echo '</textarea></td></tr>';
echo '<tr><td colspan="2"><input type="submit" value="Register"></td></tr>';
echo '</table></form>';
echo '</body></html>';
