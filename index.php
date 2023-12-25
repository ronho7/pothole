<?php
    $userID = intval($_COOKIE['userID']);
    $conn = mysqli_connect("", "", "", "");
    $sql = "SELECT * FROM potholeuser WHERE ID=" . $userID;
    $result = mysqli_query($conn, $sql);
    $cookie_result = $result->fetch_assoc();
    $level = $cookie_result['level'];
    $conn->close();
    
    switch ($level) {
        case 1:
            header('location: map.php?mode=1');
            break;
        case 2:
            header('location: map_boss.php');
            break;
        case 3:
            header('location: admin.php');
            break;
    };

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="description" content="Pothole App"> <!-- This is what google look for in search -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, interactive-widget=resizes-content">
    <title>Pothole</title>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <style>
        body {
            font-family: Arial, Helvetica, serif;
            margin: 0;
            padding: 0;
            overflow: hidden;
            background-color: #000; /* Set a background color for better contrast */
        }

        #video {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: auto;
            z-index: 1;
        }
        #tapText {
            position: fixed;
            z-index: 2;
            color: white;
            font-size: 20px;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
        }
        #checkDuplicate {
            border: solid;
            display: flex;
            justify-content: center;
            z-index: 2;
        }
        #wait {
            font-size: 30px;
            position: fixed;
            top: 0;
            left: 0;
            width:100vw;
            height:100vh;
            background-color: black;
            color: black;
            z-index: 0;
            align-content: middle;
        }
        button {
            font-size: 20px;
            border: solid;
            border-color: purple;
            color: purple;
            background-color: black;
            border-radius: 10px;
            padding: 5px 10px;
            margin: none;
            position: fixed;
            top: 600px;
            z-index: 2;

        }
    </style>
</head>
<script>
    v=Date.now();        
    var iOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    if (iOS) { 
        document.write('<link rel="manifest" href="manifest-ios.json?v=' + v + '">');
        aler("hi");
    } else { 
        document.write('<link rel="manifest" href="manifest.json?v=' + v + '">');
    }
</script>

<?php
    include '../functions.php';
?>
<body>
    <div id="tapText" onclick=captureImage()>
        <center><br><?php echo translate('Tap the screen to take picture', language(), 'en-US') ?></center>
    </div> 
    <video id="video" muted autoplay playsinline></video>
    <div id=wait>
        <center>
        <br><br><br><br><?php echo translate('Obtaining location and uploading picture', language(), 'en-US') ?><br><br>
        <img id=waiting>
        </center>
    </div>
    <div id="checkDuplicate"><button onclick="goToMapDuplicate()"><?php echo translate('Check duplicates', language(), 'en-US') ?></button></div>
    <script>
        
        var id;
        var gps_accuracy = 9;
        var options = { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }; // Options for geolocation accuracy:, GPS only, wait no longer than 5000 seconds

        function goToMapDuplicate() {
            window.location.href = 'map_user.php';
        }

        function hideButton() {
            var button = document.getElementById('checkDuplicate');

            // Set the button's style to hide it (display: none)
            button.style.display = 'none';
        }

        function GPSdata(position) {
            lat = position.coords.latitude;
            lng = position.coords.longitude;
            accuracy = position.coords.accuracy; // metres
            if(position.coords.accuracy<=gps_accuracy) { // keep trying until accuracy good enough
                window.location.href = 'process.php?id=' + id + '&lat=' + lat + '&lng=' + lng;
            }
        }

        function GPSerror(error) {
            results.innerHTML = 'Error getting location ' + error + '<br>';
        }

        document.addEventListener('DOMContentLoaded', function () {
            
            const video = document.getElementById('video');
            const tapText = document.getElementById('tapText');
            let stream;
            // Get rear camera (environment is rear camera, user would be front facing camera)
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
                .then((mediaStream) => {
                    video.srcObject = mediaStream;
                    stream = mediaStream;
                })
                .catch((error) => {
                    console.error('Error accessing rear camera:', error);
                });

            // Tap on the video to capture
            tapText.addEventListener('click', function () {
                captureImage();
                hideButton();
            });
            
            function captureImage() {
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                const dataUrl = canvas.toDataURL('image/png');

                document.getElementById('wait').style.zIndex = '2';
                document.getElementById('wait').style.color = 'white';
                document.getElementById('waiting').src='waiting.gif';

                fetch('capture.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded', },
                        body: 'imageData=' + encodeURIComponent(dataUrl),
                    })
                    .then(response => {
                        if (!response.ok) { 
                            throw new Error('Network response was not ok'); 
                        } 
                        return response.text(); 
                    })
                    .then(response => {
                        console.log(response);
                        id = response;
                        var watchID = navigator.geolocation.watchPosition(GPSdata, GPSerror, options);
                        
                    })
                    .catch(error => {
                        console.error('Error during fetch:', error);
                    });
                
                // turn off camera
                stream.getTracks().forEach(track => track.stop());
            }
            
        });
        
    </script>
</body>
</html>
<script>
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', function() {
        navigator.serviceWorker.register('service-worker.js?v=' + v).then(function(registration) {
          console.log('Service worker registered successfully:', registration.scope);
        }).catch(function(error) {
          console.log('Service worker registration failed:', error);
        });
      });
    }
   
    // reload to check cookies on PWA
    window.onload = function() {  
        if(window.location.hash !== '#loaded') {            
            window.location.hash = '#loaded';
            window.location.reload();
        }
    }
        
   /*
    window.onload = function() {
        if(!localStorage.loaded) {
            localStorage.setItem('loaded', 'yes')
            window.location.reload();
        }
    } 
    */
</script>