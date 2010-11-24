#!/usr/bin/python

import urllib2 as url
import time

def query(stock):
  page = "http://339.cs.northwestern.edu/~cel294/portfolio/scripts/getQuote.php?s=" + stock
  handle = url.urlopen(page)
  handle.close()

stocks = open('/home/cel294/public_html/portfolio/scripts/sp500.txt', 'r')

for line in stocks:
  stock = line.strip()
  query(stock)
  #print "I would now add stock: " + stock
  time.sleep(2)
