#!/usr/bin/env python
# -*- coding:utf8 -*-
__author__ = 'xiaozhang'



from codeigniter.system.core.CI_Cache import CI_Cache

import os
import re

class Index:


    def upload(self,**kwargs):
        file=kwargs['file']
        open('upload/'+file.filename,'wb').write(file.file.read())
        return 'success'

    def download(self,**kwargs):
        file=kwargs['file']
        return open(file,'rb').read()

    def listfile(self,**kwargs):
        return "\n".join(os.listdir('upload'))



    def search(self,keyword):
        rs=[]

        if keyword.startswith('http'):
            http=os.popen('w3m '+'"'+keyword+'"')
            return http.read().decode('utf-8')

        if keyword.startswith('%') and keyword.endswith('%'):
            rows=self.app.db.query("select * from cmdhelp where cmd like '%s' or cmdinfo like '%s'" %( keyword,keyword))
            for row in rows:
                rs.append(str(row['id'])+'. '+unicode(row['cmdinfo'])+"      "+row['description'])
            return "<brbr>".join(rs)
        elif keyword.startswith('%') or keyword.endswith('%'):
            keyword='%'+keyword+'%'
            rows=self.app.db.query("select * from cmdhelp where cmd like '%s' or cmdinfo like '%s'" %( keyword,keyword))
            for row in rows:
                rs.append(str(row['id'])+'. '+unicode(row['cmd']))
            return "<brbr>".join(rs)
        else:
            rows=self.app.db.query("select * from cmdhelp where cmd='%s'" % keyword)
            for row in rows:
                rs.append(str(row['id'])+'. '+unicode(row['cmdinfo'])+"      "+row['description'])
            return "<brbr>".join(rs)

    def list(self):
        rows=self.app.db.query("select * from cmdhelp group by cmd")
        rs=[]
        for row in rows:
            rs.append(row['cmd'])
        return "<brbr>".join(rs)

    def add_file(self,cmd='',cmdinfo='',description=""):
        if cmdinfo=='':
            return 'cmdinfo is null'
        #cmdinfo=cmdinfo.replace('%','%%')
        data={'cmd':cmd,'cmdinfo':cmdinfo,'description':description}
        self.app.db.insert('cmdhelp',data)
        return 'success'
    def get(self,id="-1"):
        rows=self.app.db.ar().select('cmdinfo').table('cmdhelp').where({'id':id}).get()

        if len(rows)>0:
            return rows.pop()['cmdinfo']
    def delfile(self,id="-1"):
        path='upload/'+id
        if os.path.exists(path):
            os.remove(path)
            return "sucess"
        else:
            return "Not Found"

    def delete(self,id="-1"):
        count=self.app.db.delete('cmdhelp',{'id':id})
        if count>0:
            return 'success'
        else:
            return 'fail'

    def upgrade(self):
        return open('cmd').read()

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



