#!/usr/bin/python
#encoding=utf-8
server_url="127.0.0.1:8044"

import sys,os,urllib2,urllib

import time
import re
import platform




def download(filename):
    data={'file':filename}
    data=urllib.urlencode(data)
    http_url='http://%s/Index/download?%s' % (server_url,data)
    conn = urllib2.urlopen(http_url)
    f = open(filename,'wb')
    f.write(conn.read())
    f.close()

def upload(filepath):
    boundary = '----------%s' % hex(int(time.time() * 1000))
    data = []
    data.append('--%s' % boundary)
    fr=open(filepath,'rb')
    filename=os.path.basename(filepath)
    data.append('Content-Disposition: form-data; name="%s"; filename="%s"' % ('file',filename))
    data.append('Content-Type: %s\r\n' % 'image/png')
    data.append(fr.read())
    fr.close()
    data.append('--%s--\r\n' % boundary)


    http_url='http://%s/Index/upload' % server_url
    # http_url='http://172.16.136.98:8005/Index/index'
    http_body='\r\n'.join(data)
    try:
        req=urllib2.Request(http_url, data=http_body)
        req.add_header('Content-Type', 'multipart/form-data; boundary=%s' % boundary)
        req.add_header('User-Agent','Mozilla/5.0')
        req.add_header('Referer','http://remotserver.com/')
        resp = urllib2.urlopen(req, timeout=5)
        qrcont=resp.read()
        print qrcont
    except Exception,e:
        print 'http error'


def sysout(str):
    out=""
    out=re.subn(r'\r','',str)
    if isinstance(str,unicode):
        out=unicode.encode(str,'utf-8')
    else:
        out=str.decode("utf-8")
    out=re.sub(r'\r','',out)
    if platform.system().lower()=='windows':
        rs= re.split(r'\<brbr\>',out)
        out="\n".join(rs)
        out=out.encode("GB18030")
    else:
            rs= re.split(r'\<brbr\>',out)

            out="\n".join(rs)
    print out


def url_fetch(url,data=None):
        html='';
        # print(url)
        try:
            headers = {
                'User-Agent':'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.6) Gecko/20091201 Firefox/3.5.6'
            }
            if data!=None:
                data=urllib.urlencode(data)
            req = urllib2.Request(
                url =url,
                headers = headers,
                data=data
            )
            html=urllib2.urlopen(req,timeout=15).read()
            charset=re.compile(r'<meta[^>]*charset=[\'\"]*?([a-z0-8\-]+)[\'\"]?[^>]*?>',re.IGNORECASE).findall(html)
            if len(charset) >0:
                if charset[0]=='gb2312':
                    charset[0]='gbk'
                html=unicode(html,charset[0])
            # print(html)
        except Exception as e:
            print e
        return html

def help():
    help='''
help:
cmd add command 	# save the command to server
cmd list 		# show server command
cmd search/help command 	# search command from server
cmd addfile/file command filename # upload configfile to server
cmd del cmd No      # delete cmd for exmale: cmd del 1
'''
    print help
    sys.exit(0)


def main(action,server_url):
    if action=='add' or action=='a':
        cmdinfo= str( " ".join(sys.argv[2:]))
        try:
            cmdinfo=unicode(cmdinfo,"utf-8")
        except Exception as e:
            try:
                cmdinfo=unicode(cmdinfo,"gbk").encode("utf-8")
            except Exception as ee:
                print "error param"
                sys.exit(0)
        print url_fetch('http://%s/add'%server_url,{'cmdinfo':" ".join(sys.argv[2:])})
    elif action=='listfile':
        result= url_fetch('http://%s/listfile'%server_url)
        if result=='':
            print "not found"
        sysout(result)
    elif action=='list' or action=='l':
        result= url_fetch('http://%s/list'%server_url)
        if result=='':
            print "not found"
        sysout(result)
    elif action=='get':
        if len(sys.argv)>2:
            print url_fetch('http://%s/get'%server_url,{'id':sys.argv[2]})
        else:
            print ""
    elif action=='delfile':
        if len(sys.argv)>2:
            print url_fetch('http://%s/delfile'%server_url,{'id':sys.argv[2]})
        else:
            print ""
    elif action=='del':
        if len(sys.argv)>2:
            print url_fetch('http://%s/delete'%server_url,{'id':sys.argv[2]})
        else:
            print ""
    elif action=='download':
        download(sys.argv[2])
    elif action=='upload':
        upload(sys.argv[2])
    elif action=='file' or action=='addfile':
        if len(sys.argv)<3:
            print "cmd file command file name"
            return
        if len(sys.argv)==5:
            description=sys.argv[4]
        else:
            description=''
        filepath=sys.argv[3]
        cmd=sys.argv[2]
        content=''
        if os.path.exists(filepath):
            content=open(filepath,'r').read()

        if content=='':
            print 'read error'
            return

        print url_fetch('http://%s/add_file'%server_url,{'cmd':cmd, 'cmdinfo':content,'description':description})

    elif action=='upgrade':
        result= url_fetch('http://%s/upgrade'%server_url)
        if result=='':
            print "not found"
            sys.exit(0)
        result=re.sub(r'^\"|\"$',"",result)
        rs= re.split(r'\<brbr\>',result)
        open('/bin/cmd','w').write(result)

    elif action=='gen' or action=='update':
        result= url_fetch('http://%s/list'%server_url)
        if result=='':
            print "not found"
            sys.exit(0)
        result=re.sub(r'^\"|\"$',"",result)
        rs= re.split(r'\<brbr\>',result)
        cmds=['update','upgrade','add','addfile','list','search','del']
        rs=rs+cmds
        cpl='complete -W "'+ ' '.join(rs) +'" cmd'
        tmp='''
        cat > /tmp/cmdhelp.sh<<EOF

%s

EOF''' % cpl
        os.system(tmp)
        #os.system('sh /tmp/cmdhelp.sh')
        #os.system('sh /tmp/cmdhelp.sh')
        #print cpl
        print('source /tmp/cmdhelp.sh')

    elif action=='--help' or action=='-h':

        help()
    else:
        result=''
        if len(sys.argv)>2:
            result= url_fetch('http://%s/search'%server_url,{'keyword':sys.argv[2]})
        else:
            result= url_fetch('http://%s/search'%server_url,{'keyword':action})
        if result=='':
            print "not found"
        # print result.decode('utf-8').encode('GB18030')
        sysout(result)
if len(sys.argv)<2:
    help()
    sys.exit(0)
action=sys.argv[1]


#print sys.argv

if __name__ == '__main__':
    main(action,server_url)









#url.url_fetch('http://127.0.0.1:8005/add',{'cmdinfo':"rm -rf abc"})








