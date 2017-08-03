#!/bin/sh

rm -f ./results/last/dump-layer*
#rm -f anim.gif

php ./scripts/cortex.php

#echo "animation"
#convert -delay 20 -loop 0 dump-layer*.png anim.gif
