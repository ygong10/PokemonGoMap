DROP PROCEDURE IF EXISTS sp_selectAllMinute $$
CREATE PROCEDURE sp_selectAllMinute ()
BEGIN
	SELECT * From minute;
END $$