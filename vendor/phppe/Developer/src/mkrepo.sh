#!/bin/bash

kernel=`uname -s`
if [ "$kernel" == "Darwin" ]; then
    COPYFILE_DISABLE=1
    COPY_EXTENDED_ATTRIBUTES_DISABLE=1
    TARFLAG="--disable-copyfile"
else
    TARFLAG=""
fi

# go to ProjectRoot
s=`pwd`
[ "${s##*/}" == "tools" ] && cd ..

if [ -f public/src.php ]; then
    if [ public/src.php -nt public/index.php ]; then
        php tools/fmt.php -c public/src.php >public/index.php
    fi
    if [ public/src.php -nt vendor/phppe/Developer/src/index.php ]; then
        php tools/fmt.php public/src.php >vendor/phppe/Developer/src/index.php
    fi
fi

# enter vendor folder
cd vendor/phppe
#refresh external files
curl -sS https://raw.githubusercontent.com/necolas/normalize.css/master/normalize.css 2>/dev/null >core/css/normalize.css || wget -q --no-check-certificate https://raw.githubusercontent.com/necolas/normalize.css/master/normalize.css -O core/css/normalize.css >/dev/null
echo

# phppe/Pack
# special, this one ships several extensions at once
if [ ! -f  ../../phppe3_pack.tgz -o "`find core/* email users db gpio -cnewer ../../phppe3_pack.tgz 2>/dev/null|grep -v normalize.css`" != "" ]; then
    tar --exclude=log $TARFLAG -czvf ../../phppe3_pack.tgz composer.json LICENSE core/libs core/addons core/js/core.js.php core/js/resptable.js core/js/setsel.js core/js/jquery.js core/ctrl core/css/normalize.css core/css/core.css core/images core/lang core/sql/views.sql core/sql/pages.sql core/out core/views/index.tpl core/views/maintenance.tpl core/views/rss.tpl core/00_core.php Email Users DB Registry
    echo
fi

# phppe/Developer
# because it contains the source, always generated
cd Developer
tar $TARFLAG -czvf ../../../phppe3_devel.tgz composer.json `ls|sed s/composer\.json//g`
echo

# phppe/Extensions
cd ../Extensions
rm ./._* 2>/dev/null
if [ ! -f  ../../../phppe3_extmgr.tgz -o "`find . -cnewer ../../../phppe3_extmgr.tgz 2>/dev/null`" != "" ]; then
    mv config.php ../config.php
    cat <<EOF >config.php
<?php
return array(
	"host"=>"localhost",
	"port"=>22,
	"user"=>"youruser",
	"identity"=>"",
	"path"=>"/var/www/localhost");
?>
EOF
    [ ! -f .rootca ] && touch .rootca
    tar $TARFLAG -czvf ../../../phppe3_extmgr.tgz composer.json `echo *|sed s/composer\.json//g` .rootca
    mv ../config.php config.php
    echo
fi

# non-special extensions
for pkg in ClassMap CMS EplosCMS wysiwyg GPIO RPi Bitstorm Chart CookieAlert Gallery DataLibrary DBA youtube Car R2D2 LDAP SF GeoLocation bootstrap; do
    tarball=`echo $pkg| tr '[:upper:]' '[:lower:]'`
    cd ../$pkg
    rm ./._* 2>/dev/null
    if [ ! -f  ../../../phppe3_$tarball.tgz -o "`find . -cnewer ../../../phppe3_$tarball.tgz 2>/dev/null`" != "" ]; then
        tar $TARFLAG -czvf ../../../phppe3_$tarball.tgz composer.json `echo *|sed s/composer\.json//g|sed s/[\.\/]+_preview//g`
        echo
    fi
done

# generate packages.json with repo.php
cd ../../..
rm packages.json
php vendor/phppe/Developer/src/repo.php >packages.json
cat packages.json | sed 's/http:\/\/phppe.org\/phppe3_/https:\/\/raw.githubusercontent.com\/bztsrc\/phppe3\/master\/phppe3_/g' >packages.json.github
cd ..
