#!/bin/sh
for file in `find Package/ |grep '.php'`; do
	sed -i 's/\\Library\\//g' $file
	sed -i 's/Library\\//g' $file
done

echo "Finished"
