CREATE TABLE `ecs_hxbb2b_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serial_number` char(16) NOT NULL DEFAULT '0' COMMENT '交易市场流水号',
  `act_code` varchar(10) NOT NULL DEFAULT '' COMMENT '操作编号',
  `code_res` varchar(10) NOT NULL DEFAULT '' COMMENT '银行返回状态码',
  `action` varchar(255) NOT NULL DEFAULT '' COMMENT '操作内容记录',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8

create table ecs_hxb2b_sign_account(
id int(11) not null auto_increment,
is_hx SMALLINT(1) not null default '1' comment '1 华夏银行 2 其他银行',
serial_number varchar(255) not null default '' comment '交易市场流水号',
MerAccountNo varchar(255) not null default '' comment '摊位号',
AccountName varchar(255) not null default '' comment '子账号名称',
AccountProp SMALLINT (1) not null default '0' comment '0 企业 1 个人',
EnterNetBankNo VARCHAR (255) null  comment '企业客户号',
LawName VARCHAR (255) null comment '法人姓名',
CertType SMALLINT (1) not null default '1' comment '1 个人身份证 4 户口簿 5 护照',
CertNo VARCHAR (255) not null default '' comment '证件号码',
PersonName VARCHAR (255) null comment '联系人姓名 企业必填',
OfficeTel VARCHAR (255) null comment '联系电话',
Addr VARCHAR (255) null comment '联系地址',
Email VARCHAR (255) null comment '邮箱',
ZipCode VARCHAR (255) null comment '邮编',
NoteFlag SMALLINT (1) not null default '0' comment '是否接受短信 1 需要 0 不需要',
NotePhone char(11) not null default '0' comment '接受短信手机号码',
CheckFlag SMALLINT (1) not null default '0' comment '是否复核 1需要 0 不需要',
BankTxSerNo VARCHAR (255) null comment '银行返回流水号',
created_at datetime not null default '0000-00-00 00:00:00' comment '创建日期',
updated_at TIMESTAMP not null default CURRENT_TIMESTAMP comment '更改时间',
PRIMARY  key(id)
)engine=innodb default charset=utf8;

create table ecs_hxb2b_sign_account_other(
id int(11) not null auto_increment,
is_hx SMALLINT(1) not null default '2' comment '1 华夏银行 2 其他银行',
serial_number varchar(255) not null default '' comment '交易市场流水号',
MerAccountNo varchar(255) not null default '' comment '摊位号',
AccountName varchar(255) not null default '' comment '子账号名称',
AccountProp SMALLINT (1) not null default '0' comment '0 企业 1 个人',
RelatingAcct VARCHAR (255) null  comment '关联账户名',
InterBankFlag VARCHAR (255) null  comment '跨行标识：0-本行 1-跨行',
RelatingAcctBank VARCHAR (255) null  comment '绑定出金账户开户行',
RelatingAcctBankAddr VARCHAR (255) null  comment '绑定出金账户开户行地址',
RelatingAcctBankCode VARCHAR (255) null  comment '绑定出金账户开户行支付系统行号',
Amt DECIMAL (10,2) not null default '0.0'  comment '总金额',
AmtUse DECIMAL (10,2) not null default '0.0'  comment '可用金额',
PersonName VARCHAR (255) null comment '联系人姓名 企业必填',
OfficeTel VARCHAR (255) null comment '联系电话',
MobileTel VARCHAR (255) null comment '移动电话',
Addr VARCHAR (255) null comment '联系地址',
ZipCode VARCHAR (255) null comment '邮编',
LawName VARCHAR (255) null comment '法人姓名',
LawType VARCHAR (255) null comment '证件类型1 个人身份证 2 军人证、警官证 3 临时证件 4 户口本 5 护照 6 其他',
LawNo VARCHAR (255) not null default '' comment '证件号码',
NoteFlag SMALLINT (1) not null default '0' comment '是否接受短信 1 需要 0 不需要',
NotePhone char(11) not null default '0' comment '接受短信手机号码',
Email VARCHAR (255) null comment '邮箱',
CheckFlag SMALLINT (1) not null default '0' comment '是否复核 1需要 0 不需要',
BankTxSerNo VARCHAR (255) null comment '银行返回流水号',
created_at datetime not null default '0000-00-00 00:00:00' comment '创建日期',
updated_at TIMESTAMP not null default CURRENT_TIMESTAMP comment '更改时间',
PRIMARY  key(id)
)engine=innodb default charset=utf8;


