// http://mherman.org/blog/2015/02/12/postgresql-and-nodejs/

var pg = require('pg');
var connectionString = process.env.DATABASE_URL || 'postgres://localhost:5432/textlr';

var client = new pg.Client(connectionString);
client.connect();
var query = client.query('CREATE TABLE texts(id CHAR(10) NOT NULL PRIMARY KEY, title VARCHAR(300), markedtext VARCHAR(16000) NOT NULL, nakedtext VARCHAR(16000) NOT NULL, time timestamp default current_timestamp, count SERIAL)');
query.on('end', function() { client.end(); });
