#!/bin/sh
echo "In order to use this package with your own autoloader, please enter fully qualified namespace"
echo "for your framework library."
echo ""
echo "Entering Framework\Library will update packages namespace to Framework\Library\Twilio"
echo ""
echo "Namespace:"

read -r namespace

if [ ${namespace: -1}  == "\\" ]
then
	echo 'Namespace must not end with \!'
	exit
fi


for file in `find Package/ |grep '.php'`; do
	echo "Processing: $file"
	sed -i 's/ \Library\\/ '${namespace//\\/\\\\}'\\/g' $file
	sed -i 's/ Library\\/ '${namespace//\\/\\\\}'\\/g' $file
        sed -i 's/ Library;/ '${namespace//\\/\\\\}';/g' $file
done

echo ""
echo "Finished"