create table ecs_hxb2b_account(
id int(11) not null auto_increment,
serial_number varchar(255) not null default '' comment '交易市场流水号',
AccountNo varchar(255) not null default '' comment '子账号',
MerAccountNo int(11) not null default '0' comment '摊位号',
DealerOperNo VARCHAR (255) not null default '' comment '操作员代码',
ErrorInfo VARCHAR (255) null comment '错误信息',
created_at timestamp not null default CURRENT_TIMESTAMP ,
PRIMARY key(id)
)engine=innodb default charset=utf8;

create table ecs_hxb2b_golden(
id int(11) not null auto_increment,
is_hx SMALLINT(1) not null default '1' comment '1 华夏银行 2 其他银行',
serial_number varchar(255) not null default '' comment '交易市场流水号',
AccountNo varchar(255) not null default '' comment '子账号',
MerAccountNo varchar(255) not null default '' comment '摊位号',
Amt DECIMAL (10,2) not null default '0.0' comment '金额',
PasswordChar VARCHAR (255) not null default '' comment '个人卡支付密码',
InOutStart SMALLINT (1) null  comment '1 他行现金汇款，2他行转账汇款',
PersonName VARCHAR (255) null  comment '汇款人姓名',
AmoutDate VARCHAR (255) null  comment '汇款日期',
BankName  VARCHAR (255) null  comment '汇款银行',
OutAccount  VARCHAR (255) null  comment '汇款账号',
BankTxSerNo VARCHAR (255) null comment '银行流水号',
created_at datetime not null default '0000-00-00 00:00:00',
updated_at timestamp not null default CURRENT_TIMESTAMP ,
PRIMARY key(id)
)engine=innodb default charset=utf8;

create TABLE ecs_hxb2b_dftt(
id int(11) not null auto_increment,
serial_number varchar(255) not null default '' comment '交易市场流水号',
AccountNo varchar(255) not null default '' comment '子账号',
MerAccountNo varchar(255) not null default '' comment '摊位号',
Amt DECIMAL (10,2) not null default '0.0' comment '金额',
channelType SMALLINT (1) not null default '0' comment '0 快速到账 1 跨行清算',
BankTxSerNo VARCHAR (255) null comment '银行流水号',
created_at datetime not null default '0000-00-00 00:00:00',
updated_at timestamp not null default CURRENT_TIMESTAMP ,
PRIMARY key(id)
)engine=innodb default charset=utf8;

create TABLE ecs_hxb2b_chujin(
id int(11) not null auto_increment,
serial_number varchar(255) null comment '交易市场流水号',
BankTxSerNo VARCHAR (255) not null comment '银行流水号',
AccountNo varchar(255) not null default '' comment '子账号',
MerAccountNo varchar(255) not null default '' comment '摊位号',
Amt DECIMAL (10,2) not null default '0.0' comment '金额',
channelType SMALLINT (1) not null default '0' comment '0 快速到账 1 跨行清算',
Result SMALLINT (1) not null default '2' comment ' 0 拒绝 1 通过 2 待处理',
Balance DECIMAL (10,2) not null default '0.0' comment '银行子账号当前余额',
BalanceUse DECIMAL (10,2) not null default '0.0' comment '银行子账号当前可用余额',
BankTxSerNo2 VARCHAR (255) null comment '银行流水号',
created_at datetime not null default '0000-00-00 00:00:00',
updated_at timestamp not null default CURRENT_TIMESTAMP ,
PRIMARY key(id)
)engine=innodb default charset=utf8;

create TABLE ecs_hxb2b_surrender(
id int(11) not null auto_increment,
serial_number varchar(255) null comment '交易市场流水号',
BankTxSerNo VARCHAR (255) not null comment '银行流水号',
AccountNo varchar(255) not null default '' comment '子账号',
MerAccountNo varchar(255) not null default '' comment '摊位号',
created_at datetime not null default '0000-00-00 00:00:00',
updated_at timestamp not null default CURRENT_TIMESTAMP ,
PRIMARY key(id)
)engine=innodb default charset=utf8;

