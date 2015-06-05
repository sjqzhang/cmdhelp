#!/usr/bin/python

server_url="127.0.0.1:8044"

import sys,os,urllib2,urllib

import re



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
    elif action=='list' or action=='l':
        result= url_fetch('http://%s/list'%server_url)
        if result=='':
            print "not found"
        result=re.sub(r'^\"|\"$',"",result)
        rs= re.split(r'\<brbr\>',result)
        for r in rs:
            print str(r).decode('utf-8')
    elif action=='del':
        if len(sys.argv)>2:
            print url_fetch('http://%s/delete'%server_url,{'id':sys.argv[2]})
        else:
            print ""
    elif action=='file' or action=='addfile':
        if len(sys.argv)<3:
            print "cmd file command file name"
            return
        filepath=sys.argv[3]
        cmd=sys.argv[2]
        content=''
        if os.path.exists(filepath):
            content=open(filepath,'r').read()

        if content=='':
            print 'read error'
            return

        print url_fetch('http://%s/add'%server_url,{'cmdinfo':cmd+' '+content})

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
        result=re.sub(r'^\"|\"$',"",result)
        rs= re.split(r'\<brbr\>',result)
        for r in rs:
            print r.replace("\\r\\n","\n").replace("\\n","\n")
if len(sys.argv)<2:
    help()
    sys.exit(0)
action=sys.argv[1]


#print sys.argv

if __name__ == '__main__':
    main(action,server_url)









#url.url_fetch('http://127.0.0.1:8005/add',{'cmdinfo':"rm -rf abc"})








