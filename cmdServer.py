#!/usr/bin/python
"""A web.py application powered by gevent"""

from gevent import monkey; monkey.patch_all()
from gevent.pywsgi import WSGIServer
import time
import web
import json

from codeigniter.system.core.CI_Application import CI_Application

ci=CI_Application(application_path=r'./')
port=ci.config['server']['port']
host=ci.config['server']['host']

def application(env, start_response):
    html=''

    code,obj=ci.router.wsgi_route(env)
    # print type(obj)
    if not isinstance(obj,str) and not isinstance(obj,unicode):
        html=json.dumps(obj)
        start_response(str(code), [('Content-Type', 'application/json')])
    else:
        start_response(str(code), [('Content-Type', 'text/html')])
        html=unicode(obj).encode('utf-8')
    return [str(html)]



if __name__ == "__main__":
    print 'Serving on %s...' % port
    WSGIServer((host, port), application).serve_forever()
# app.loader.ctrl("Index").add("ls -alh ")




