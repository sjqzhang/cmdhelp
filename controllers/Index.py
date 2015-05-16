#!/usr/bin/env python
# -*- coding:utf8 -*-
__author__ = 'xiaozhang'



from codeigniter.system.core.CI_Cache import CI_Cache

import re

class Index:


    def search(self,keyword):
        rows=self.app.db.query("select * from cmdhelp where cmd='%s'" % keyword)
        rs=[]
        for row in rows:
            rs.append(str(row['id'])+'. '+unicode(row['cmdinfo'])+"      "+row['description'])
        return "<brbr>".join(rs)

    def list(self):
        rows=self.app.db.query("select * from cmdhelp group by cmd")
        rs=[]
        for row in rows:
            rs.append(row['cmd'])
        return "<brbr>".join(rs)

    def add_file(self,cmdinfo='',file="",description=""):
        if cmdinfo=='':
            return 'cmdinfo is null'
        cmd=re.split(r"\s+",cmdinfo)[0]
        file=file.replace('%','%%')
        data={'cmd':cmd,'cmdinfo':file,'description':description}
        self.app.db.insert('cmdhelp',data)
        return 'success'

    def delete(self,id="-1"):
        count=self.app.db.delete('cmdhelp',{'id':id})
        if count>0:
            return 'success'
        else:
            return 'fail'

    def add(self,cmdinfo='',action='cmd'):
        if cmdinfo=='':
            return 'cmdinfo is null'
        cmd=re.split(r"\s+",cmdinfo)[0]
        description= re.findall(r'\/\/.*',cmdinfo)
        if len(description)>0:
            description=description[0]
            cmdinfo=cmdinfo.replace(description,'')
        else:
            description=''
        cmdinfo=cmdinfo.replace('%','%%')
        data={'cmd':cmd,'cmdinfo':cmdinfo,'description':description}
        count=self.app.db.insert('cmdhelp',data)
        if count>0:
            return 'success'
        else:
            return 'fail'



