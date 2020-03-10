# script to read data from http://wikicfp.com

import urllib
from bs4 import BeautifulSoup
import csv

# read url # TODO: handle ids in a loop
url = "http://wikicfp.com/cfp/servlet/event.showcfp?eventid=2"
html = urllib.urlopen(url).read()
soup = BeautifulSoup(html, 'html.parser')

# result object
results = {}

# read title from html and store in result object
results['title'] = soup.title.string;

# get metadata from h2
h2 = soup.find('h2')
spans = h2.find_all('span')

# get metadata and its labels from spans
for span in spans:
    if(span.attrs.get('content') and span.attrs.get('property')):
        results[str(span.attrs.get('property').replace('v:', ''))] = str(span.attrs.get('content'))

# TODO: get metadata from other places

# write to csv
csvfile = open('events.csv','w')
fields = list(results.keys())
obj = csv.DictWriter(csvfile, fieldnames=fields)
obj.writeheader()
obj.writerows([results])
csvfile.close()
