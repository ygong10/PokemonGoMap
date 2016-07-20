DROP PROCEDURE IF EXISTS sp_createMinuteReference $$
CREATE PROCEDURE sp_createMinuteReference()
BEGIN
	CREATE TABLE minute(
        Id		INT NOT NULL PRIMARY KEY
    );
END $$