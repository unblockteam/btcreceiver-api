--users
create user ROLENAME password 'ROLEPASS';

--tables
create table users (
  id serial primary key,
  wallet varchar(42) unique,
  password char(60),
  created timestamp(0) with time zone default current_timestamp
);

create table sessions (
  uid integer references users(id),
  access_token char(50) unique,
  refresh_token char(50) unique,
  start timestamp(0) with time zone default current_timestamp
);

--user permission
grant select, insert, update
on users, sessions
to ROLENAME;

grant  usage, select
on all sequences in schema public
to ROLENAME;

grant delete
on sessions
to ROLENAME;

grant connect
on database DBNAME
to ROLENAME;