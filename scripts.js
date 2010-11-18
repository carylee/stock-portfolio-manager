function drawTimeline() {
  google.load('visualization', '1', {'packages':['annotatedtimeline']});
  google.setOnLoadCallback(drawChart);
  function drawChart() {
    $.getJSON('graph.php', {'symbol':'AAPL'}, function(quotes) { 
      var data = new google.visualization.DataTable();
      data.addColumn('date', 'Date');
      data.addColumn('number', 'Close Price');
      var rows = [];
      for( var i in quotes ) {
        rows.push( [new Date( quotes[i]['date']*1000), quotes[i]['close']*1] );
      }
      data.addRows(rows);
      var chart = new google.visualization.AnnotatedTimeLine(document.getElementById('chart_div'));
      chart.draw(data, {displayAnnotations: true});
      });
  }
}
