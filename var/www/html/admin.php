<?php
include('../common.php');
try{
	$db=new PDO('mysql:host=' . DBHOST . ';dbname=' . DBNAME, DBUSER, DBPASS, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_WARNING, PDO::ATTR_PERSISTENT=>PERSISTENT]);
}catch(PDOException $e){
	die('No Connection to MySQL database!');
}
header('Content-Type: text/html; charset=UTF-8');
session_start(['name'=>'hosting_admin']);
if($_SERVER['REQUEST_METHOD']==='HEAD'){
	exit; // headers sent, no further processing needed
}
echo '<!DOCTYPE html><html><head>';
echo '<title>Wowmee\'s Hosting - Login</title>';
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
echo '<meta name="author" content="Wowmee">';
echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
echo '</head><body>';
echo '<h1>Hosting - Admin panel</h1>';
$error=false;
if($_SERVER['REQUEST_METHOD']==='POST' && isSet($_POST['pass']) && $_POST['pass']===ADMIN_PASSWORD){
	if(!($error=check_captcha_error())){
		$_SESSION['logged_in']=true;
	}
}
if(empty($_SESSION['logged_in'])){
	echo "<form action=\"$_SERVER[SCRIPT_NAME]\" method=\"POST\"><table>";
	echo "<tr><td>Password </td><td><input type=\"password\" name=\"pass\" size=\"30\" required autofocus></td></tr>";
	send_captcha();
	echo "<tr><td colspan=\"2\"><input type=\"submit\" name=\"action\" value=\"Login\"></td></tr>";
	echo '</table></form>';
	if($error){
		echo "<p style=\"color:red;\">$error</p>";
	}elseif(isSet($_POST['pass'])){
		echo "<p style=\"color:red;\">Wrong password!</p>";
	}
	echo '<p>If you disabled cookies, please re-enable them. You can\'t log in without!</p>';
}else{
	echo '<p>';
	if(REQUIRE_APPROVAL){
		$stmt=$db->query('SELECT COUNT(*) FROM new_account WHERE approved=0;');
		$cnt=$stmt->fetch(PDO::FETCH_NUM)[0];
		echo "<a href=\"$_SERVER[SCRIPT_NAME]?action=approve\">Approve pending sites ($cnt)</a> | ";
	}
	echo "<a href=\"$_SERVER[SCRIPT_NAME]?action=list\">List of hidden hosted sites</a> | <a href=\"$_SERVER[SCRIPT_NAME]?action=list2\">List of Normal hosted sites</a> | <a href=\"$_SERVER[SCRIPT_NAME]?action=delete\">Delete accounts</a> | <a href=\"$_SERVER[SCRIPT_NAME]?action=logout\">Logout</a></p>";
	if(empty($_REQUEST['action']) || $_REQUEST['action']==='login'){
		echo '<p>Welcome to the admin panel!</p>';
	}elseif($_REQUEST['action']==='logout'){
		session_destroy();
		header("Location: $_SERVER[SCRIPT_NAME]");
		exit;
	}elseif($_REQUEST['action']==='list'){
		echo '<table border="1">';
		echo '<tr><td>Onion link</td></tr>';
		$stmt=$db->query('SELECT onion FROM users WHERE public=0 ORDER BY onion;');
		while($tmp=$stmt->fetch(PDO::FETCH_NUM)){
			echo "<tr><td><a href=\"http://$tmp[0].onion\" target=\"_blank\">$tmp[0].onion</a></td></tr>";
		}
		echo '</table>';
	}elseif($_REQUEST['action']==='list2'){
		echo '<table border="1">';
		echo '<tr><td>Onion link</td></tr>';
		$stmt=$db->query('SELECT onion FROM users WHERE public=1 ORDER BY onion;');
		while($tmp=$stmt->fetch(PDO::FETCH_NUM)){
			echo "<tr><td><a href=\"http://$tmp[0].onion\" target=\"_blank\">$tmp[0].onion</a></td></tr>";
		}
		echo '</table>';	
	}elseif($_REQUEST['action']==='approve'){
		if(!empty($_POST['onion'])){
			$stmt=$db->prepare('UPDATE new_account SET approved=1 WHERE onion=?;');
			$stmt->execute([$_POST['onion']]);
			echo '<p style="color:green;">Successfully approved</p>';
		}
		echo '<table border="1">';
		echo '<tr><td>Username</td><td>Onion address</td><td>Action</td></tr>';
		$stmt=$db->query('SELECT users.username, users.onion FROM users INNER JOIN new_account ON (users.onion=new_account.onion) WHERE new_account.approved=0 ORDER BY users.username;');
		while($tmp=$stmt->fetch(PDO::FETCH_NUM)){
			echo "<form action=\"$_SERVER[SCRIPT_NAME]\" method=\"POST\"><input type=\"hidden\" name=\"onion\" value=\"$tmp[1]\"><tr><td>$tmp[0]</td><td>$tmp[1].onion</td><td><input type=\"submit\" name=\"action\" value=\"approve\"><input type=\"submit\" name=\"action\" value=\"delete\"></td></tr></form>";
		}
		echo '</table>';
	}elseif($_REQUEST['action']==='delete'){
		echo '<p>Delete accouts:</p>';
		echo "<form action=\"$_SERVER[SCRIPT_NAME]\" method=\"POST\">";
		echo '<p>Onion address: <input type="text" name="onion" size="30" value="';
		if(isSet($_POST['onion'])){
			echo htmlspecialchars($_POST['onion']);
		}
		echo '" required autofocus></p>';
		echo '<input type="submit" name="action" value="delete"></form><br>';
		if(!empty($_POST['onion'])){
			if(preg_match('~^([a-z2-7]{16})(\.onion)?$~', $_POST['onion'], $match)){
				$stmt=$db->prepare('SELECT null FROM users WHERE onion=?;');
				$stmt->execute([$match[1]]);
				if($stmt->fetch(PDO::FETCH_NUM)){
					$stmt=$db->prepare('UPDATE users SET todelete=1 WHERE onion=?;');
					$stmt->execute([$match[1]]);
					echo "<p style=\"color:green;\">Successfully queued for deletion!</p>";
				}else{
					echo "<p style=\"color:red;\">Onion address not hosted by us!</p>";
				}
			}else{
				echo "<p style=\"color:red;\">Invalid onion address!</p>";
			}
		}
	}
}
echo '</body></html>';
