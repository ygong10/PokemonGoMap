DROP PROCEDURE IF EXISTS sp_selectAllHour $$
CREATE PROCEDURE sp_selectAllHour ()
BEGIN
	SELECT * From hour;
END $$