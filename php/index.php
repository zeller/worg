<?php
$root = "/var/www/worg/php";
$view = $_GET['view'];
if($view == "") {

echo "<link rel='stylesheet' type='text/css' href='http://www.ics.uci.edu/~zellerm/tips/html/style.css'/>";
echo "<h1 class=\"title\">Index</h1>";

function traverseDirTree($base,$fileFunc,$dirFunc=null,$afterDirFunc=null){
  $subdirectories=@opendir($base);
  $subdirectorylist = array();
  while ($file = @readdir($subdirectories)) {
  $subdirectorylist[] = $file;
  }

  sort($subdirectorylist);

  foreach ($subdirectorylist as $subdirectory) {
    $path=$base.$subdirectory;
    if (is_file($path)){
      if ($fileFunc!==null) $fileFunc($path);
    }else{
      if ($dirFunc!==null) $dirFunc($path);
      if (($subdirectory!='.') && ($subdirectory!='..') && ($subdirectory!='.git')){
        traverseDirTree($path.'/',$fileFunc,$dirFunc,$afterDirFunc);
      }
      if ($afterDirFunc!==null) $afterDirFunc($path);
    }
  }
}

function outputPath($path){
  global $root;
  $base = basename($path);
  if((($base != "." && $base != ".." && $base != ".git") && !is_file($path)) || preg_match('/.org$/', $path)) {
    $level=substr_count($path,'/');       
    for ($i=1;$i<$level;$i++) echo '   ';
    if (!is_file($path)) echo basename($path);
    else {
      $path = preg_replace("@$root/org/@", "", "/" . $path);
      $path = preg_replace('@\.org$@', "", $path);
      echo "<a href='$path.html'>" . basename($path) . "</a>";
    }	
    echo "\n";
  }
}
echo '<pre>';
traverseDirTree("$root/org/",'outputpath','outputpath');
echo '</pre>';
exit();
}
?>
<?php
if(ereg('[^A-Za-z0-9_/]', $view)) die("Invalid page requested.");
?>
<?php
  $auth = 0;

  if($_POST && $_POST['user'] != "" && $_POST['pass'] != "") {

     $db = pg_connect("host=localhost port=5432 dbname=worg user=worg password=worg") or die("Couldn't connect to the database.");

     // Add slashes to the username, and make a md5 checksum of the password.
     $_POST['user'] = addslashes($_POST['user']);
     $_POST['pass'] = md5($_POST['pass']);

     $ret = pg_query($db, "SELECT count(id) FROM users WHERE digest='$_POST[pass]' AND author='$_POST[user]'") or die("Couldn't query the user-database.");
     $num = pg_fetch_array($ret, NULL, PGSQL_NUM);   
     if ($num[0]) {
     
     // Start the login session
     session_start();

     // We've already added slashes and MD5'd the password
     $_SESSION['user'] = $_POST['user'];
     $_SESSION['pass'] = $_POST['pass'];
     $auth = 1;
     }
     else echo "<center><font color='red'>Login failed</font></center>";
  }
  else if($_GET['mode'] == "logout") {
     session_start();
     session_destroy(); 
  }
  else {
  session_start();
  # always check if user is authenticated
  if ($_SESSION['user'] && $_SESSION['pass']) {
    
    $db = pg_connect("host=localhost port=5432 dbname=worg user=worg password=worg") or die("Couldn't connect to the database.");

    $ret = pg_query($db, "SELECT count(id) FROM users WHERE digest='$_SESSION[pass]' AND author='$_SESSION[user]'") or die("Couldn't query the user-database.");
    $num = pg_fetch_array($ret, NULL, PGSQL_NUM);
    if ($num[0]) $auth = 1;
  }
  }

?>
<?php
if($_POST && $_POST['content'] != "" && $auth) {
  $content = $_POST['content'];
  $file = "org/$view.org";
  $fh = fopen($file, 'w');
  fwrite($fh, stripslashes($content));
  fclose($fh);
  `emacs --batch --load org.elc --load cl.elc --load ess-site.elc --eval '(setq org-export-with-LaTeX-fragments t)' --eval '(setq ess-ask-for-ess-directory nil)' --eval '(setq ess-directory "$root/org/out/")' --visit $root/org/$view.org --funcall org-mode --funcall org-export-as-html 2>&1`;
}
?>

<link rel='stylesheet' type='text/css' href='http://www.ics.uci.edu/~zellerm/tips/html/style.css'/>
<div id='header' style='padding-bottom:5px; margin-left:auto; position:relative'>
<?php
  echo "<a href='/index.html'>Index</a>&nbsp;&nbsp;";
  if($_GET['mode'] != "" && $_GET['mode'] != "logout") {
  echo "<a href='" . preg_replace('/.html&mode=[a-zA-Z]+/', '', @basename($_SERVER['REQUEST_URI'])) . ".html'>Cancel</a>";
  if($_GET['mode'] == "login") {
  echo "<span style='position:absolute; right:0'><form id='login' method='post' action='" . basename($_SERVER['REQUEST_URI'], ".html&mode=login") . ".html'><input type='text' name='user'>&nbsp;<input type='password' name='pass'>&nbsp;<input type='submit' value='Submit'></form></span>";
  }
  }
  else if ($auth) {
  echo "<a href='" . basename($_SERVER['REQUEST_URI'], ".html") . ".html&mode=edit'>Edit</a>";
  echo "<span style='position:absolute; right:0;'><a href='" . preg_replace('/.html(&mode=[a-zA-Z]+)?/', '', @basename($_SERVER['REQUEST_URI'])) . ".html&mode=logout'>Logout</a></span>";
  }
  else {
  echo "<span style='position:absolute; right:0;'><a href='" . preg_replace('/.html(&mode=[a-zA-Z]+)?/', '', @basename($_SERVER['REQUEST_URI'])) . ".html&mode=login'>Login</a></span>";
  }
?>
</div>

<?php
if($_GET['mode'] == 'edit') {
if($auth) {
echo "<form method='post' action='" . basename($_SERVER['REQUEST_URI'], ".html&mode=edit") . ".html'>";
echo "<textarea name='content' style='width:100%;height:50%' type='textbox'>";
@include("$root/org/$view.org");
echo "</textarea>";
echo "<br><input type='submit' value='Save'/>";
echo "</form>";
}
else echo "Insufficient privledges to edit";
}
?>

<?php 
@readfile("$root/org/$view.html");
?>