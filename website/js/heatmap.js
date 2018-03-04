
//Written by GROUP#3
//Adding Data Points
var map, pointarray, heatmap, heatmap2, geocoder, marker;
var infowindow = new google.maps.InfoWindow();
//test data

var heatmapdata_ = new Array;
var coordinates = new Array;
var geolocation = new Array;
var jsonfile = 'location';
var databasenum = 18;
var databasecount = 0;
var pointArray;
var dynamic = false;

function getdata(){

  coordinates.splice(0,coordinates.length);
  $.getJSON(jsonfile,function(result){
    $.each(result, function(i, field){
      coordinates.push(eval(field.Lat));      //get lat lng data NOTE: MIND THE ORDER
      coordinates.push(eval(field.Lng));
    });
  getheatmappoint(coordinates);                   //copy the coordinates data to the heatmapdata_ while coordinates is not expired 
});
}

function getheatmappoint(datasource)
{
  heatmapdata_.splice(0,heatmapdata_.length);
  for(var datacount = 0; datacount < datasource.length/2; datacount++){
    var temp = new google.maps.LatLng(datasource[datacount*2],datasource[datacount*2+1]);
    heatmapdata_.push(temp);
    //codeLatLng(temp);
  };
  codeLatLng(heatmapdata_[0]);
  //$('p').append(datasource.length/2);
}

function codeLatLng(coordinates_) {
  geocoder.geocode({'latLng': coordinates_}, function(results, status) {
    if (status == google.maps.GeocoderStatus.OK) {
      if (results[6].formatted_address) {
        geolocation.push(results[6].formatted_address);
        //$('p').append(geolocation);
      } 
    }
    //else if(status == google.maps.GeocoderStatus.OVER_QUERY_LIMIT)$('p').append("Over Query Limit!");
    //else if(status == google.maps.GeocoderStatus.ZERO_RESULTS)$('p').append("Zero Results!");
  });
}

function writeFile(filename,filecontent){  
  var fso, f, s ;   
    fso = new ActiveXObject("Scripting.FileSystemObject");    //This won't work on MAC!!
    f = fso.CreateTextFile(filename,2,true);  
    f.WriteLine(filecontent); 
    f.Close();   
    alert('write ok');   
  }

  function initialize() {
    geocoder = new google.maps.Geocoder();
    var mapOptions = {
      zoom: 4,
      center: new google.maps.LatLng(42.494222, -90.433523),
      minZoom: 3,
      mapTypeId: google.maps.MapTypeId.MAP
    };

  map = new google.maps.Map(document.getElementById('map-canvas'),mapOptions);  //Initialize gmap

  pointArray = new google.maps.MVCArray(heatmapdata_);                      //Initialize heatmap layer

  heatmap = new google.maps.visualization.HeatmapLayer({data: pointArray},{radius: 5});

  heatmap.setMap(map);
}

function dynamicheatmap()
{
  dynamic = !dynamic;
  if(dynamic)
  {
    var count = 0;
    timerID = window.setInterval(function()
    {
      databasecount = (databasecount)%databasenum;
      jsonfile = 'location';
      jsonfile = jsonfile + (databasecount).toString() + '.json';
      getdata();


      pointArray.delete;
      pointArray = new google.maps.MVCArray(heatmapdata_);//Initialize 
      heatmap2 = new google.maps.visualization.HeatmapLayer({data: pointArray});
      heatmap2.set('radius',15);
      heatmap2.setMap(map);
      heatmap.delete;

      heatmap = heatmap2;
      heatmap.set('radius',15);
      heatmap.setMap(map);
      databasecount++;
      count++;
      
      if(count ==(2*databasenum))window.clearInterval(timerID);
    }
    ,200);
  }
  else
  {
    window.clearInterval(timerID);

    jsonfile = 'location';
    jsonfile = jsonfile + (databasecount).toString() + '.json';
    getdata();
    pointArray = new google.maps.MVCArray(heatmapdata_);
    heatmap = new google.maps.visualization.HeatmapLayer({data: pointArray});
    heatmap.setMap(map);
  }
  
}

//Auxilliary functions
function toggleHeatmap() {
  if(!heatmap.getMap())
  {
    initialize();
  }
  else 
  {
    heatmap.setMap(null);
  }
}

function changeGradient() {
  var gradient = [
  'rgba(0, 255, 255, 0)',
  'rgba(0, 255, 255, 1)',
  'rgba(0, 191, 255, 1)',
  'rgba(0, 127, 255, 1)',
  'rgba(0, 63, 255, 1)',
  'rgba(0, 0, 255, 1)',
  'rgba(0, 0, 223, 1)',
  'rgba(0, 0, 191, 1)',
  'rgba(0, 0, 159, 1)',
  'rgba(0, 0, 127, 1)',
  'rgba(63, 0, 91, 1)',
  'rgba(127, 0, 63, 1)',
  'rgba(191, 0, 31, 1)',
  'rgba(255, 0, 0, 1)'
  ]
  heatmap.set('gradient', heatmap.get('gradient') ? null : gradient);
}

function changeRadius() {
  heatmap.set('radius', heatmap.get('radius') ? null : 50);
}

function changeOpacity() {
  heatmap.set('opacity', heatmap.get('opacity') ? null : 0.2);
}


//Main process
jsonfile = 'location';
jsonfile = jsonfile + (databasecount).toString() + '.json';
getdata();


google.maps.event.addDomListener(window, 'load', initialize);