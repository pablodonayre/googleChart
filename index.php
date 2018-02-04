<html>
    <head>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script type ="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.js"></script>
        
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<!--         <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script> -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/js/bootstrap.min.js" integrity="sha384-a5N7Y/aK3qNeh15eJKGWxsqtnX/wWdSZSKp+81YjTmS15nvnvxKHuzaWwXHDli+4" crossorigin="anonymous"></script>
        
        <script type ="text/javascript" src="moment.js"></script>
    </head>
    <body>
        Current Price up to Date (UTC): <div id="last_update_date"></div>
        <div id="last_update_value"></div>

        <div id="chart_div"></div>
        
        <div >
            <a id="30days" href="javascript:void(0)" class="btn btn-primary btn-sm">30 days</a>
            <a id="60days" href="javascript:void(0)" class="btn btn-primary btn-sm">60 days</a>
            <a id="1year" href="javascript:void(0)" class="btn btn-primary btn-sm">1 Year</a>
            <a id="all" href="javascript:void(0)" class="btn btn-primary btn-sm">All Time</a>
        </div>
        <div>
            Chart Updated up to Date (UTC):
            <div id="div1"></div>
        </div>
        <div>
            Value: 
            <div id="div2"></div>
        </div>
    </body>
    <script>
        var nodes = [];
        var timespan = '1year';
        
        $( document ).ready(function() {
            
            $('#30days').click(function(){
                 timespan='30days';  
                 request_data();
            });
            
            $('#60days').click(function(){
                 timespan='60days';  
                 request_data();
            });
            
            $('#1year').click(function(){
                 timespan='1year'; 
                 request_data();
            }); 
            
            $('#all').click(function(){
                 timespan='all'; 
                 request_data();
            });            
            
            $.ajax({
                url: 'stats_api.php',
                type: 'GET',
                dataType: 'json',

                success: function(result){
                    document.getElementById('last_update_date').innerHTML = moment(new Date(result.timestamp)).utc().format('MMMM Do YYYY, HH:mm:ss');
                    var market_price = result.market_price_usd;
                    document.getElementById('last_update_value').innerHTML = "USD $ "+market_price.toLocaleString(undefined,{minimumFractionDigits: 3, maximumFractionDigits: 3});
                }
            });  
            
            request_data();
        });
        
        function request_data(){
            nodes = [];
            $.ajax({
                url: 'api.php',
                type: 'POST',
                dataType: 'json',
                data:
                    {
                        timespan: timespan,
                    },
                success: function(result){
                    var values = result.values;
                    console.log(values);
                    for(i=0; i<values.length; i++){
                        node_date= new Date(values[i].x*1000);

                        console.log(node_date);
                        
                        nodes.push([node_date, values[i].y]);

                    }
                    
                    console.log(nodes);
                    document.getElementById('div1').innerHTML = moment(new Date(values[values.length-1].x*1000)).utc().format('MMMM Do YYYY, HH:mm:ss');
                    
                    var values = values[values.length-1].y;
                    document.getElementById('div2').innerHTML = "USD $ "+values.toLocaleString(undefined,{minimumFractionDigits: 3, maximumFractionDigits: 3});
                    
                    
                    google.charts.load('current', {packages: ['corechart', 'line']});
                    google.charts.setOnLoadCallback(drawBasic);
                    
                }
            });
            
        }

        function drawBasic() {

          var data = new google.visualization.DataTable();
          data.addColumn('date', 'X');
          data.addColumn('number', 'USD');

//           data.addRows([
//             [0, 0],   [1, 10],  [2, 23],  [3, 17],  [4, 18],  [5, 9],
//             [6, 11],  [7, 27],  [8, 33],  [9, 40],  [10, 32], [11, 35],
//             [12, 30], [13, 40], [14, 42], [15, 47], [16, 44], [17, 48]
//           ]);

            data.addRows(nodes);
          var options = {
                title: 'Market Price of Bitcoin (Average per Day)',
                width: 900,
                height: 500,              
            hAxis: {
                title: 'Date',
                gridlines: {count: 15},
            },
            vAxis: {
                title: 'USD',
                gridlines: {count: 10},
//                 gridlines: {color: 'none'},
                minValue: 0                
            },
            explorer: { 
                    actions: ['dragToZoom', 'rightClickToReset'],
                    axis: 'horizontal',
                    keepInBounds: true,
                    maxZoomIn: 4.0
            }              
          };

            var date_format = new google.visualization.DateFormat({pattern:"MMM dd, yyyy HH:mm:ss", timeZone: 0});
            date_format.format(data,0);
          var chart = new google.visualization.LineChart(document.getElementById('chart_div'));

          chart.draw(data, options);
        }
    </script>
</html>