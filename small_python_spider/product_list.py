#coding=utf-8
from urllib.request import urlopen
from urllib.error import HTTPError
from bs4 import BeautifulSoup
import os
import re
# http://www.86pla.com/ 中国塑料机械网分类抓取

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
    output = open(r'/home/kung/python3.5/roll_dice/product_class2.sql','a')
    for txt in txt_list:
        txt = txt + "\n"
        output.write(txt)
    output.close()


url = 'http://www.86pla.com'
bsObj = getbsObj(url)
i = 146
sql_list =[]

ulObj = bsObj.find('div',{'class':'productLeftBot'})
liObj = ulObj.findAll('li')
for li_item in liObj:
    for dl_item in li_item.findAll('dl'):
        txt_parent = dl_item.find('dt').get_text()
        i = i + 1
        pid = i
        sql_text = "insert into 76web_product_class set id = "+str(i)+", parent_id = 2,name='"+txt_parent+"',grade=2;"
        sql_list.append(sql_text)
        dd_item_list = dl_item.findAll('dd')
        for dd_item in dd_item_list:
            list_a = dd_item.findAll('a')
            for item_a in list_a:
                text_a = item_a.get_text()
                i = i + 1
                sql_text = "insert into 76web_product_class set id = " + str(i) + ", parent_id = "+str(pid)+",name='" + text_a + "',grade=3;"
                sql_list.append(sql_text)

writein(sql_list)