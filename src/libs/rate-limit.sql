create table if not exists RateLimit
(
    AutoID integer primary key autoincrement,
    key varhcar (32) default '',
    UserName varchar (64) default ''
)