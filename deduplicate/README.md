# Merging duplicate entries

This script merges duplicate entries in a csv file. I assumes that the first row contains the key, that is duplicate (like an id). All other entries will be merged into a new entry. The old entry will be removed.

# Usage

`php deduplicate.php [input.csv] [output.csv]`

# Example 

## Before

1, test, blubbb

2, test1, blubbb

2, test2, blubbb

2, test3, blubbb

3, test, blubbb

## After

1," test"," blubbb"

2," test2 test1 test3"," blubbb"

3," test"," blubbb"
