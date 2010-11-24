function drawTimeline(symbol) {
  google.load('visualization', '1', {'packages':['annotatedtimeline']});
  google.setOnLoadCallback(function(){drawChart(symbol)});
  function drawChart(symbol) {
    $.getJSON('graph.php', {'symbol':symbol}, function(quotes) { 
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

function drawTimeline(portfolio_id) {
  google.load('visualization', '1', {'packages':['annotatedtimeline']});
  google.setOnLoadCallback(function(){drawChart(symbol)});
  function drawChart(portfolio_id) {
    $.getJSON('index.php', {'id':portfolio_id,'p':'portfolio-json'}, function(quotes) { 
      var data = new google.visualization.DataTable();
      data.addColumn('date', 'Date');
      data.addColumn('number', 'Value');
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

$(document).ready(function(){
  $('#transaction-type').change( function(){
    if( this.value == 'sell' || this.value == 'buy' ) {
      //$('#quantity-label').html('Shares');
      $('.stock-transaction').show();
      $('.cash-transaction').hide()
    }
    else {
      $('.stock-transaction').hide();
      $('.cash-transaction').show();
    }
    });
});
