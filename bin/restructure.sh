#!/bin/bash
# This is a migration script to migrate the current Fork CMS code to the new structure that will allow installing
# things with composer and give the module developers more freedom of how they want to structure the extra code within
# their module
#
# src
# | Files
# | Core
# | | Backend
# | | Frontend
# | Themes
# | | ThemeName
# | Modules
# | | ModuleName
# | | | Backend
# | | | Frontend
# | Installer
# | Console
while true; do
    read -p "Is this a dry run (y/n)?" yn
    case $yn in
        [Yy]* ) CLEANUP=false; break;;
        [Nn]* ) CLEANUP=true; break;;
        * ) echo "Please answer yes or no.";;
    esac
done

# Set some base paths
ROOT=$(pwd)
OLDSRC=$(pwd)'/src'
NEWSRC=$(pwd)'/newSrc'
MODULESDIR=$NEWSRC'/Modules'
COREDIR=$NEWSRC'/Core'
FILESDIR=$NEWSRC'/Files'
THEMESDIR=$NEWSRC'/Themes'
INSTALLERDIR=$NEWSRC'/Installer'
CONSOLEDIR=$NEWSRC'/Console'
APPLICATIONS=('Frontend' 'Backend')
# Remove the old attempt
rm -rf $NEWSRC

# Create the base directories
mkdir -p $COREDIR'/Backend'
mkdir -p $COREDIR'/Frontend'
mkdir -p $MODULESDIR

cp -r $OLDSRC'/Common' $COREDIR'/Common'
if $CLEANUP ; then rm -r $OLDSRC'/Common'; fi;
cp -r $OLDSRC'/Console' $NEWSRC'/Console'
if $CLEANUP ; then rm -r $OLDSRC'/Console'; fi;
cp -r $OLDSRC'/Frontend/Files' $FILESDIR
if $CLEANUP ; then rm -r $OLDSRC'/Frontend/Files'; fi;
cp -r $OLDSRC'/Frontend/Themes' $THEMESDIR
if $CLEANUP ; then rm -r $OLDSRC'/Frontend/Themes'; fi;
cp -r $OLDSRC'/ForkCMS/Bundle/InstallerBundle' $INSTALLERDIR
if $CLEANUP ; then rm -r $OLDSRC'/ForkCMS/Bundle/InstallerBundle'; fi;

for APPLICATION in ${APPLICATIONS[@]}
do
  for dir in $OLDSRC/$APPLICATION/Modules/*/
  do
      dir=${dir%*/}
      mkdir $MODULESDIR/${dir##*/} &> /dev/null
      cp -r $dir $MODULESDIR/${dir##*/}/$APPLICATION/
      if $CLEANUP ; then rm -r $dir; fi;
  done
done

if $CLEANUP
then
  cp -r $OLDSRC $(pwd)'/oldsrc'
  rm -rf $OLDSRC
  cp -r $NEWSRC $OLDSRC
  rm -rf $NEWSRC
fi
