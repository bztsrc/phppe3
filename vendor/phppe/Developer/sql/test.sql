drop table if exists test;
create table test (
	id integer primary key autoincrement,
	name varchar(32) not null default '',
	parentId int not null default 0
);
insert into test values (1,'first',0);
insert into test values (2,'second',0);
insert into test values (3,'third',1);
insert into test values (4,'fourth',0);
