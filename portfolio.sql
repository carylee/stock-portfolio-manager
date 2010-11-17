create sequence portfolio_id_sequence start with 1;

create table users (
  email VARCHAR(32) not null primary key,
    constraint email_valid CHECK (password REGEXP_LIKE('[a-zA-Z0-9._%-]+@[a-zA-Z0-9._%-]+\.[a-zA-Z]{2,4}')),
  password VARCHAR(64) not null,
    contraint password_length CHECK (password LIKE '________%'),
  name VARCHAR(32) not null,
);

create TABLE portfolios (
  id integer primary key,
  name VARCHAR(32) not null,
  description VARCHAR(255),
  owner not null references users(email),
  cash_balance number not null default 0,
    constraint cash_balance_nonnegative CHECK( cash_balance >=0 ),
);

create TABLE stocksDaily (
  symbol varchar(16) not null,
  date number not null,
    constraint stock_time_unique UNIQUE(symbol, date),
  open number not null,
  close number not null,
  high number not null,
  low number not null,
  volume number not null,
);

insert into users (email, password, name) VALUES ('carylee@gmail.com', 'helloworld', 'Cary Lee');
insert into users (email, password, name) VALUES ('tepeacock@gmail.com', 'peacockpreston', 'Todd Peacock-Preston');
insert into users (email, password, name) VALUES ('admin@localhost.com', 'mypasswd', 'Some Administrator');
insert into users (email, password, name) VALUES ('clarkefreak@gmail.com', 'istbnt2tb2nw', 'Nathan Ritter');

insert into portfolios (id, name, description, owner) VALUES (portfolio_id_sequence.nextval, 'Practice', 'description', 'carylee@gmail.com')

quit;
