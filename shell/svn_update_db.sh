SQL_FILE=$1 #传值sql文件所在文件夹或者sql文件的路径名. 此处以svn生成的记录为操作.

HOSTNAME="localhost"
PORT="3306"
USERNAME="root"
PASSWORD="root"
DBNAME="shop"

if [ -f ${SQL_FILE} ];then
	act_shell="cat ${SQL_FILE}"
	SQL_DIR="/home/wwwroot/ibk/ibk/"
fi

if [ -d ${SQL_FILE} ];then
	act_shell="ls ${SQL_FILE}"
	SQL_DIR="/home/wwwroot/ibk/ibk/sql/"
fi

for line in `${act_shell}`;
do
	line=$(echo ${line#* })
	line=$(echo ${line#*“})
	line=${line%”*}
	file_extend=${line##*.} #获取文件扩展名
	if [ ! "${file_extend}" == 'sql' ]; then
		echo '这不是sql文件' 
		echo '---------------------------------------------------';
		continue
	fi
	file_path="${SQL_DIR}${line}";
	if [ -f ${file_path} ]; then
		echo ${file_path}
		ACT_SQL="source ${file_path}"
		ACT_RESULT=`mysql -h${HOSTNAME} -P${PORT} -u${USERNAME} -p${PASSWORD} ${DBNAME} -e "${ACT_SQL}"`
		echo ${ACT_RESULT}
	fi
	echo '---------------------------------------------------';
done
