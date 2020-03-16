# script to read event metadata from http://wikicfp.com
# use: python events.py [startId] [stopId] [filename]
# @param startId
# @param stopId
# @param filename
# example: python events.py 2000 2999 output-2000-2999.csv

import sys
import urllib
from bs4 import BeautifulSoup
import csv

# get metadata from td
def get_td_data(label):
    th = soup.find("th", text=label)
    if th:
        return th.find_next_sibling('td').text.strip().encode('utf-8')

# get dublin core metadata
def get_dublin_core_data(label):
    return soup.find('span', {'property': 'dc:'+label}).attrs.get('content').encode('utf-8')

# variables
dcMetadata = ['identifier', 'title', 'description', 'source'] # metadata that is stored with dublin core tags
spanMetadata = ['startDate', 'endDate', 'locality', 'eventType', 'summary'] # metadata that is stored in spans
tdMetadata = ['Submission Deadline', 'Notification Due', 'Final Version Due'] # metadata that is stored in td
fieldnames = dcMetadata + spanMetadata + tdMetadata + ['Categories', 'Call For Papers', 'Related Resources'] # combination of the metadata for the csv table heading

# get filename from user input
if len(sys.argv) == 4: filename = sys.argv[3]
else: filename ='events.csv'

# prepare output file
csvfile = open(filename, 'w')
file = csv.DictWriter(csvfile, fieldnames = fieldnames)
file.writeheader()

# get range from user input
for i in range(int(sys.argv[1]), int(sys.argv[2]), -1):

    # store the data of this event in a dictionary
    result = {}

    # get html via url
    url = "http://wikicfp.com/cfp/servlet/event.showcfp?eventid="+str(i)
    html = urllib.urlopen(url).read()
    soup = BeautifulSoup(html, 'html.parser', from_encoding="windows-1259")

    # check if event for this id exists (there should be a h2 with some of the metadata)
    if soup.find('h2'):

        # get dublin core metadata
        for metadata in dcMetadata:
            result[metadata] = get_dublin_core_data(metadata)

        # get metadata and its labels from spans
        for span in soup.find('h2').find_all('span'):
            if span.attrs.get('content') and span.attrs.get('property'):
                result[span.attrs.get('property').replace('v:', '')] = span.attrs.get('content').encode('utf-8')

        # get metadata from h5: categories
        if soup.find('h5'):
            categories = ''
            for category in soup.find('h5').find_all('a'):
                if category.text != 'Categories':
                    categories += '"'+category.text+'" '
            result['Categories'] = categories

        # get metadata from td: dates
        for metadata in tdMetadata:
            result[metadata] = get_td_data(metadata)

        # get Call For Papers and Related Resources
        div = soup.findAll('div', {'class': 'cfp'})
        result['Call For Papers'] = div[0].text.encode('utf-8') # Call For Papers
        result['Related Resources'] = div[1].encode('utf-8') # Related Resources

        # write this event to the file
        file.writerow(result)

    # print id to commandline to give feedback on the process
    print i

# close csv file
csvfile.close()

# finish
print 'all done - check ', filename ,' for the results'
