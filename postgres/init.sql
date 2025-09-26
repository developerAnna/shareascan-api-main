-- Create database if not exists
SELECT 'CREATE DATABASE shareascan'
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'shareascan')\gexec

-- Connect to the database
\c shareascan;

-- Create extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- Grant all privileges to the user
GRANT ALL PRIVILEGES ON DATABASE shareascan TO shareascan;
GRANT ALL ON SCHEMA public TO shareascan;

-- Create initial tables will be handled by Laravel migrations
-- This file is just for initial database setup