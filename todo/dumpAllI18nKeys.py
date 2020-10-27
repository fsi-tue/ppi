#!/usr/bin/python
import os, re
i18nKey = re.compile('i18n->get\(.*?\)')
resultSet = set()
texts = {}

helpContent = ['faqHelpContent', 'lecturesHelpContent', 'downloadHelpContent', 'uploadHelpContent', 'asktokensHelpContent', 'userslistHelpContent', 'lectureslistHelpContent', 'examprotocolslistHelpContent', 'viewexamprotocolHelpContent', 'logeventsHelpContent', 'changepasswordHelpContent', 'impressumHelpContent', 'loginHelpContent', 'recoveryHelpContent', 'createHelpContent', 'activateHelpContent']
helpTitles = ['lecturesHelp', 'downloadHelp', 'uploadHelp', 'asktokensHelp', 'userslistHelp', 'lectureslistHelp', 'examprotocolslistHelp', 'viewexamprotocolHelp', 'logeventsHelp', 'changepasswordHelp', 'impressumHelp', 'loginHelp', 'recoveryHelp', 'createHelp', 'activateHelp']
uploadReplies=['uploadReply1', 'uploadReply2', 'uploadReply3', 'uploadReply4', 'uploadReply5', 'uploadReply6', 'uploadReply7', 'uploadReply8', 'uploadReply9']
keysToInclude= helpContent + helpTitles + uploadReplies
keysToExclude=['uploadReply . $i', '$currentPage . Help', '$page . HelpContent']

def parseI18n(filecontent):
    values = {}
    filecontent = filecontent.replace('\r', '')
    lines = filecontent.split('\n')
    for line in lines:
        if not '=' in line:
            continue
        s = line.split('=')
        key = s[0]
        value = s[1]
        values[key] = value
    return values

def printval(key, texts, lang):
    currentTexts = texts[lang]
    if key in currentTexts:
        print(key + '=' + currentTexts[key])
    else:
        print(key + '=')

for root, dirs, files in os.walk("../"):
    path = root.split(os.sep)
    for f in files:
        if f.endswith(".php"):
            phpFile = open(root + os.sep + f, "r")
            content = phpFile.read()
            phpFile.close()
            matches = i18nKey.findall(content)
            for ma in matches:
                s = ma.replace("'", "")
                s = s.replace('"', '')
                s = s.replace("i18n->get(", '')
                s = s.replace(")", '')
                #resultSet.add(s + '= (' + f + ')')
                resultSet.add(s)
        if f == "de.txt":
            phpFile = open(root + os.sep + f, "r")
            content = phpFile.read()
            phpFile.close()
            texts['de'] = parseI18n(content)
        if f == "en.txt":
            phpFile = open(root + os.sep + f, "r")
            content = phpFile.read()
            phpFile.close()
            texts['en'] = parseI18n(content)

resultArray = list(resultSet-set(keysToExclude))
resultArray = resultArray + keysToInclude
resultArray.sort()

for r in resultArray:
    printval(r, texts, 'de')
    
print('\n\n\n')

for r in resultArray:
    printval(r, texts, 'en')
