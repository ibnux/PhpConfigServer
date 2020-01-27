<?php
session_start();
$foldeFig = "config";
$allowExt = array('ini','env','txt');

if(empty($_SESSION['EMAIL'])){
    if (!isset($_SERVER['PHP_AUTH_USER'])) {
        header('WWW-Authenticate: Basic realm="Input email and password"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'You shall not pass';
        exit;
    } else {
        $email = $_SERVER['PHP_AUTH_USER'];
        $pass  = $_SERVER['PHP_AUTH_PW'];
        $host = "mail.carsworld.co.id";
        ini_set('default_socket_timeout',3);
        if($mbox=imap_open('{'.$host.':143/imap/tls/novalidate-cert}',$email,$pass)){
            $_SESSION['EMAIL'] = $email;
            header('location: ./?sukses');
            exit();
        }else{
            header('location: ./?invalid');
            exit();
        }
    }
}


if(isset($_GET['buat']) && !empty($_GET['buat'])){
    $file = preg_replace("/[^A-Za-z0-9 .]/", '', $_GET['buat']);
    $files  = pathinfo($file);
    if(in_array($files['extension'],$allowExt)){
        if(!file_exists("$foldeFig/$file")){
            file_put_contents("$foldeFig/$file",'');
            $msg = "$file telah dibuat";
        }else
            $msg = "File sudah ada";
    }else{
        $msg = "Ekstensi tidak valid. Only: ".implode(",",$allowExt);
    }
}
?><!DOCTYPE html>
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
        <?=__DIR__."/$foldeFig"?>
        </p>
        <hr>
        <?php 
        
        if(isset($_GET['edit']) && !empty($_GET['edit'])){
            $file = preg_replace("/[^A-Za-z0-9 .]/", '', urldecode(base64_decode($_GET['edit'])));
            if(file_exists("$foldeFig/$file")){
                if(isset($_GET['simpan'])){
                    if(!empty($_POST['filename'])){
                        $_POST['filename'] = preg_replace("/[^A-Za-z0-9 .]/", '', $_POST['filename']);
                        if($_POST['filename']!=$file){
                            //ganti nama
                            if(!file_exists("$foldeFig/".$_POST['filename'])){
                                $files  = pathinfo($_POST['filename']);
                                if(in_array($files['extension'],$allowExt)){
                                    if(!file_exists("$foldeFig/".$_POST['filename'])){
                                        unlink("$foldeFig/$file");
                                        $file = $_POST['filename'];
                                    }else
                                        $msg = "File sudah ada";
                                }else{
                                    $msg = "Ekstensi tidak valid. Only: ".implode(",",$allowExt);
                                }
                            }else{
                                $msg = "file name baru sudah ada";
                            }
                        }
                        if(file_put_contents("$foldeFig/$file",$_POST['isi']))
                            $msg = "data disimpan";
                        else
                            $msg = "data gagal disimpan, permission?";
                    }else{
                        $msg = "file dihapus";
                        unlink("$foldeFig/$file");
                    }
                }

                if(!empty($msg)){
                    ?><div class="notification">
                    <b><?=$msg?></b>
                  </div><?php
                } 
                ?>
                <a href="./" class="button is-warning">kembali</a>
                <hr>
                <form method="post" action="./?simpan&edit=<?=$_GET['edit']?>" onsubmit="return confirm('Simpan File?')">
                <div class="columns">
                    <div class="column">
                        <input class="input" type="text" name="filename" value="<?=$file?>">
                        <p class="help">Jangan ada spasi, alphanumeric only, kosongkan jika ingin hapus file</p>
                    </div>
                </div>
                <textarea id="editor" name="isi" class="textarea" rows="50"><?php if(file_exists("$foldeFig/$file"))echo file_get_contents("$foldeFig/$file")?></textarea>
                <button type="submit" class="button is-primary is-fullwidth">Simpan</button>
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
                <?php
            }else{
                echo "no file";
            }
        }else{
            if(!empty($msg)){
                ?><div class="notification">
                <b><?=$msg?></b>
              </div><?php
            } 
        ?>
            <form onsubmit="return confirm('Buat File?')">
            <div class="columns">
                <div class="column is-four-fifths">
                    <input class="input" type="text" name="buat" placeholder="nama_config.env" required>
                    <p class="help">Jangan ada spasi, alphanumeric only</p>
                </div>
                <div class="column">
                    <button type="submit" class="button is-primary is-fullwidth">Buat File</button>
                </div>
            </div>
            </form>
            <hr>
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
                    foreach($dir as $file){
                        if(!is_dir(($file)) && in_array(pathinfo($file)['extension'],$allowExt)){
                            ?>
                            <tr>
                                <td><a href="./?edit=<?= urlencode(base64_encode($file))?>"><?=$file?></td>
                                <td><?=date("d M Y H:i",filemtime("./config/$file"))?></td>
                                <td><?=filesize("./config/$file")?> b</td>
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