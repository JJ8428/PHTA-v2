import zipfile
import sys
import os

dir1 = sys.argv[1]
dir2 = sys.argv[2]

r0 = open('activeDir', 'r')
activeDir = r0.readline()
r0.close()

# Clean up before itself
filelist = [ f for f in os.listdir('users/dirs/' + activeDir + '/results')]
for f in filelist:
    os.remove(os.path.join('users/dirs/' + activeDir + '/results', f))

# Unzip the file
with zipfile.ZipFile(dir1, 'r') as z:
    z.extractall(dir2)