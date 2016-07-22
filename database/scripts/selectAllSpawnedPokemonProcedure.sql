DROP PROCEDURE IF EXISTS sp_selectAllSpawnedPokemon $$
CREATE PROCEDURE sp_selectAllSpawnedPokemon ()
BEGIN
	SELECT * From Pokemoninfo
	WHERE DATE_ADD(createdDate, INTERVAL spawnDuration SECOND) > NOW() AND spawnDuration >= 0;
END $$
