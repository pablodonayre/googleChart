<html>
    <head>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script type ="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.js"></script>
        
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/js/bootstrap.min.js" integrity="sha384-a5N7Y/aK3qNeh15eJKGWxsqtnX/wWdSZSKp+81YjTmS15nvnvxKHuzaWwXHDli+4" crossorigin="anonymous"></script>
        <script defer src="https://use.fontawesome.com/releases/v5.0.6/js/all.js"></script>
        <script type ="text/javascript" src="moment.js"></script>
        <style>
            .red{
                color: red;
            }
            .blue{
                color: blue;
            }
            #change_percent,
            #hour{
                margin-top:9px;
                font-size: 21px;
            }
            #arrow{
                margin-top:9px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <h1 id="usd_sell_2"></h1>
                <div id="hour"></div>
                <i id="arrow" class="fas"></i>
                <div id="change_percent"></div>
            </div>
            <div class="row">
                BTC Sell: <div id="usd_sell"></div> &nbsp|&nbsp <div id="clp_sell"></div>
            </div>
        
            <div class="row">
                BTC Buy: <div id="usd_buy"></div> &nbsp|&nbsp<div id="clp_buy"></div>
            </div>
        
            <div id="chart_div"></div>

            <div >
                <a id="30days" href="javascript:void(0)" class="btn btn-primary btn-sm">30 days</a>
                <a id="60days" href="javascript:void(0)" class="btn btn-primary btn-sm">60 days</a>
                <a id="1year" href="javascript:void(0)" class="btn btn-primary btn-sm">1 Year</a>
                <a id="all" href="javascript:void(0)" class="btn btn-primary btn-sm">All Time</a>
            </div>
        
            <div class="row">
                Chart Updated up to Date (UTC): <div id="div1"></div>
            </div>
            <div id="div2"></div>
            <br>
            <div class="row">
                Market Price up to Date (UTC): <div id="last_update_date"></div>
            </div>
            <div id="last_update_value"></div>
        </div>
    </body>
    <script>
        var nodes = [];
        var timespan = '1year';
        var values;
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
                url: 'https://api.blockchain.info/stats?cors=true',
                type: 'GET',
                dataType: 'json',

                success: function(result){
                    document.getElementById('last_update_date').innerHTML = moment(new Date(result.timestamp)).utc().format('MMMM Do YYYY, HH:mm:ss');
                    var market_price = result.market_price_usd;
                    document.getElementById('last_update_value').innerHTML = " USD $ "+market_price.toLocaleString(undefined,{minimumFractionDigits: 3, maximumFractionDigits: 3});
                }
            });  
            
            request_data();
            
            
        });
        
        function request_data(){
            nodes = [];
            $.ajax({
                url: 'https://api.blockchain.info/charts/market-price?cors=true&timespan='+timespan,
                type: 'GET',
                dataType: 'json',
                success: function(result){
                    values = result.values;
                    // Este "for" aumenta el tiempo de carga del grafico, se debera optimizar a futuro
                    for(i=0; i<values.length; i++){
                        node_date= new Date(values[i].x*1000);
                        nodes.push([node_date, values[i].y]);

                    }
                    
                    document.getElementById('div1').innerHTML = moment(new Date(values[values.length-1].x*1000)).utc().format('MMMM Do YYYY, HH:mm:ss');
                    
                    values = values[values.length-1].y;
                    document.getElementById('div2').innerHTML = " USD $ "+values.toLocaleString(undefined,{minimumFractionDigits: 3, maximumFractionDigits: 3});
                    
                    
                    google.charts.load('current', {packages: ['corechart', 'line']});
                    google.charts.setOnLoadCallback(drawBasic);
                    
                    exchange_rate();

                }
            });
            
        }

        function drawBasic() {

          var data = new google.visualization.DataTable();
          data.addColumn('date', 'X');
          data.addColumn('number', 'USD');
           /*
          data.addRows([
            [0, 0],   [1, 10],  [2, 23],  [3, 17],  [4, 18],  [5, 9],
            [6, 11],  [7, 27],  [8, 33],  [9, 40],  [10, 32], [11, 35],
            [12, 30], [13, 40], [14, 42], [15, 47], [16, 44], [17, 48]
          ]);
          */
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
                //gridlines: {color: 'none'},
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
        
        function exchange_rate(){
            $.ajax({
                url: 'https://blockchain.info/es/ticker',
                type: 'GET',
                dataType: 'json',
                success: function(result){
                    var usd_sell =result.USD.sell.toLocaleString(undefined,{minimumFractionDigits: 2, maximumFractionDigits: 2});
                    document.getElementById('usd_sell').innerHTML= "&nbsp USD $ "+usd_sell+"&nbsp ";
                    document.getElementById('usd_buy').innerHTML= "&nbsp USD $ "+result.USD.buy+"&nbsp ";
                    document.getElementById('clp_sell').innerHTML= "&nbsp CLP $ "+result.CLP.sell+"&nbsp ";
                    document.getElementById('clp_buy').innerHTML= "&nbsp CLP $ "+result.CLP.buy+"&nbsp ";
                    
                    document.getElementById('usd_sell_2').innerHTML= "USD $ "+result.USD.sell+"&nbsp ";
                    
                    var btc_current = result.USD.sell;
                    var btc_open = values;
                    var change = btc_current-btc_open;
                    var change_percent = (change*100/btc_open).toFixed(2);

                    if(change_percent>=0){
                        $('#change_percent').html(" +"+change_percent+" %").addClass('blue').removeClass('red');
                        $('#arrow').addClass('fas fa-caret-up fa-2x blue');//.removeClass('fas fa-caret-down fa-2x red');
                        $('#hour').html(" 24h &nbsp").addClass('blue').removeClass('red');
                    }else{
                        $('#change_percent').html(change_percent+" %").addClass('red').removeClass('blue');
                        $('#arrow').addClass('fas fa-caret-down fa-2x red');//.removeClass('fas fa-caret-up fa-2x blue');
                        $('#hour').html(" 24h &nbsp").addClass('red').removeClass('blue');
                    }
                }
            });
        }
    </script>
</html>