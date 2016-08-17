#! /bin/bash
: << !
Move the file into a folder which is editted when svn update.
!
svn update > update.txt &&
CURRPATH=$(pwd)
FILENAME='update.txt'
UPDATEPATH=$CURRPATH'/update_dir'
if [ -d "$UPDATEPATH" ]
then
    rm -rf $UPDATEPATH
fi
sed -i '1d;$d' $FILENAME #去除第一条和最后一条svn生成的记录
while read line
do
    FILEPATH=$(echo ${line#* })  #去除每条记录中空格之前的部分
    if [ ! -d $UPDATEPATH ] ; then #判断更新目录是否存在,若不存在则创建
	echo $UPDATEPATH
	mkdir "$UPDATEPATH"
    fi
    sp='/'
    result=$(echo $FILEPATH | grep "${sp}") #判断更新文件中是否存在/
    result=$(echo $result)  #去除空格
#    if [ "$result"!=" " ] 这是一个错误的写法
    if [ -n "$result" ]  #判断不为空
    then    
   	 if [ -f $FILEPATH ]; then #判断文件是否存在
		cd $UPDATEPATH
		mkdir -p ${FILEPATH%/*} #去除文件最后一个/右侧的文件名,生成路径
		cd ..
         fi
    fi
#    echo $FILEPATH
    cp $FILEPATH $UPDATEPATH'/'$FILEPATH #复制文件到目标文件夹中
done < $FILENAME
