<?php
include('../common.php');
try{
	$db=new PDO('mysql:host=' . DBHOST . ';dbname=' . DBNAME, DBUSER, DBPASS, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_WARNING, PDO::ATTR_PERSISTENT=>PERSISTENT]);
}catch(PDOException $e){
	die('No Connection to MySQL database!');
}
session_start();
$user=check_login();
if(!isset($_REQUEST['type'])){
	$_REQUEST['type']='acc';
}
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
	if(!isset($_POST['pass']) || !password_verify($_POST['pass'], $user['password'])){
		$msg.='<p style="color:red;">Wrong password.</p>';
	}elseif(!isset($_POST['confirm']) || !isset($_POST['newpass']) || $_POST['newpass']!==$_POST['confirm']){
		$msg.='<p style="color:red;">Wrong password.</p>';
	}else{
		if($_REQUEST['type']==='acc'){
			$hash=password_hash($_POST['newpass'], PASSWORD_DEFAULT);
			$stmt=$db->prepare('UPDATE users SET password=? WHERE username=?;');
			$stmt->execute([$hash, $user['username']]);
			$msg.='<p style="color:green;">Successfully changed account password.</p>';
		}elseif($_REQUEST['type']==='sys'){
			$stmt=$db->prepare('INSERT INTO pass_change (onion, password) VALUES (?, ?);');
			$hash=get_system_hash($_POST['newpass']);
			$stmt->execute([$user['onion'], $hash]);
			$msg.='<p style="color:green;">Successfully changed system account password, change will take affect within the next minute.</p>';
		}elseif($_REQUEST['type']==='sql'){
			$stmt=$db->prepare("SET PASSWORD FOR '$user[onion].onion'@'%'=PASSWORD(?);");
			$stmt->execute([$_POST['newpass']]);
			$db->exec('FLUSH PRIVILEGES;');
			$msg.='<p style="color:green;">Successfully changed sql password.</p>';
		}else{
			$msg.='<p style="color:red;">Couldn\'t update password: Unknown reset type.</p>';
		}
	}
}
header('Content-Type: text/html; charset=UTF-8');
echo '<!DOCTYPE html><html><head>';
echo '<title>Wowmee\'s Hosting - Change password</title>';
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
echo '<meta name="author" content="Wowmee TheGreat">';
echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
echo '</head><body>';
echo $msg;
echo '<form method="POST" action="password.php"><table>';
echo '<tr><td>Reset type:</td><td><select name="type">';
echo '<option value="acc"';
if($_REQUEST['type']==='acc'){
	echo ' selected';
}
echo '>Account</option>';
echo '<option value="sys"';
if($_REQUEST['type']==='sys'){
	echo ' selected';
}
echo '>System account</option>';
echo '<option value="sql"';
if($_REQUEST['type']==='sql'){
	echo ' selected';
}
echo '>MySQL</option>';
echo '</select></td></tr>';
echo '<tr><td>Account password:</td><td><input type="password" name="pass" required autofocus></td></tr>';
echo '<tr><td>New password:</td><td><input type="password" name="newpass" required></td></tr>';
echo '<tr><td>Confirm password:</td><td><input type="password" name="confirm" required></td></tr>';
echo '<tr><td colspan="2"><input type="submit" value="Reset"></td></tr>';
echo '</table></form>';
echo '<p><a href="home.php">Go back to dashboard.</a></p>';
echo '</body></html>';
