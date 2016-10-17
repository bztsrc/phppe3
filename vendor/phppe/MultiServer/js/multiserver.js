pe.multiserver = {
    add: function()
    {
        var msbg = document.getElementById('msbg');
        var msbox = document.getElementById('msbox');
        if(msbg == null) {
            //! create background div
            msbg = document.createElement('DIV');
            msbg.setAttribute('id', 'msbg');
            msbg.setAttribute('style', 'position:fixed;display:table-cell;top:0px;left:0px;width:100%;height:100%;z-index:2001;background:#000;opacity:0.4;visibility:hidden;');
            msbg.setAttribute('onclick', 'pe.multiserver.close();');
            document.body.appendChild(msbg);
            //! create editor box iframe
            msbox = document.createElement('DIV');
            msbox.setAttribute('id', 'msbox');
            msbox.setAttribute('style', 'position:fixed;display:table-cell;top:30%;left:30%;width:40%;height:280px;z-index:2002;background:rgba(64,64,64,0.9) !important;visibility:hidden;overflow:hidden;border:0px;opacity:0.9;');
            msbox.setAttribute('scrolling', 'no');
            msbox.innerHTML="<h3>"+L("Add New Server")+"</h3><label>"+L("Name")+"<br><input type='text' class='input' name='ms_id'></label>"+
            "<label>"+L("Username")+"<br><input type='text' class='input' name='ms_user'></label>"+
            "<label>"+L("Host")+"<br><input type='text' class='input' name='ms_host' placeholder='localhost'></label>"+
            "<label>"+L("Port")+"<br><input type='number' class='input' value='22' name='ms_port'></label>"+
            "<label>"+L("Identity")+"<br><textarea class='input' name='ms_identity'></textarea></label>"+
            "<label>"+L("Path")+"<br><input type='text' class='input' name='ms_path' placeholder='/var/www/localhost'><br><br><input type='button' class='button' value='"+L("Save")+"' onclick='pe.multiserver.save(this);'></label>";
            document.body.appendChild(msbox);
        }
        msbg.style.visibility='visible';
        msbox.style.visibility='visible';
    },

    close: function()
    {
        var msbg = document.getElementById('msbg');
        var msbox = document.getElementById('msbox');
        msbg.style.visibility='hidden';
        msbox.style.visibility='hidden';
    },

    save: function(btn)
    {
        if( window.XMLHttpRequest ) {
            var form=btn.parentNode.parentNode.querySelectorAll("[class=input]"),data=new FormData;
            var i, s=null, t='', r = new XMLHttpRequest();
            var url='ms/add';
            for(i=0;i<form.length;i++){
                data.append(form[i].name, form[i].value);
            }
            r.open('POST', url, true);
            r.onload = function(e) {
            try {
                if(r.status==200) {
                    document.location.href=document.location.href;
                }else console.error('HTTP-E: '+r.status+' '+url);
            } catch(e) {
                console.error('HTTP-E: '+e);
            }
            };
            r.send(data);
        } else return;

    },

    remove: function(id)
    {
        if( window.XMLHttpRequest ) {
            if(!confirm(L("sure"))) return;
            var i, s=null, t='', r = new XMLHttpRequest();
            var url='ms/remove?item='+id;
            r.open('GET', url, true);
            r.onload = function(e) {
            try {
                if(r.status==200) {
                    document.location.href=document.location.href;
                }else console.error('HTTP-E: '+r.status+' '+url);
            } catch(e) {
                console.error('HTTP-E: '+e);
            }
            };
            r.send(null);
        } else return;
    },

    set: function(id)
    {
        if( window.XMLHttpRequest ) {
            var i, s=null, t='', r = new XMLHttpRequest();
            var url='ms/set?item='+id;
            r.open('GET', url, true);
            r.onload = function(e) {
            try {
                if(r.status==200) {
                    document.location.href=document.location.href;
                }else console.error('HTTP-E: '+r.status+' '+url);
            } catch(e) {
                console.error('HTTP-E: '+e);
            }
            };
            r.send(null);
        } else return;
    },

};
