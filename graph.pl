#!/usr/bin/perl -w

use strict;

# The CGI web generation stuff
# This helps make it easy to generate active HTML content
# from Perl
#
# We'll use the "standard" procedural interface to CGI
# instead of the OO default interface
use CGI qw(:standard);

# The interface to the database.  The interface is essentially
# the same no matter what the backend database is.  
#
# DBI is the standard database interface for Perl. Other
# examples of such programatic interfaces are ODBC (C/C++) and JDBC (Java).
#
#
# This will also load DBD::Oracle which is the driver for
# Oracle.
use DBI;

#
#
# A module that makes it easy to parse relatively freeform
# date strings into the unix epoch time (seconds since 1970)
#
use Time::ParseDate;


#
# The following is necessary so that DBD::Oracle can
# find its butt
#
#$ENV{ORACLE_HOME}="/opt/oracle/product/11.2.0/db_1";
#$ENV{ORACLE_BASE}="/opt/oracle/product/11.2.0";
#$ENV{ORACLE_SID}="CS339";

#
# You need to override these for access to your database
#
my $dbuser="cs339";
my $dbpasswd="cs339";

use Mysql;

my $host = "localhost";
my $database = "CS339";

my $connect = Mysql->connect('localhost', 'cs339', 'cs339', 'cs339');
$connect->selectdb('cs339');

print header(-expires=>'now');

#
# Get action user wants to perform
#
my $stock;
if (param("symbol")) { 
  $stock=param("symbol");
} else {
  $stock="AAPL";
}

#print start_html('Get Stock Chart');
print "<!DOCTYPE html><html><head><meta charset='utf-8'/><title>Get Stock Chart</title>";

my $query = "SELECT date, close, high, low FROM StocksDaily WHERE symbol='$stock' order by date desc";
my $execute = $connect->query($query);

print '<script>';
print "stockData = [";
while(my @results = $execute->fetchrow()) {
  print "{'date':'".$results[0]."','close':".$results[1].",'high':".$results[2].",'low':".$results[3]."},";
}
print "]</script>";

print "<script type='text/javascript' src='https://www.google.com/jsapi'></script>";

print "<script type='text/javascript'>
google.load('visualization', '1', {'packages':['annotatedtimeline']});
google.setOnLoadCallback(drawChart);
function drawChart() {
var data = new google.visualization.DataTable();
data.addColumn('date', 'Date');
data.addColumn('number', 'Close');
data.addColumn('number', 'High');
data.addColumn('number', 'Low');
var rows = [];
for( var row in stockData ) {
  rows.push( [new Date( stockData[row].date * 1000 ), stockData[row].close, stockData[row].high, stockData[row].low] );
}

data.addRows(rows
);

var chart = new google.visualization.AnnotatedTimeLine(document.getElementById('chart_div'));
chart.draw(data, {displayAnnotations: true});
}
</script>";
print "</head><body>";
print "<div id='chart_div' style='width: 700px; height: 240px;'></div>";
