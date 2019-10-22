#!/bin/sh

# Build snapshots
rm ./storage/last/dump-layer*
cd ./core
php build.php

# Compile
cd ../storage/last/
convert -delay 20 -loop 0 dump-layer*.png anim.gif

# Show
google-chrome anim.gif
#cp anim.gif ../../
