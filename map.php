<!DOCTYPE html>
<html lang="en">
<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- leaflet map library style and js code -->

  <link rel="stylesheet" href="map/leaflet.css" /> 
  <script src="map/leaflet.js"></script>

</head>
<style>
    body {
        margin:0;
        border:0;
        padding:0;
        font-family: 'Defa Vu Sans',Arial, Helvetica, serif;
    }
    #map {
        height: 100vh;
        width: 100%;
    }

    /* the following 3 elements are from the leaflet library, this is to override some of it's default style */

    .leaflet-popup-content img { /* allow the image to be responsive design */
        width: 100%;
        height: auto;
    }
    .leaflet-popup-content-wrapper { /* black background for pin popup */
        background-color: black;
    }
    .leaflet-popup-content { /* white colour for pip popup text */
        color: white;
    }

</style>
<body>

    <div id="map"></div> <!-- the leaflet map library expects this div to exist so it can draw and style it's map -->

    <?php

        // read data from database and put it in an array

        $plots = array();
        $conn = mysqli_connect("","","","");
        $sql = "select * from pothole where isnull(completed)";
        $result = mysqli_query($conn,$sql);
        if ($result->num_rows>0) {
            while ($row = $result->fetch_assoc()) {
                $next_record=array($row["ID"],$row["latitude"],$row["longitude"],$row["address"],$row["created"]);
                $plots[]=$next_record;
            }
        }    
        $conn->close();
    ?>

    <script>

        /*
        var customIcon = L.divIcon({
            className: 'custom-marker',
            iconSize: [30, 40],
            iconAnchor: [15, 40],
            html: '<svg width="30" height="30" xmlns="http://www.w3.org/2000/svg"><circle cx="15" cy="15" r="12" fill="red" stroke="black" stroke-width="2"/></svg>'            
        });
        */
        var size=20;
        var customIcon = L.divIcon({
            className: 'custom-marker',
            iconSize: [size, size],
            iconAnchor: [size, size],
            html: '<svg width="' + size + '" height="' + size + '" xmlns="http://www.w3.org/2000/svg"><circle cx="' + size / 2 + '" cy="' + size / 2 + '" r="' + (size / 2 - 2) + '" fill="red" stroke="black" stroke-width="2"/></svg>'
        });

        // the following is just used to format dates. For example, change it from 2023-12-09 11:15:30 to something nicer such as Dec 9, 2023 11:15AM 
        var options = { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: 'numeric', hour12: true };

        // initialize map centred to lat/lng 46.2344,-63.1280 and zoomed to a factor of 13
        var map = L.map('map').setView([46.2344,-63.1280], 13);

        // set map to open at current location
        // map = L.map('map').locate({setView: true, maxZoom: 16});

        // add attribution for OpenStreetMap. It's considered polite in the open source community to give credit to the authors in exchange for free usage.
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap contributors', maxNativeZoom:19, maxZoom:25 }).addTo(map);

        // copy the php array we created previously to a js array
        var plots = <?php echo json_encode($plots); ?>;
        var markers = [];

        // loop through the array and draw the plots on the map
        for (var i = 0; i < plots.length; i++) {
            // var marker = L.marker([plots[i][1], plots[i][2]]).addTo(map); // add lat/lng for pin point
            var marker = L.marker([plots[i][1], plots[i][2]], { icon: customIcon }).addTo(map); // add lat/lng for pin point

            // next we add content to the popup
            var popupContent='';
            popupContent = popupContent + plots[i][3] + '<br>'; // add the address

            // convert date to more readable format
            dateObject = new Date(plots[i][4]);
            var formattedDate = dateObject.toLocaleString('en-US', options);

            popupContent = popupContent + formattedDate + '<br>'; // add the date

            popupContent = popupContent + '<img src=image/' + plots[i][0] + '.png onclick=openFullScreenPopup("'+plots[i][0]+'")>'; // add the picture

            // bind our content to the pin so it comes up when we click the pin
            marker.bindPopup(popupContent);
            markers.push(marker);
        }    

        // this is the full screen popup that appears when clicking the image in the popup

        function openFullScreenPopup(id) {
            var width = screen.width;
            var height = screen.height;

            window.open(
                'update.php?ID=' + id + '&mode=1',
                'FullScreenPopup',
                'width=' + width + ', height=' + height + ', top=0, left=0'
            );
        }

    </script>

</body>
</html>
