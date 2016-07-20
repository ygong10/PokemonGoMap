DROP PROCEDURE IF EXISTS sp_createHourReference $$
CREATE PROCEDURE sp_createHourReference()
BEGIN
	CREATE TABLE hour(
        Id		INT NOT NULL PRIMARY KEY
    );
END $$