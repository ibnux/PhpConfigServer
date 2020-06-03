<?php
session_start();
/**
 * Created by Ibnu Maksum 2020
 *
 * Read README.md before using this code
 *
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE', which is part of this source code package.
 */




// CONFIGURATION

$mailServer = "mail.carsworld.co.id";
$allowedEmails = ['ibnumaksum@carsworld.id'];

$foldeFig = "config";
$allowExt = array('ini', 'env', 'txt');

if (!empty($_SERVER['PHP_AUTH_USER']) && in_array($_SERVER['PHP_AUTH_USER'], $allowedEmails)) {
    $email = $_SERVER['PHP_AUTH_USER'];
    $pass  = $_SERVER['PHP_AUTH_PW'];
    ini_set('default_socket_timeout', 3);
    if ($mbox = @imap_open('{' . $mailServer . ':143/imap/tls/novalidate-cert}', $email, $pass)) {
        $_SESSION['EMAIL'] = $email;
    } else {
        unset($_SERVER['PHP_AUTH_USER']);
        unset($_SESSION['EMAIL']);
        header('location: ./?invalid&email');
        die();
    }
}

//END OF CONFIGURATION
if (empty($_SESSION['EMAIL'])) {
    header('WWW-Authenticate: Basic realm="Input email and password"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'You shall not pass';
    die();
}


if (isset($_GET['buat']) && !empty($_GET['buat'])) {
    $file = preg_replace("/[^A-Za-z0-9_.]/", '', $_GET['buat']);
    $files  = pathinfo($file);
    if (in_array($files['extension'], $allowExt)) {
        if (!file_exists("$foldeFig/$file")) {
            file_put_contents("$foldeFig/$file", '');
            $msg = "$file telah dibuat";
        } else
            $msg = "File sudah ada";
    } else {
        $msg = "Ekstensi tidak valid. Only: " . implode(",", $allowExt);
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Config File Editor</title>
    <link rel="stylesheet" href="css/bulma.min.css">

</head>

<body>
    <section class="section">
        <div class="container">
            <h1 class="title">
                <a href="./">Configuration File</a>
            </h1>
            <p class="subtitle">
                <?= __DIR__ . "/$foldeFig" ?>
            </p>
            <hr>
            <?php

            if (isset($_GET['edit']) && !empty($_GET['edit'])) {
                $file = preg_replace("/[^A-Za-z0-9_.]/", '', urldecode(base64_decode($_GET['edit'])));
                if (file_exists("$foldeFig/$file")) {
                    if (isset($_GET['simpan']) && !empty($_POST)) {
                        if (!empty($_POST['filename'])) {
                            $_POST['filename'] = preg_replace("/[^A-Za-z0-9 .]/", '', $_POST['filename']);
                            if ($_POST['filename'] != $file) {
                                //ganti nama
                                if (!file_exists("$foldeFig/" . $_POST['filename'])) {
                                    $files  = pathinfo($_POST['filename']);
                                    if (in_array($files['extension'], $allowExt)) {
                                        if (!file_exists("$foldeFig/" . $_POST['filename'])) {
                                            unlink("$foldeFig/$file");
                                            $file = $_POST['filename'];
                                        } else
                                            $msg = "File Exists";
                                    } else {
                                        $msg = "File extention not allowed. Only: " . implode(",", $allowExt);
                                    }
                                } else {
                                    $msg = "File exists.";
                                }
                            }
                            $data = file_get_contents("$foldeFig/$file");
                            $md5o = md5($data);
                            $md5n = md5($_POST['isi']);
                            if (file_put_contents("$foldeFig/$file", $_POST['isi'])){
                                $msg = "File Saved";
                                if($md5o!=$md5n && !empty($data)){
                                    if(!file_exists("history/".$file)) mkdir("history/".$file);
                                    file_put_contents("history/".$file."/".str_replace("@","_at_",$_SESSION['EMAIL'])."_".date("Y-m-d_h.i.s").".txt",$data);
                                }
                            }else
                                $msg = "Failed to save file, Write permission allowed?";
                        } else {
                            if(!empty($_POST)){
                                $msg = "file dihapus";
                                unlink("$foldeFig/$file");
                            }
                        }
                    }

                    if (!empty($msg)) {
            ?><div class="notification">
                            <b><?= $msg ?></b>
                        </div><?php
                            }
                                ?>
                    <a href="./" class="button is-warning">Back</a>
                    <hr>
                    <form method="post" action="./?simpan&edit=<?= $_GET['edit'] ?>" onsubmit="return confirm('Simpan File?')">
                        <div class="columns">
                            <div class="column">
                                <input class="input" type="text" name="filename" value="<?= $file ?>">
                                <p class="help">Don't use space, alphanumeric only, Empty filename to delete file</p>
                            </div>
                        </div>
                        <textarea id="editor" name="isi" class="textarea" rows="50"><?php if (file_exists("$foldeFig/$file")) echo file_get_contents("$foldeFig/$file") ?></textarea>
                        <button type="submit" class="button is-primary is-fullwidth">Save</button>
                        <!-- Create a simple CodeMirror instance -->
                        <link rel="stylesheet" href="css/codemirror.css">
                        <script src="js/codemirror.js"></script>
                        <script>
                            var editor = CodeMirror.fromTextArea(document.getElementById("editor"), {
                                lineNumbers: true
                            });
                            editor.setSize(null, 500);
                        </script>
                    </form>
                    <hr>
                    <b>Riwayat</b>
                    <ul>
                    <?php
                    $history = "history/$file/";
                    if(file_exists($history)){
                        $files = scandir($history);
                        $files = array_reverse($files);
                        foreach($files as $fl){
                            if(!is_dir("$history$fl"))
                                echo "<li><a href=\"$history$fl\" target=\"_blank\">$fl</li>\n";
                        }
                    }
                    ?></ul>
                <?php
                } else {
                    echo "no file";
                }
            } else if(isset($_GET['summary'])) {
                echo '<a class="button is-warning is-small" href="./">back</a><br>&nbsp;<br>';
                //changes all
                if(!empty($_POST['barisAsli']) && !empty($_POST['barisEdit']) && !empty($_POST['files'])){
                    $files = explode(",",$_POST['files']);
                    foreach($files as $file){
                        $datalama = file_get_contents("$foldeFig/$file");
                        $md5o = md5($datalama);
                        $data = str_replace($_POST['barisAsli'],$_POST['barisEdit'],$datalama);
                        $md5n = md5($data);
                        if($md5o==$md5n){
                            echo "<a href=\"./?summary#".md5($_POST['barisEdit'])."\" class=\"tag is-warning is-light\">$file no changes</a><br>";
                        }else{
                            if (file_put_contents("$foldeFig/$file", $data)){
                                echo "<a href=\"./?summary#".md5($_POST['barisEdit'])."\" class=\"tag is-link is-light\">$file changes.</a><br>";
                                if($md5o!=$md5n && !empty($datalama)){
                                    if(!file_exists("history/".$file)) mkdir("history/".$file);
                                    file_put_contents("history/".$file."/".str_replace("@","_at_",$_SESSION['EMAIL'])."_".date("Y-m-d_h.i.s").".txt",$datalama);
                                }
                            }else
                                echo "<a href=\"./?summary#".md5($_POST['barisEdit'])."\" class=\"tag is-danger is-light\">Failed to save $file , Write permission allowed?</a><br>";
                        }
                    }
                    echo "<br>";
                }

                $files = scandir("$foldeFig/");
                $result = array(); // ["md5":{"baris":"","files":[]}]
                foreach($files as $file){
                    if(!is_dir("$foldeFig/$file") && $file!='index.html'){
                        $bariss = explode("\n", str_replace("\r","",file_get_contents("$foldeFig/$file")));
                        foreach($bariss as $baris){
                            $baris = trim($baris);
                            if(!empty($baris)){
                                $md5 = md5($baris);
                                $result[$md5]['baris'] = $baris;
                                $result[$md5]['files'][] = $file;
                            }
                        }
                    }
                }
                ?><table class="table is-bordered is-narrow is-hoverable is-fullwidth">
                    <thead>
                        <tr>
                            <th>Value <small>hanya bisa save perbaris</small></th>
                            <th>Save</th>
                        </tr>
                    </thead>
                    <tbody>
                <?php
                foreach($result as $key => $baris){
                    echo "<form method='POST' action=\"./?summary\"><tr>\n";
                    echo "<td id=\"$key\"><input class='input is-success' type='text' name='barisEdit' value=\"".htmlentities($baris['baris'])."\"></td><td width=\"80\"><button class=\"button is-link\" type=\"submit\" onsubmit=\"return confirm('Ubah File ".implode(',',$baris['files'])."?')\">save</button></td></tr>";
                    echo "<input class='input' type='hidden' name='barisAsli' readonly value=\"".htmlentities($baris['baris'])."\"><input type='hidden' name='files' value=\"".implode(',',$baris['files'])."\"></form>\n\n";
                    echo "<tr><td colspan='4'><span class=\"tag is-link\">".implode('</span> <span class="tag is-link">',$baris['files'])."</span></td></tr>\n";
                    echo "<tr><td colspan='4' style=\"background-color:#f5f5f5\"></td></tr>\n";
                }?>
                    </tbody>
                </table><?php
            } else {
                if (!empty($msg)) {
                ?><div class="notification">
                        <b><?= $msg ?></b>
                    </div><?php
                        }
                            ?>
                <form onsubmit="return confirm('Buat File?')">
                    <div class="columns">
                        <div class="column is-four-fifths">
                            <input class="input" type="text" name="buat" placeholder="nama_config.env" required>
                            <p class="help">No space, alphanumeric only</p>
                        </div>
                        <div class="column">
                            <button type="submit" class="button is-primary is-fullwidth">Buat File</button>
                        </div>
                    </div>
                </form>
                <hr>
                <a class="button is-link is-small" href="./?summary">summary</a>
                <table class="table is-fullwidth is-striped is-hoverable">
                    <thead>
                        <tr>
                            <th><abbr title="File Config">Filename</abbr></th>
                            <th>Last Update</th>
                            <th>File Size</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $dir = scandir($foldeFig);
                        foreach ($dir as $file) {
                            if (!is_dir(($file)) && in_array(pathinfo($file)['extension'], $allowExt)) {
                        ?>
                                <tr>
                                    <td><a href="./?edit=<?= urlencode(base64_encode($file)) ?>"><?= $file ?></td>
                                    <td><?= date("d M Y H:i", filemtime("./config/$file")) ?></td>
                                    <td><?= filesize("./config/$file") ?> b</td>
                                </tr>
                        <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>

    </section>

</body>

</html>