create TABLE ecs_hxb2b_fail_liquidation(
id int(11) not null auto_increment,
serial_number varchar(255) null comment '交易市场流水号',
AccountNo varchar(255) not null default '' comment '子账号',
MerAccountNo varchar(255) not null default '' comment '摊位号',
Amt DECIMAL (10,2) not null default '0.0' comment '金额',
Type varchar(10) not null default '' comment '交易类型 01 正常交易 02 冻结资金',
Flag char(2) not null default '' comment '借贷标示 1 借 2贷',
Remark varchar(255) null comment '备注',
Workday VARCHAR (255) null comment '工作日',
created_at TIMESTAMP not null default CURRENT_TIMESTAMP ,
PRIMARY key(id)
)engine=innodb default charset=utf8;

alter table ecs_supplier_money_log add COLUMN created_at TIMESTAMP not null default CURRENT_TIMESTAMP;

create TABLE ecs_hxb2b_fail_accountCheck(
id int(11) not null auto_increment,
serial_number varchar(255) null comment '交易市场流水号',
AccountNo varchar(255) not null default '' comment '子账号',
MerAccountNo varchar(255) not null default '' comment '摊位号',
Amt DECIMAL (10,2) not null default '0.0' comment '金额',
AmtUse DECIMAL (10,2) not null default '0.0' comment '可用金额',
BankAmt DECIMAL (10,2) not null default '0.0' comment '银行金额',
BankAmtUse DECIMAL (10,2) not null default '0.0' comment '银行可用金额',
Workday VARCHAR (255) null comment '工作日',
created_at TIMESTAMP not null default CURRENT_TIMESTAMP ,
PRIMARY key(id)
)engine=innodb default charset=utf8;

create table ecs_hxb2b_DZ001(
id int(11) not null auto_increment,
serial_number varchar(255) null comment '交易市场流水号',
BankTxSerNo VARCHAR (255) not null comment '银行流水号',
TrnxCode VARCHAR (10) not null default '' comment '操作',
AccountNo varchar(255) not null default '' comment '子账号',
MerAccountNo varchar(255) not null default '' comment '摊位号',
AccountName varchar(255) not null default '' comment '子账号名称',
AccountProp SMALLINT (1) not null default '0' comment '0 企业 1 个人',
Amt DECIMAL (10,2) not null default '0.0' comment '金额',
AmtUse DECIMAL (10,2) not null default '0.0' comment '可用金额',
PersonName VARCHAR (255) null comment '联系人姓名 企业必填',
OfficeTel VARCHAR (255) null comment '联系电话',
MobileTel VARCHAR (255) null comment '移动电话',
Addr VARCHAR (255) null comment '联系地址',
created_at TIMESTAMP not null default CURRENT_TIMESTAMP,
PRIMARY key(id)
)engine=innodb default charset=utf8;

create table ecs_hxb2b_DZ002(
id int(11) not null auto_increment,
serial_number varchar(255) null comment '交易市场流水号',
BankTxSerNo VARCHAR (255) not null comment '银行流水号',
TrnxCode VARCHAR (10) not null default '' comment '操作',
AccountNo varchar(255) not null default '' comment '子账号',
MerAccountNo varchar(255) not null default '' comment '摊位号',
Amt DECIMAL (10,2) not null default '0.0' comment '金额',
Balance DECIMAL (10,2) not null default '0.0' comment '银行子账号当前余额',
BalanceUse DECIMAL (10,2) not null default '0.0' comment '银行子账号当前可用余额',
reject VARCHAR (255) not null default  '' comment '驳回理由',
created_at TIMESTAMP not null default CURRENT_TIMESTAMP,
PRIMARY key(id)
)engine=innodb default charset=utf8;

create table ecs_hxb2b_DZ004(
id int(11) not null auto_increment,
serial_number varchar(255) null comment '交易市场流水号',
BankTxSerNo VARCHAR (255) not null comment '银行流水号',
TrnxCode VARCHAR (10) not null default '' comment '操作',
AccountNo varchar(255) not null default '' comment '子账号',
MerAccountNo varchar(255) not null default '' comment '摊位号',
AccountName varchar(255) not null default '' comment '子账号名称',
created_at TIMESTAMP not null default CURRENT_TIMESTAMP,
PRIMARY key(id)
)engine=innodb default charset=utf8;


