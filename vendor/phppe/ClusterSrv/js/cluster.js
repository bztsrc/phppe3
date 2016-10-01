pe.cluster = {
    statusbox:null,
    refreshInterval:30000,

    init: function()
    {
        this.statusbox=document.getElementById("pe_cl");
        this.getstatus();
        setInterval("pe.cluster.getstatus();",this.refreshInterval);
    },

    open: function(func)
    {
        if( window.XMLHttpRequest ) {
            pe_p("");
            var i, s=null, t='', r = new XMLHttpRequest();
            var url='cluster/'+func;
            console.log('PE Cluster',func);
            r.open('GET', url, true);
            r.onload = function(e) {
            try {
                if(r.status==200) {
                    console.log(r.responseText);
                }else console.error('HTTP-E: '+r.status+' '+url);
            } catch(e) {
                console.error('HTTP-E: '+e);
            }
            };
            r.send(null);
        } else return;
    },

    getstatus: function()
    {
        if( window.XMLHttpRequest ) {
            var i, s=null, t, r = new XMLHttpRequest();
            var url='cluster';
            r.open('GET', url, true);
            r.onload = function(e) {
            try {
            if(r.status==200) {
                s=JSON.parse(r.responseText);
                pe.cluster.statusbox.nextSibling.style.color=(s.status=='warn'?'#F0F060':(s.status=='error'?'#FF6060':'#60A060'));
                t='<table><tr><td><li onclick="pe.cluster.open(\'flush\');" class="glyphicon glyphicon-refresh" style="padding:2px;" title="'+L("Flush")+'"></li>';
                t+='<li onclick="pe.cluster.open(\'deploy\');" class="glyphicon glyphicon-share" style="'+(s.newfiles?'color:#d06060;':'')+'padding:2px;" title="'+L("Deploy")+'"></li></td><td colspan="2" style="padding:2px;" title="'+L("All")+'"><meter style="width:100%;" value="'+s.loadavg+'" optimum=\"0.01\" low=\"0.5\" high=\"0.75\"></meter></td>';
                t+='<td title="'+L("Load average")+'" style="color:'+(s.loadavg<0.1?'rgb(0,187,187)':(s.loadavg>0.5?'rgb(187,187,0)':'rgb(0,147,0)'))+';padding:2px;" align="right">'+s.loadavg+'</td></tr>';
                t+='<tr><td colspan="4" style="background:#C0C0C0;"><b>'+L("Management")+'</b></td></tr>';
                var f=1,gl={'lb':'random','master':'cloud-upload','slave':'cloud','worker':'cog'};
                for(i=0;i<s.nodes.length;i++){
                    if(f && s.nodes[i].type=='worker') { f=0;
                        t+='<tr><td colspan="4" style="background:#C0C0C0;"><b>'+L("Workers")+'</b></td></tr>';
                    }
                    t+='<tr style="'+(i%2==0?'background:#e0e0e0;':'')+'" title="'+htmlspecialchars(L(s.nodes[i].type)+' '+s.nodes[i].id)+'">';
                    t+='<td style="padding:2px 2px 2px 10px;"><span class="glyphicon glyphicon-'+gl[s.nodes[i].type]+'"></span></td>';
                    t+='<td style="'+(s.nodes[i].type=='lb'?'color:#A0A060;':(s.nodes[i].type=='master'?'color:#A06060;':(s.nodes[i].type=='slave'?'color:#60A060;':'')))+'padding:2px;">'+(s.nodes[i].name?s.nodes[i].name:s.nodes[i].id)+'</td>';
                    t+='<td style="color:'+(s.nodes[i].load<0.1?'rgb(0,187,187)':(s.nodes[i].load>0.5?'rgb(187,187,0)':'rgb(0,147,0)'))+';padding:2px;"><meter value="'+s.nodes[i].load+'" optimum=\"0.01\" low=\"0.5\" high=\"0.75\"></meter></td>';
                    t+='<td style="color:'+(s.nodes[i].load<0.1?'rgb(0,187,187)':(s.nodes[i].load>0.5?'rgb(187,187,0)':'rgb(0,147,0)'))+';padding:2px 10px;" align="right">'+s.nodes[i].load+'</td></tr>';
                }
                t+='</table>';
                pe.cluster.statusbox.innerHTML=t;
            }else console.error('HTTP-E: '+r.status+' '+url);
            } catch(e) {
                console.error('HTTP-E: '+e);
            }
            };
            r.send(null);
        } else return;

    }

};
