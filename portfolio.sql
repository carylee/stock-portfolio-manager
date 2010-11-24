drop sequence portfolio_ids;
drop sequence stock_stats_cache_unique;
drop table portfolio_stocks;
drop table portfolio_portfolios;
drop table portfolio_users;
--Not dropping or creating stocksDaily because it contains valuable data
--drop table portfolio_stocksDaily;
drop table stocks_stats;
create sequence portfolio_ids start with 1;

create TABLE portfolio_users (
  email VARCHAR(32) NOT NULL primary key,
    constraint email_valid CHECK (REGEXP_LIKE (email, '[a-zA-Z0-9._%-]+@[a-zA-Z0-9._%-]+\.[a-zA-Z]{2,4}')),
  name VARCHAR(32) NOT NULL,
  password VARCHAR(64) NOT NULL,
      constraint long_pw CHECK (password LIKE '________%')
);

create TABLE portfolio_portfolios (
  id number primary key,
  name VARCHAR(32) NOT NULL,
  description VARCHAR(255),
  owner NOT NULL references portfolio_users(email) ON DELETE CASCADE,
  creation_date number,
  cash_balance number default '0' NOT NULL,
    constraint cash_balance_nonnegative CHECK( cash_balance >=0 )
);

--create TABLE portfolio_stocksDaily (
  --symbol varchar(16) NOT NULL,
  --time number NOT NULL,
    --constraint stock_time_unique UNIQUE(symbol, time),
  --open number NOT NULL,
  --close number NOT NULL,
  --high number NOT NULL,
  --low number NOT NULL,
  --volume number NOT NULL
--);

create TABLE stocks_stats (
  symbol VARCHAR(16) NOT NULL,
  from_date number,
  to_date number,
  field VARCHAR(32),
  count number NOT NULL,
  average number NOT NULL,
  std_dev number NOT NULL,
  min number NOT NULL,
  max number NOT NULL,
  volatility number NOT NULL,
    constraint stock_stats_cache_unique UNIQUE(symbol, from_date, to_date, field)
);

create TABLE portfolio_stocks (
  symbol VARCHAR(16) NOT NULL,
  shares number default '0' NOT NULL,
  cost_basis number NOT NULL,
  holder number references portfolio_portfolios(id) ON DELETE CASCADE,
    constraint stock_portfolio_unique UNIQUE(symbol, holder)
);

insert into portfolio_users (email, password, name) VALUES ('carylee@gmail.com', '7be01a57f18b040a75e8de566d93352ba050a1b7e0c49f6b6114ea10b3520dea', 'Cary Lee');
insert into portfolio_users (email, password, name) VALUES ('mcgough.david@gmail.com', '7e988f9fc6241cebf0e9376a558d558d5893befacb3e2c1fbf087bc5ae7a9f06', 'david');
insert into portfolio_users (email, password, name) VALUES ('tepeacock@gmail.com', 'peacockpreston', 'Todd Peacock-Preston');
insert into portfolio_users (email, password, name) VALUES ('admin@localhost.com', 'mypasswd', 'Some Administrator');
insert into portfolio_users (email, password, name) VALUES ('clarkefreak@gmail.com', 'istbnt2tb2nw', 'Nathan Ritter');
insert into portfolio_portfolios (id, name, description, owner) VALUES (portfolio_ids.nextval, 'Practice', 'description', 'carylee@gmail.com');
insert into portfolio_portfolios (id, name, description, owner) VALUES (portfolio_ids.nextval, 'Actual', 'The stocks I actually own', 'carylee@gmail.com');
insert into portfolio_portfolios (id, name, description, owner) VALUES (portfolio_ids.nextval, 'Marshmallows', 'Non-fat, high sugar', 'mcgough.david@gmail.com');
insert into portfolio_portfolios (id, name, description, owner) VALUES (portfolio_ids.nextval, 'Relish', 'Gross, but makes it good', 'mcgough.david@gmail.com');
insert into portfolio_portfolios (id, name, description, owner) VALUES (portfolio_ids.nextval, 'Yummy', 'Yep.', 'mcgough.david@gmail.com');
insert into portfolio_stocks (symbol, shares, cost_basis, holder) VALUES ('SBUX', '100', '28.01', (SELECT id FROM portfolio_portfolios WHERE name='Practice' and owner='carylee@gmail.com'));
insert into portfolio_stocks (symbol, shares, cost_basis, holder) VALUES ('MSFT', '200', '142.58', (SELECT id FROM portfolio_portfolios WHERE name='Practice' and owner='carylee@gmail.com'));
insert into portfolio_stocks (symbol, shares, cost_basis, holder) VALUES ('F', '100', '28.01', (SELECT id FROM portfolio_portfolios WHERE name='Actual' and owner='carylee@gmail.com'));
insert into portfolio_stocks (symbol, shares, cost_basis, holder) VALUES ('CSCO', '200', '142.58', (SELECT id FROM portfolio_portfolios WHERE name='Actual' and owner='carylee@gmail.com'));
insert into portfolio_stocks (symbol, shares, cost_basis, holder) VALUES ('K', '200', '142.58', (SELECT id FROM portfolio_portfolios WHERE name='Actual' and owner='carylee@gmail.com'));
insert into portfolio_stocks (symbol, shares, cost_basis, holder) VALUES ('LUV', '100', '28.01', (SELECT id FROM portfolio_portfolios WHERE name='Marshmallows' and owner='mcgough.david@gmail.com'));
insert into portfolio_stocks (symbol, shares, cost_basis, holder) VALUES ('F', '200', '142.58', (SELECT id FROM portfolio_portfolios WHERE name='Marshmallows' and owner='mcgough.david@gmail.com'));
insert into portfolio_stocks (symbol, shares, cost_basis, holder) VALUES ('K', '200', '142.58', (SELECT id FROM portfolio_portfolios WHERE name='Relish' and owner='mcgough.david@gmail.com'));
insert into portfolio_stocks (symbol, shares, cost_basis, holder) VALUES ('CSCO', '200', '142.58', (SELECT id FROM portfolio_portfolios WHERE name='Relish' and owner='mcgough.david@gmail.com'));

quit;
