#!/bin/sh

cd ./storage/last/

convert -delay 20 -loop 0 dump-layer*.png anim.gif

google-chrome anim.gif

#cp anim.gif ../../
