#!/bin/bash

[[ "$1" == "" ]] && {
    echo "Usage: ./bpc-prepare.sh src.list"
    exit
}

rm -rf ./bpc-test
cp -r ../src/Monolog/Test Monolog/
rsync -a                        \
      --exclude=".*"            \
      -f"+ */"                  \
      -f"- *"                   \
      ./                        \
      ./bpc-test
for i in `cat $1`
do
    if [[ "$i" == \#* ]]
    then
        echo $i
    else
        filename=`basename -- $i`
        if [ "${filename##*.}" == "php" ]
        then
            echo "phptobpc $i"
            phptobpc $i > ./bpc-test/$i
        else
            echo "cp       $i"
            cp $i ./bpc-test/$i
        fi
    fi
done
cp run-test.php $1 Makefile ./bpc-test/
