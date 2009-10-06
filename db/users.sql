-- psql template1

-- CREATE DATABASE worg;

-- psql worg

CREATE TABLE users (
id SERIAL NOT NULL,
author VARCHAR(20) NOT NULL UNIQUE,
digest VARCHAR(32) NOT NULL,
email VARCHAR(320) NOT NULL UNIQUE
);

CREATE USER worg WITH PASSWORD 'worg';

GRANT ALL ON users TO worg;



