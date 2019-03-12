SELECT @@SERVERNAME AS 'Server Name'  
--xxxxxxxx

SELECT @@SERVICENAME AS 'Service Name'; 
--MSSQLSERVER

SELECT @@VERSION AS 'SQL Server Version';  
--Microsoft SQL Server 2017 (RTM-CU12) (KB4464082) - 14.0.3045.24 (X64)
--Express Edition (64-bit) on Linux (Ubuntu 18.04.1 LTS)

SELECT @@LOCK_TIMEOUT AS 'Lock Timeout';  
--5000 mSec

SELECT @@MAX_CONNECTIONS AS 'Max Connections';  
--32767

SELECT @@OPTIONS AS 'OriginalOptionsValue';  
--5496

SELECT @@SPID AS 'ID', SYSTEM_USER AS 'Login Name', USER AS 'User Name';  
--52, sa, dbo


EXEC sp_databases
--list DBs

select db_name()
--current DB

USE TestDB1
go

select * from information_schema.columns

select * from Inventory 



