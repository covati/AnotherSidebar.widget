#!/bin/sh
# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
# Used to pull down a project listing
# from a confluence projetc listing
# which is then parsed for display
# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-

# How old your local parsed project can be
MAX_AGE_MINUTES=240

# Username & Password for the wiki
USERNAME_FILE=.wikiusername
PASSWORD_FILE=.wikipassword

# Page to load
PAGE_ID=65473987
PAGE_URL=https://wiki.mapp.tools/pages/viewpage.action?pageId=$PAGE_ID

# Widget Directory
WIDGET_DIR=AnotherSidebar.widget/Status.widget

# For reduced status updates
STATUS_FILE=data/current_status.txt
# For full project status updates
FULL_STATUS_FILE=data/full_current_status.txt

# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
# Start execution
# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-

# This is launched from the main widgets directory
cd $WIDGET_DIR

# Check that you have created your PW file
if [ ! -f $PASSWORD_FILE ];
	then
	echo No PW file - please create $PWD/$PASSWORD_FILE
	exit;
fi
# Check that you have created your username file
if [ ! -f $USERNAME_FILE ];
	then
	echo No user file - please create $PWD/$USERNAME_FILE
	exit;
fi
FILE=`find $STATUS_FILE -mmin -$MAX_AGE_MINUTES`

# if the file is too old then, curl the new one and 
# parse it to get an updated status file
if [ -z "$FILE" ];
	then
	echo "Pulling update at `date`" >> data/sidebar_status.log
	HTML_PAGE=data/page.html

    USERNAME=`cat $USERNAME_FILE`
    PASSWORD=`cat $PASSWORD_FILE`

	curl -sL -o $HTML_PAGE -u "$USERNAME:$PASSWORD" $PAGE_URL

	php parse_it.php $HTML_PAGE $STATUS_FILE $FULL_STATUS_FILE
fi
cat $STATUS_FILE
