#!/usr/bin/perl -w

use Getopt::Long;
use Time::ParseDate;
use Time::CTime;
use FileHandle;
use DBI;

$user='cel294';
$pass='o66abbfd4';
$dbh = DBI->connect("DBI:Oracle:",$user,$pass);
if (not $dbh) {
  die "Can't connect to database because of ".$DBI::errstr;
  }


$close=1;

$nodate=0;
$open=0;
$high=0;
$low=0;
$close=0;
$vol=0;
$from=0;
$to=0;
$plot=0;

&GetOptions( "nodate"=>\$nodate,
             "open" => \$open,
	     "high" => \$high,
	     "low" => \$low,
	     "close" => \$close,
	     "vol" => \$vol,
	     "from=s" => \$from,
	     "to=s" => \$to,
	     "plot" => \$plot);

if (defined $from) { $from=parsedate($from); }
if (defined $to) { $to=parsedate($to); }


$#ARGV==0 or die "usage: get_data.pl [--open] [--high] [--low] [--close] [--vol] [--from=date] [--too=date] [--plot] SYMBOL\n";

$symbol=shift;

push @fields, "time" if !$nodate;
push @fields, "open" if $open;
push @fields, "high" if $high;
push @fields, "low" if $low;
push @fields, "close" if $close;
push @fields, "volume" if $vol;



$sql = "select ".join(",",@fields). " from portfolio_StocksDaily";
$sql.= " where symbol='$symbol'";
$sql.= " and time>=$from" if $from;
$sql.= " and time<=$to" if $to;
$sql.= " order by time";

#print STDERR $sql,"\n";

my $sth = $dbh->prepare($sql);
if (not $sth) {
   my $errstr="Can't prepare $sql because of ".$DBI::errstr;
   die $errstr;
}

if(not $sth->execute()) {
  my $errstr="Can't execute $sql because of ".$DBI::errstr;
  $dbh->disconnect();
  die $errstr;
}

my @data;

while(@data=$sth->fetchrow_array()) {
  foreach (@data) {$_ = "$_\t"}
  print "@data\n";
  #print "@ret\n";
}


$dbh->disconnect();



