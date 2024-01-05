<?php
session_start();

?>
<html>
  <head>
    <title>ROBOT PHP ML</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
      body { font-family: Arial; text-align: center; margin:0px auto; padding-top: 30px;}
      table { margin-left: auto; margin-right: auto; }
      td { padding: 8 px; }
      .button {
      width:150px;
        background-color: #2f4468;
        border: none;
        color: white;
        padding: 10px 20px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 18px;
        margin: 6px 3px;
        cursor: pointer;
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        -khtml-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        -webkit-tap-highlight-color: rgba(0,0,0,0);
      }
    </style>
  </head>
  <body>

    <?php
    // for security user must login
    $password = '123456';
    // login
    if (isset($_POST['password']))
    {
        if ($_POST['password'] == $password)
        {
            $_SESSION['logged'] = true;
        }
    }
    if (!isset($_SESSION['logged']))
    {
        echo "<form method='post'>";
        echo "<input type='password' name='password'>";
        echo "<input type='submit' value='login'>";
        echo "</form>";
        exit;
    }
    ?>


    <table>
        <tr>
            <td align="center">
                    <div style="">
                        Live:<br />
                    <img src="" id="live_preview">
                </div>

                    <table>
                      <tr><td colspan="3" align="center"><button class="button" onmousedown="sendCommand('forward');" ontouchstart="sendCommand('forward');" onmouseup="sendCommand('stop');" ontouchend="sendCommand('stop');">Forward</button></td></tr>
                      <tr><td align="center"><button class="button" onmousedown="sendCommand('left');" ontouchstart="sendCommand('left');" onmouseup="sendCommand('stop');" ontouchend="sendCommand('stop');">Left</button></td><td align="center"><button class="button" onmousedown="sendCommand('stop');" ontouchstart="sendCommand('stop');">Stop</button></td><td align="center"><button class="button" onmousedown="sendCommand('right');" ontouchstart="sendCommand('right');" onmouseup="sendCommand('stop');" ontouchend="sendCommand('stop');">Right</button></td></tr>
                      <tr><td colspan="3" align="center"><button class="button" onmousedown="sendCommand('backward');" ontouchstart="sendCommand('backward');" onmouseup="sendCommand('stop');" ontouchend="sendCommand('stop');">Backward</button></td></tr>
                    </table>

                <h3>Machine Learning labeling</h3>
                    <table>
                      <tr><td colspan="3" align="center"><a href="index.php?save_image&type=forward" class="button">Forward</a></td></tr>
                      <tr><td align="center"><a href="index.php?save_image&type=left" class="button">Left</a></td>
                      <td align="center"></td>
                      <td align="center"><a href="index.php?save_image&type=right" class="button">Right</a>
                      </td></tr>
                      <tr><td colspan="3" align="center"><a href="index.php?save_image&type=backward" class="button">Backward</a></td></tr>                   
                    </table>
            </td>
            <td valign="top">
                ML:<br />
                <img src="" id="live_ml" style="width:120px">
            </td>
            <td valign="top" align="center">
                Train data:<br />
        
            <?php
              $folder = 'data_labels';
              if (isset($_GET['save_image']) AND isset($_GET['type']))
              {
                    // make image for ML
                    $im = imagecreatefromjpeg('img.jpg');
                    $im = imagecrop($im, ['x' => 100, 'y' => 125, 'width' => 230, 'height' => 230]);
                    $im = imagerotate($im, -90, 0);
                    imagefilter($im, IMG_FILTER_GRAYSCALE);
                    $temp = imagecreatetruecolor(32, 32);
                    imagecopyresampled($temp, $im, 0, 0, 0, 0, 32, 32, 230, 230);
                    imagejpeg($temp, 'data_labels/'.time().'_'.$_GET['type'].'.jpg');
              }

              //delete
              if (isset($_GET['delete']))
              {
                  unlink($folder.'/'.$_GET['delete']);
              }

            // send command
            if (isset($_GET['send_command']))
            {
                $fp = fopen('command.dat', 'w');
                fwrite($fp, $_GET['send_command']);
                fclose($fp);
                exit;
            }

              // scandir
              $files = scandir($folder);
              $files = array_diff($files, array('.', '..'));


              // display html + delete
              foreach($files as $file)
              {
                  echo "<div style='display:inline-block;float:left;text-align: center;margin:5px'>";
                  echo "<img src='".$folder."/".$file."' style='width: 120px; height: 120px;margin:auto'><br />";
                  echo $file."<br>";
                  echo "<a href='index.php?delete=".$file."'>delete</a>";
                  echo "<br>";
                  echo "</div>";
              }
              ?>
            </td>
        <tr>
    </table>

    <script>
    function updateImage() {
        var timestamp = (new Date()).getTime();

        document.getElementById("live_preview").src = "./img_preview.php?" + timestamp;
        document.getElementById("live_ml").src = "./img_ml.php?" + timestamp;

        // update every 0.2s - can be changed to any value
        setTimeout(updateImage, 200);
    }

    function sendCommand(x) {
         var xhr = new XMLHttpRequest();
         xhr.open("GET", "index.php?send_command=" + x, true);
         xhr.send();
    }

   window.onload = updateImage();
  </script>



  </body>
</html>
