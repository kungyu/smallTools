#coding=utf-8
from urllib.request import urlopen
from urllib.error import HTTPError
from bs4 import BeautifulSoup
import os
import re
# jc35.com 中国机床网分类抓取

def getbsObj(url):
    try:
        html = urlopen(url)
    except HTTPError as e:
        return None
    try:
        bsObj = BeautifulSoup(html.read(),'lxml')
    except AttributeError as e:
        return None
    return bsObj

def writein(txt_list):
    output = open(r'/home/kung/python3.5/roll_dice/product_class.sql','a')
    for txt in txt_list:
        txt = txt + "\n"
        output.write(txt)
    output.close()


url = 'http://www.jc35.com'
bsObj = getbsObj(url)
i = 3
def getSql(id_name):
    ulObj = bsObj.find('ul',{'id':id_name})
    liObj = ulObj.findAll('li')
    text_list = []
    global i
    for child in  liObj:
        for child_a in child.findAll('a'):
            try:
                child_class = child_a['class']
                class_name = child_class[0]
            except KeyError as e:
                class_name = ''
            i = i + 1
            name = child_a.get_text()
            if class_name == 'title-b':
                fid = i
                sql_text = "insert into 76web_product_class set id = "+str(i)+", parent_id = 1,name='"+name+"',grade=2;"
            else:
                sql_text = "insert into 76web_product_class set id = "+str(i)+", parent_id = "+str(fid)+",name='" + name + "',grade=3;"
            text_list.append(sql_text)
    return text_list

sql_list_0 = getSql('botProductList0')
sql_list_1 = getSql('botProductList1')
sql_list_2 = getSql('botProductList2')
sql_list_3 = getSql('botProductList3')
sql_list = sql_list_0 + sql_list_1 + sql_list_2 + sql_list_3
writein(sql_list)