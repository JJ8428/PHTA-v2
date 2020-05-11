try:
    from scipy import stats as st
    import matplotlib
    # matplotlib.use('Agg')
    import matplotlib.pyplot as plt
    import os
    import shutil
    import numpy as np
except Exception as e:
    print(e)
    exit(1)

# Extract Info
r0 = open('activeDir', 'r')
activeDir = r0.readline()
r0.close()
r1 = open('users/dirs/' + activeDir + '/tmp/requests', 'r')
r1.readline()
srcData = r1.readline().replace("\n", "")
count1 = int(r1.readline())
count2 = int(r1.readline())
saveAs = r1.readline().replace("\n", "")
userInput = []
for line in r1.readlines():
    line = line.replace(', ', ',').replace('^M', "").replace("\n", "").replace("\r", "").split(",")
    for a in range(1, line.__len__()):
        line[a] = float(line[a])
    userInput.append(line)
r1.close()

# Clean up before itself
filelist = [ f for f in os.listdir('users/dirs/' + activeDir + '/results')]
for f in filelist:
    os.remove(os.path.join('users/dirs/' + activeDir + '/results', f))

# Extract the data from the Source Data file
r2 = open(srcData, 'r')
r2.readline()
column = r2.readline().replace(', ', ',').replace("^M", "").replace("\n", "").replace("\r", "").split(",")
data = []
nullCount = 0
linecount = 0
for line in r2.readlines():
    linecount = linecount + 1
    if line.__contains__(',,,,,') or line.__eq__('') or line.__eq__("\n"):
        continue
    line = line.replace(', ', ',').replace('^M', "").replace("\n", "").replace("\r", "").split(",")
    for a in range(count1 - 1, count2):
        try:
            line[a] = float(line[a])
        except Exception as e:
            '''print(line)
            print('.')
            print(linecount)
            print('.')
            print(a)
            print('.')
            print(e)'''
            line[a] = 0
    if line[0] == '' or line[0].__len__() == 0:
        line[0] = 'null' + str(nullCount)
        nullCount += 1
    data.append(line)
r2.close()

# Do the calculations for R and P values
RP = []
for a in range(0, userInput.__len__()):
    tmp = []
    for b in range(0, data.__len__()):
        calc = st.pearsonr(userInput[a][1:], data[b][count1-1:count2])
        tmp.append(calc)
    RP.append(tmp)

# Write the data
file = 'users/dirs/' +  activeDir + '/results/' + saveAs + '.csv'
w0 = open(file, 'w')
for a in range(0, userInput.__len__()):
    w0.write((str(userInput[a][0]) + ","))
    for b in range(1, count1 - 1):
        w0.write(",")
    for c in range(count1, count2 + 1):
        w0.write(str(userInput[a][c - count1 + 1]) + ",")
    w0.write("\n")
for a in range(0, column.__len__()):
    w0.write(column[a] + ',')
w0.write('P Values,')
for a in range(0, userInput.__len__()):
    w0.write(userInput[a][0] + ',')
w0.write('R Values,')
for a in range(0, userInput.__len__()):
    w0.write(userInput[a][0])
    if (a != userInput.__len__() - 1):
        w0.write(',')
    else:
        w0.write("\n")

# Transcribe the data
for a in range(0, data.__len__()):
    for b in range(0, data[a].__len__()):
        w0.write(str(data[a][b]) + ',')
        if b == data[a].__len__() - 1:
            w0.write(',')
    for b in range(0, RP.__len__()):
        w0.write(str(RP[b][a][1]) + ',')
    w0.write(',')
    for b in range(0, RP.__len__()):
        w0.write(str(RP[b][a][0]))
        if b != RP.__len__() - 1:
            w0.write(',')
    w0.write("\n")
w0.close()

# Generate the plots
R = []
for a in range(0, RP.__len__()):
    tmp = []
    for b in range(0, RP[a].__len__()):
        if str(RP[a][b][0]).__contains__('e'):
            tmp.append(0)
        else:
            tmp.append(RP[a][b][0])
    R.append(tmp)
for a in range(0, R.__len__()):
    for b in range(0, R.__len__()):
        if a != b:
            fig, ax = plt.subplots()
            ax.scatter(R[a], R[b])
            plt.grid(True)
            title = userInput[a][0] + '_vs_' + userInput[b][0]
            ax.set_title(title)
            ax.set_xlabel(userInput[a][0])
            ax.set_ylabel(userInput[b][0])
            plt.savefig('users/dirs/' +  activeDir + '/results/' + title + '.png')
            # plt.show()
shutil.make_archive('users/dirs/' + activeDir + '/zip/' + saveAs, 'zip', 'users/dirs/' +  activeDir + '/results')

# Clean up after itself
filelist = [f for f in os.listdir('users/dirs/' + activeDir + '/results')]
for f in filelist:
    os.remove(os.path.join('users/dirs/' + activeDir + '/results', f))