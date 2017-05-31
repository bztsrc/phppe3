DROP TABLE IF EXISTS `smenu_list`;
CREATE TABLE `smenu_list` (
  `list_id` varchar(64) NOT NULL default '',
  `id` varchar(128) NOT NULL default '',
  `title` varchar(128) NOT NULL default '',
  `info` varchar(128) NOT NULL default '',
  `type` varchar(16) NOT NULL default '',
  `posx` int(11) NOT NULL default 0,
  `posy` int(11) NOT NULL default 0,
  `ordering` int(11) NOT NULL default 0,
  PRIMARY KEY  (`list_id`,`id`)
);

INSERT INTO `views` VALUES ('smenu','Smenu','<!if cms><!cms(0,0,50,pageresp) img(128) background>&nbsp;&nbsp;&nbsp;<!cms(400,10,0,link) text link>&nbsp;&nbsp;&nbsp;<!cms(400,10,0,pageinfo) text callback>&nbsp;&nbsp;&nbsp;<!cms(50,10,0,pagerevert) num(0,600) refresh>&nbsp;&nbsp;&nbsp;<img style="position:absolute;z-index:997;cursor:pointer;opacity:0.7;" onclick="pe.smenu.add();" src="images/cms/pageadd.png" title="<!L Add new menu>"><!/if><script>var smenu_data = {"background":"<!=app.background>","link":"<!=link>","url":"<!=core.url>","callback":"<!=callback>","refresh":"<!=refresh>"<!if cms>,"edit":true<!/if>};</script><!foreach smenu><div class="smenu_item <!=type>" data-id="<!=id>" data-smenu="<!=posx>,<!=posy>" data-title="<!=title>"><!if title!=""><b><!=title></b><br><!/if><!=info></div><!/foreach>','','','','',CURRENT_TIMESTAMP,CURRENT_TIMESTAMP,-1);
