#!/usr/bin/python

import urllib2 as url
import time

def query(stock):
  page = "http://339.cs.northwestern.edu/~cel294/portfolio/scripts/getHistoricalData.php?s=" + stock
  handle = url.urlopen(page)
  handle.close()

stocks = open('sp500.txt', 'r')

for line in stocks:
  stock = line.strip()
  query(stock)
  time.sleep(2)
