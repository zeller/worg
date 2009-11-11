<?php
// custom set variables

// set the path to php directory
// where org symbolic link resides
// i.e. $root = "/var/www/worg/php";
//* Paths
$root = "/var/www/worg/php";
$style_path = "http://www.ics.uci.edu/~zellerm/tips/html/style.css";
//*

//* Features

//** git
$git_enabled = true;

//** gitweb
$gitweb_enabled = true;
$gitweb_port = 8081;

//** comments
$comments_enabled = false; // TODO

//** inline edit
$edit_enabled = true;
$pg_connection_string = "host=localhost port=5432 dbname=worg user=worg password=worg";
//*

$side_bar = true; // TODO
?>
<?php
function writeHeader() {
  global $style_path, $side_bar;
  echo "<html><head>";
  echo "<link rel='stylesheet' type='text/css' href='$style_path'/>";
  echo "</head><body style='margin:0; background:#f30'><table width='100%' cellspacing='0' width='100%' border='0' summary='' cellpadding='0' style='border:none;background:none' id='bodyTable'>";
  echo "<tr valign='top'><td id='leftDiv' nowrap width='20'></td><td id='middleDiv' valign='top'><div id='bodyDiv'><div id='bodyContent'>";
  echo "<table><tr><td colspan=2 style='height:50px; vertical-align:middle; background:#000'>&nbsp;<span style='font-size:30; color:white'>Michael Zeller</span>.com</td></tr><tr><td id='content' style='width:100%'>";
}
function writeFooter() {
  global $side_bar;
  if ($side_bar) {
    echo "</td><td id='sidebar' style='min-width:125px; max-width:125px; background:#333; color:white'>";
    echo "Search this Site<br>";
    echo "Subscribe<br>";
    echo "Recent Changes<br>";
    echo "</td>";
  }
  else {
    echo "</td><td></td>";
  }
  echo "</tr><tr><td colspan=2 style='height:25px; text-align:center; background:#000; vertical-align:middle'>Copyleft - <a href='http://github.com/zeller'>Michael Zeller</a> - 2009</td></tr></table>";
  echo "<span style='float:left'>Powered by Emacs</span>";
  echo "</div></div></td><td id='rightDiv' nowrap width='20'></td></tr></table></body></html>";
}
?>
<?php
$view = $_GET['view'];
if($view == "admin") {
  writeHeader();
  echo "<h1 class=\"title\">Admin</h1>";
  function makeButton($text, $linebreak=FALSE) {
    echo "<input type='button' id='$text' value='$text'/>";
    if ($linebreak) echo "<br/>";
  }
  function makeTextbox($id, $linebreak=FALSE, $type="text") {
    echo "<input type='$type' id='$text'/>";
    if ($linebreak) echo "<br/>";
  }
  echo "<table style='border:none'><tr><td align='right'>";
  echo "<table style='border:none'><tr><td align='right'>";
  echo "Author: </td><td>"; makeTextbox("username", TRUE);
  echo "</td></tr><tr><td>";
  echo "Password: </td><td>"; makeTextbox("password", TRUE, $type="password");
  echo "</td></tr></table>";
  echo "</td><td>";
  echo "<table style='border:none'><tr><td>";
  makeButton("Create Author", TRUE);
  echo "</td></tr><tr><td>";
  makeButton("Delete Author", TRUE);
  echo "</td></tr><tr><td>";
  makeButton("Change Password", TRUE);
  echo "</td></tr></table>";
  echo "</td></tr></table>";
  writeFooter();
  exit();
}
if($view == "") {
  writeHeader();
  echo "<h1 class=\"title\">Index</h1>";
  $output = "";
  $empty = array(true);
  function traverseDirTree($base,$fileFunc,$dirFunc=null,$afterDirFunc=null) {
    
    global $empty;
    
    $subdirectories=opendir($base);
    $subfilelist = array();
    $subdirectorylist = array();
    while ($file = readdir($subdirectories)) {
      if (is_file($base . $file))
        $subfilelist[] = $file;
      else
        $subdirectorylist[] = $file;
    }
  
    rsort($subfilelist);
    rsort($subdirectorylist);

    foreach (array_merge($subfilelist, $subdirectorylist) as $subdirectory) {
      $path=$base.$subdirectory;
      if (is_file($path)){
        if ($fileFunc!==null) $fileFunc($path);
      }else{
        if ($dirFunc!==null) $dirFunc($path);
        if (($subdirectory!='.') && ($subdirectory!='..') && ($subdirectory!='.git')){
          array_push($empty, true);
          traverseDirTree($path.'/',$fileFunc,$dirFunc,$afterDirFunc);
        }
      }
    }

    if (!is_file($base) && $afterDirFunc!==null) $afterDirFunc($base, $subfilelist);
  }

  function outputfile($path){

    global $output, $empty, $root;
    $base = basename($path);
    if($base != "index.html" && preg_match('/.html$/', $path)) {
      $level=substr_count($path,'/');	     
      //$path = preg_replace('/.html$/', "", $path);
      $path = preg_replace("@$root/org/@", "", "/" . $path);
      $output = "<a href='$path' style='font-weight:normal;'>" . basename($path) . "</a>\n" . $output;
      for ($i=1;$i<$level;$i++) $output = '   ' . $output;
      array_pop($empty); 
      array_push($empty, false);
    }
    else if($base == "index.html") {
      array_pop($empty); 
      array_push($empty, false);
    }
  }

  function afterdir($path, $subdirectorylist) {
    global $output, $empty, $root;
    $isempty = array_pop($empty);
    $base = basename($path);
    if (($path != "$root/org/" && $base != "." && $base != ".." && $base != ".git" && $base != "ltxpng" && $base != "out" && $base != "files") && !$isempty) {
      $level=substr_count($path,'/');	 
      if (!in_array("index.html", array_map("basename", $subdirectorylist), true)) {
        $output = "<span style='font-weight:normal;'>" . basename($path) . "</span>\n" . $output;
        for ($i=2;$i<$level;$i++) $output = '   ' . $output;
      }
      else {
        $path = preg_replace("@$root/org/@", "", $path);
        $output = "<a href='${path}index.html'>" . basename($path) . "</a>\n" . $output;
        for ($i=2;$i<$level;$i++) $output = '   ' . $output;
      }
    }
  }

  echo "<pre style='border:none'>";
  traverseDirTree("$root/org/",'outputfile',NULL, 'afterdir');
  echo $output;
  echo '</pre>';
  writeFooter();
  exit();
}
?>
<?php
if(ereg('[^A-Za-z0-9_/]', $view)) die("Invalid page requested: " . $view);
?>
<?php
  $auth = 0;

  if($_POST && $_POST['user'] != "" && $_POST['pass'] != "") {
    
    $db = pg_connect($pg_connection_string) or die("Couldn't connect to the database.");

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
    $db = pg_connect($pg_connection_string) or die("Couldn't connect to the database.");
    
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
  $dir = "$root/" . dirname($file);
  @mkdir($dir, 0777, TRUE);
  $fh = fopen($file, 'w');
  fwrite($fh, stripslashes($content));
  fclose($fh);
  chdir($dir);
  if ($git_enabled) {
    $memo = preg_replace("@'@", "'\"'\"'", stripslashes($_POST['memo']));
    `git add $root/../org/$view.org 2>&1`;
    `git commit '$root/../$file' -m '$memo' 2>&1`;
  }
  `emacs --batch --load org.elc --load cl.elc --load ess-site.elc --eval '(setq org-export-with-LaTeX-fragments t)' --eval '(setq ess-ask-for-ess-directory nil)' --eval '(setq ess-directory "$root/org/out/")' --visit $root/org/$view.org --funcall org-mode --funcall org-export-as-html 2>&1`;
  chdir("$root");
}
writeHeader();
?>
<div id='header' style='padding-bottom:5px; margin-left:auto; position:relative'>
<?php
  echo "<a href='/index.html'>Index</a>&nbsp;&nbsp;";
  if ($gitweb_enabled) echo "<a href='http://" . $_SERVER['SERVER_NAME'] . ":" . $gitweb_port . "/?p=.git;a=history;f=$view.org;hb=HEAD'>History</a>&nbsp;&nbsp;";
  if($_GET['mode'] != "" && $_GET['mode'] != "logout" && $edit_enabled) {
    echo "<a href='" . preg_replace('/.html&mode=[a-zA-Z]+/', '', @basename($_SERVER['REQUEST_URI'])) . ".html'>Cancel</a>";
    if($_GET['mode'] == "login") {
      echo "<span style='position:absolute; right:0'><form id='login' method='post' action='" . basename($_SERVER['REQUEST_URI'], ".html&mode=login") . ".html'><input type='text' name='user'>&nbsp;<input type='password' name='pass'>&nbsp;<input type='submit' value='Submit'></form></span>";
    }
  }
  else {
    $tangle_file = preg_replace('/.html(&mode=[a-zA-Z]+)?/', '', preg_replace('@http://home.michaelzeller.com/@', '', $_SERVER['REQUEST_URI'])) . ".R";
    if (file_exists("$root/org/$tangle_file")) echo "<a href='$tangle_file'>Tangle</a>&nbsp;&nbsp";
    if ($edit_enabled) {
      if ($auth) {
        echo "<a href='" . basename($_SERVER['REQUEST_URI'], ".html") . ".html&mode=edit'>Edit</a>";
        echo "<span style='position:absolute; right:0;'><a href='" . preg_replace('/.html(&mode=[a-zA-Z]+)?/', '', @basename($_SERVER['REQUEST_URI'])) . ".html&mode=logout'>Logout</a></span>";
      }
      else {
        echo "<span style='position:absolute; right:0;'><a href='" . preg_replace('/.html(&mode=[a-zA-Z]+)?/', '', @basename($_SERVER['REQUEST_URI'])) . ".html&mode=login'>Login</a></span>";
      }
    }
  }
?>
</div>

<?php
if($_GET['mode'] == 'edit' && $edit_enabled) {
if($auth) {
echo "<form method='post' action='" . basename($_SERVER['REQUEST_URI'], ".html&mode=edit") . ".html'>";
echo "<textarea name='content' style='width:100%;height:300px' type='textbox'>";
@include("$root/org/$view.org");
echo "</textarea>";
if ($git_enabled) echo "<br/><input type='text' name='memo' onfocus='this.select();' style='width:100%' value='Short description'/>";
echo "<br/><input type='submit' value='Save'/>";
echo "</form>";
}
else echo "Insufficient privledges to edit";
}
?>

<?php 
@readfile("org/$view.html");
?>

<?php
writeFooter();
?>