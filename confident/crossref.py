# script to read event metadata from
# use: python crossref.py

import json
import requests
import csv

# variables
row = 1000
# datatypes = ['proceedings', 'proceedings-article', 'proceedings-series'] TODO
filename ='crossref-events-from-proceeding-series.csv'
fieldnames = ['name', 'start', 'end', 'acronym', 'location', 'number', 'sponsor', 'theme', 'proceedings-title', 'doi']

# prepare output file
with open(filename, 'w') as csvfile:
    file = csv.DictWriter(csvfile, fieldnames = fieldnames)
    file.writeheader()

    # call crossref api
    response = requests.get("https://api.crossref.org/types/proceedings-series/works?rows="+str(row)).json()

    # get all entries
    for i in range(1, int(stopId), 1):

        # read the metadata of one event from response
        metadata = response['message']['items'][i]['event']

        # add the title and doi of the proceeding
        metadata['proceedings-title'] = response['message']['items'][i]['title']
        metadata['doi'] = response['message']['items'][i]['DOI']

        # write metadata to file
        file.writerow(metadata)
