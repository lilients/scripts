# script to read event metadata from http://wikicfp.com
# use: python events.py [startId] [stopId] [filename]
# @param startId
# @param stopId
# @param filename
# example: python events.py 2000 2999 output-2000-2999.csv

#
# This script has been modified by Philipp Holz
# The script is now capable of concurrency usage
# This should impreove the performance
#
# The script was also ported to work with latest python version
#
# another parameter was added, determines how much threads the script is allowed to use
# @param threads


import sys
# with python 3 you need this import instead of just urllib
import urllib.request
from bs4 import BeautifulSoup
import csv
# needed for threading
import threading
import logging

# get range from user input
startId = sys.argv[1]
stopId = sys.argv[2]

# get filename from user input
if len(sys.argv) == 4: filename = sys.argv[3]
else: filename ='events'

# if parameter thread is given take it otherwise use 1 threads as default
if len(sys.argv) == 5: threads = sys.argv[4]
else: threads = 1

# do not use more than 10 threads at all
if int(threads) >= 10:
    threads = 10

# init logger
logging.basicConfig(level=logging.INFO, format="%(message)s")
logger = logging.getLogger("queue_example")

# get metadata from td
def get_td_data(label, soup):
    th = soup.find("th", text=label)
    if th:
        return th.find_next_sibling('td').text.strip().encode('utf-8')

# get dublin core metadata
def get_dublin_core_data(label, soup):
    return soup.find('span', {'property': 'dc:'+label}).attrs.get('content').encode('utf-8')

# variables
dcMetadata = ['identifier', 'title', 'description', 'source'] # metadata that is stored with dublin core tags
spanMetadata = ['startDate', 'endDate', 'locality'] # metadata that is stored in spans
tdMetadata = ['Submission Deadline', 'Notification Due', 'Final Version Due'] # metadata that is stored in td
fieldnames = dcMetadata + spanMetadata + tdMetadata + ['Categories', 'Call For Papers', 'Related Resources'] # combination of the metadata for the csv table heading

# prepare output file
def threadedFunction(filename, startId, stopId):
    with open(filename, 'w') as csvfile:
        file = csv.DictWriter(csvfile, fieldnames = fieldnames)
        file.writeheader()

        # go forward or backward
        if startId <= stopId: step = +1
        else: step = -1

        # get all ids
        for i in range(int(startId), int(stopId), step):

            # store the data of this event in a dictionary
            result = {}

            # get html via url
            url = "http://wikicfp.com/cfp/servlet/event.showcfp?eventid="+str(i)
            # with python 3 you need to change with call use request
            response = urllib.request.urlopen(url)
            html = response.read()
            soup = BeautifulSoup(html, 'html.parser', from_encoding="windows-1259")

            # check if event for this id exists (there should be a h2 with some of the metadata)
            if soup.find('h2'):

                # get dublin core metadata
                for metadata in dcMetadata:
                    result[metadata] = get_dublin_core_data(metadata, soup)

                # get metadata and its labels from spans
                for span in soup.find('h2').find_all('span'):
                    if span.attrs.get('content') and span.attrs.get('property'):
                        thisMetadata = span.attrs.get('property').replace('v:', '')
                        if thisMetadata in spanMetadata:
                            result[thisMetadata] = span.attrs.get('content').encode('utf-8')

                # get metadata from h5: categories
                if soup.find('h5'):
                    categories = ''
                    for category in soup.find('h5').find_all('a'):
                        if category.text != 'Categories':
                            categories += str(category.text.encode('utf-8')) + ' '
                    result['Categories'] = categories

                # get metadata from td: dates
                for metadata in tdMetadata:
                    result[metadata] = get_td_data(metadata, soup)

                # get Call For Papers and Related Resources
                div = soup.findAll('div', {'class': 'cfp'})
                if div[0]: result['Call For Papers'] = div[0].text.encode('utf-8') # Call For Papers
                if len(div) > 1: result['Related Resources'] = div[1].encode('utf-8') # Related Resources

                # write this event to the file
                file.writerow(result)

            # thread safe console output
            logger.info(i)

# determine the id range for each thread
id_range = int(stopId) - int(startId)
chunkSize = int(id_range) / int(threads)
print('We have ' + str(threads) + ' threads with chunks of ' + str(int(chunkSize))+ ' IDs each')

# this list will contain all threads -> we can wait for all to finish at the end
allThreads = []

# now start each thread with its id range and own filename
for i in range(int(threads)):

    s = int(startId) + (i * chunkSize)
    e = s + (chunkSize - 1)
    # last chunk we do not want to forget the last id
    if i == int(threads) - 1:
        e += 1

    print('threadedFunction with:' + str(int(s)) + ' and ' + str(int(e)))
    threadFilename = filename + '_' + str(int(s)) + '-' + str(int(e)) + '.csv'

    thread = threading.Thread(target = threadedFunction, args=[threadFilename, s, e])
    thread.start()
    allThreads.append(thread)

# wait till all threads have finished before print the last output
for t in allThreads:
    t.join()

print('All threads have finished their work')
