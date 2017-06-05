#coding=utf-8
import urllib
from urllib.request import urlopen
from urllib.error import HTTPError
from bs4 import BeautifulSoup
import os
import re
import time
import datetime

def getbsObj(url):
    try:
        send_headers = {
            'Host': 'hq.zhaosuliao.com',
            'User-Agent': 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/58.0.3029.110 Chrome/58.0.3029.110 Safari/537.36',
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Connection': 'keep-alive'
        }
        req = urllib.request.Request(url, None, send_headers)
        html = urllib.request.urlopen(req)
    except HTTPError as e:
        return 'http 请求错误'
    try:
        bsObj = BeautifulSoup(html.read(),'lxml')
    except AttributeError as e:
        return 'read 页面错误'
    return bsObj

def writein(txt_list):
    output = open(r'/home/wwwroot/hangqing_spider/today_price_'+datetime.datetime.now().strftime('%Y-%m-%d')+'.sql','a')
    for txt in txt_list:
        output.write(txt)
    output.close()
'''
url = 'http://hq.zhaosuliao.com/price/0_1_0_0_0_1_0_0.html'
bsObj = getbsObj(url)
sql_list =[]
td_texts = []

tableObj = bsObj.find('table',{'class':'pricecenter_list'})
tbodyObj = tableObj.find('tbody')
trObj    = tbodyObj.findAll('tr')
for tr_item in trObj:
    for td_item in tr_item.findAll('td'):
        td_text = td_item.get_text().strip()
        if td_text == '':
            continue
        if td_text == '查看':
            continue
        td_texts.append(td_text)
        td_str = ",".join(td_texts)
        td_texts = []
        print(td_str+"\n")
'''
url = 'http://hq.zhaosuliao.com/price/0_1_0_0_0_1_0_0.html'
bsObj = getbsObj(url)
totalObj = bsObj.find('span',{'class':'total'}).get_text()
p = re.compile(r'\d+')
for m in p.finditer(totalObj):
    total_page = int(m.group())+1

for i in range(1,total_page):
    url = 'http://hq.zhaosuliao.com/price/0_1_0_0_0_'+str(i)+'_0_0.html'
    bsObj = getbsObj(url)
    sql_list = []
    td_texts = []

    tableObj = bsObj.find('table', {'class': 'pricecenter_list'})
    tbodyObj = tableObj.find('tbody')
    trObj = tbodyObj.findAll('tr')
    for tr_item in trObj:
        for td_item in tr_item.findAll('td'):
            td_text = td_item.get_text().strip()
            if td_text == '查看':
                continue
            td_texts.append(td_text)
        td_str = "','".join(td_texts)
        td_str = "'"+td_str+"'"
        td_texts = []
        sql_text = "insert into today_market (id,name,model,company,district,price,unit_price,up_down,price_cond,updated_at) value ("+td_str+");"
        sql_list.append(sql_text + "\n")
    writein(sql_list)
    time.sleep(2)
