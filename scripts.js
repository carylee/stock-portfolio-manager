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

function portfolioChart(portfolio_id) {
  google.load('visualization', '1', {'packages':['annotatedtimeline']});
  google.setOnLoadCallback(function(){drawChart(portfolio_id)});
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
      var chart = new google.visualization.AnnotatedTimeLine(document.getElementById('portfolio_chart'));
      chart.draw(data, {displayAnnotations: true});
      });
  }
}

var replaceImage = function(symbol, id) {
  $.get('futureChart.php', {'s':symbol}, function(data) {
    $("#"+id)[0].src = data;
  })}

$(document).ready(function(){
  $("#search-symbol").focus( function() {
    if( $(this).val() == 'Symbol') {
      $(this).val('');
    }
  });
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
  $('#overview-symbol').change( function() {
    $.get('index.php', {'p':'getcost','s':$(this).val()}, function(data) {
      $('#overview-cost').val(data);
    });
  });
});
