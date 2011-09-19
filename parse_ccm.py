#!/usr/bin/env python
# Generates the relevant SQL queries from the csv
# Some non-trivial processing involved so it can't be imported directly

import csv

# To use the sql-generating part:
# Uncomment lines 12, 22 and 24 (and comment out lines after 24)
# ./parse_ccm.py > ccm.sql
# Then edit the SQL and delete the last trailing comma

#print "INSERT INTO `ssuns_2011`.`country_committee_matrix` (`country_id`, `committee_id`, `num_delegates`) VALUES",

reader = csv.reader(open('ccm.csv', 'rt'))
country_id = 1 # It starts indexing from 1 for some reason
for row in reader:
    # Ignore the first row
    committees = row[1:]
    for committee, num_delegates in enumerate(committees):
        if num_delegates == '':
            num_delegates = 0
        #print "('%d', '%s', '%s')," % (country_id, committee, num_delegates)
    country_id += 1
#print ";"

# Country-generating part, comment out if not needed
reader = csv.reader(open('ccm.csv', 'rt'))
for row in reader:
    print "'%s'," % row[0].replace("'", "\\'")
