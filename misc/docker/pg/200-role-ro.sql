create role nyro with password 'nyropw' login;
grant connect on database gz to nyro;

\c gz

grant usage on schema public to nyro;
grant select on all tables in schema public to nyro;
alter default privileges in schema public grant select on tables to nyro;