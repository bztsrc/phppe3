<?php
use PHPPE\Core as PHPPE;

echo("PHPPE\DB class: ");
if(!class_exists("\PHPPE\DB")) {
	echo("Failed\n");
	return false;
} else echo ("OK\n");

echo("simple select: ");
if( \PHPPE\DB::select("users") != "SELECT * FROM users" ) {
	echo("Failed\n");
	return false;
} else echo ("OK\n");

echo("select fields: ");
if( \PHPPE\DB::select("users")->fields(["id","name"]) != "SELECT id,name FROM users" ) {
	echo("Failed\n");
	return false;
} else echo ("OK\n");

echo("select where: ");
if( \PHPPE\DB::select("users")->where("id=?") != "SELECT * FROM users WHERE id=?" ) {
	echo("Failed\n");
	return false;
} else echo ("OK\n");

echo("complex where: ");
if( \PHPPE\DB::select("users")->where([["name","=","?"],["email","=","?"]],"OR")->where([["id","=","?"],["active","=","1"]]) != "SELECT * FROM users WHERE (name = ? OR email = ?) AND (id = ? AND active = '1')" ) {
echo(\PHPPE\DB::select("users")->where([["name","=","?"],["email","=","?"]],"OR")->where([["id","=","?"],["active","=","1"]]));
	echo("Failed\n");
	return false;
} else echo ("OK\n");

echo("select table multiplication: ");
if( \PHPPE\DB::select("users","u")->table("user_posts","p")->where("u.id=p.id") != "SELECT * FROM users u, user_posts p WHERE u.id=p.id" ) {
	echo("Failed\n");
	return false;
} else echo ("OK\n");

echo("update no fields (Exception): ");
try {
	\PHPPE\DB::update("users")->sql();
	echo("Failed\n");
	return false;
} catch(\Exception $e){
	echo "OK\n";
}

echo("update no fields (toString): ");
if( \PHPPE\DB::update("users") != "<span style='background:#F00000;color:#FEA0A0;padding:3px;'>E-DB:&nbsp;No fields specified</span>" ) {
	echo("Failed\n");
	return false;
} else echo ("OK\n");

echo("update with fields: ");
if( \PHPPE\DB::update("users")->fields(['id','name']) != "UPDATE users SET id=?,name=?" ) {
	echo("Failed\n");
	return false;
} else echo ("OK\n");

echo("delete table: ");
if( \PHPPE\DB::delete("users") != "DELETE FROM users" ) {
	echo("Failed\n");
	return false;
} else echo ("OK\n");

echo("delete table from references: ");
if( \PHPPE\DB::delete("user_posts")->join("LEFT","users","user_posts.userId=users.id")->where([["users.id","IS NULL"]]) != "DELETE user_posts FROM user_posts LEFT JOIN users ON user_posts.userId=users.id WHERE (users.id IS NULL)" ) {
	echo("Failed\n");
	return false;
} else echo ("OK\n");

echo("insert into: ");
if( \PHPPE\DB::insert("users")->fields(['id','name']) != "INSERT INTO users (id,name) VALUES (?,?)" ) {
	echo("Failed\n");
	return false;
} else echo ("OK\n");

echo("replace into: ");
if( \PHPPE\DB::replace("users")->fields(['id','name'])->where("id=''") != "REPLACE INTO users (id,name) VALUES (?,?) WHERE id=''" ) {
	echo("Failed\n");
	return false;
} else echo ("OK\n");

echo("truncate table: ");
if( \PHPPE\DB::truncate("users") != "TRUNCATE TABLE users" ) {
	echo("Failed\n");
	return false;
} else echo ("OK\n");

return true;
?>