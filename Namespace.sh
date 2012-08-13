#!/bin/sh
for file in `find Package/`; do
	echo $file;
     	#sed 's/FINDSTRING/REPLACESTRING/g' $fl.old > $fl
	#rm -f $fl.old
done
