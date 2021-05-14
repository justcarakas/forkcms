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
CACHEDIR=$NEWSRC'/Cache'
FILESDIR=$NEWSRC'/Files'
THEMESDIR=$(pwd)'/templates/Themes'
INSTALLERDIR=$NEWSRC'/Installer'
CONSOLEDIR=$NEWSRC'/Console'
APPLICATIONS=('Frontend' 'Backend')
# Remove the old attempt
rm -rf $NEWSRC

# Create the base directories
mkdir -p $COREDIR'/Backend'
mkdir -p $COREDIR'/Frontend'
mkdir -p $CACHEDIR'/Backend'
mkdir -p $CACHEDIR'/Frontend'
mkdir -p $MODULESDIR

cp -a $OLDSRC'/Common' $COREDIR'/Common'
if $CLEANUP ; then rm -r $OLDSRC'/Common'; fi;
cp -a $OLDSRC/ForkCMS $COREDIR'/Common'
if $CLEANUP ; then rm -r $OLDSRC/ForkCMS; fi;
cp -a $OLDSRC'/Console' $NEWSRC'/Console'
if $CLEANUP ; then rm -r $OLDSRC'/Console'; fi;
cp -a $OLDSRC'/Frontend/Files' $FILESDIR
if $CLEANUP ; then rm -r $OLDSRC'/Frontend/Files'; fi;
cp -a $OLDSRC'/Frontend/Themes' $THEMESDIR
if $CLEANUP ; then rm -r $OLDSRC'/Frontend/Themes'; fi;
cp -a $OLDSRC'/ForkCMS/Bundle/InstallerBundle' $INSTALLERDIR
if $CLEANUP ; then rm -r $OLDSRC'/ForkCMS/Bundle/InstallerBundle'; fi;

for APPLICATION in ${APPLICATIONS[@]}
do
  for dir in $OLDSRC/$APPLICATION/Modules/*/
  do
      dir=${dir%*/}
      mkdir $MODULESDIR/${dir##*/} &> /dev/null
      cp -a $dir $MODULESDIR/${dir##*/}/$APPLICATION/
      if $CLEANUP ; then rm -r $dir; fi;
  done
  cp -a $OLDSRC/$APPLICATION/Core $COREDIR/$APPLICATION/
  if $CLEANUP ; then rm -r $OLDSRC/$APPLICATION/Core; fi;
  cp -a $OLDSRC/$APPLICATION/Form $COREDIR/$APPLICATION/
  if $CLEANUP ; then rm -r $OLDSRC/$APPLICATION/Form; fi;
  cp -a $OLDSRC/$APPLICATION/DependencyInjection $COREDIR/$APPLICATION/
  if $CLEANUP ; then rm -r $OLDSRC/$APPLICATION/DependencyInjection; fi;
  cp -a $OLDSRC/$APPLICATION/favicon.ico $COREDIR/$APPLICATION/
  if $CLEANUP ; then rm -r $OLDSRC/$APPLICATION/favicon.ico; fi;
  cp -a $OLDSRC/$APPLICATION/Init.php $COREDIR/$APPLICATION/
  if $CLEANUP ; then rm -r $OLDSRC/$APPLICATION/Init.php; fi;
  cp -a $OLDSRC/$APPLICATION/Cache/* $CACHEDIR/$APPLICATION/
  if $CLEANUP ; then rm -r $OLDSRC/$APPLICATION/Cache; fi;
done

if $CLEANUP
then
  cp -a $OLDSRC $(pwd)'/oldsrc'
  rm -rf $OLDSRC
  cp -a $NEWSRC $OLDSRC
  rm -rf $NEWSRC
fi
