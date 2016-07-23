DROP PROCEDURE IF EXISTS sp_selectSpawnedPokemonInfoByName $$
CREATE PROCEDURE sp_selectSpawnedPokemonInfoByName (
		IN pokemonNameParam 	VARCHAR(60)
)
BEGIN
	SELECT * From Pokemoninfo
	WHERE DATE_ADD(createdDate, INTERVAL spawnDuration SECOND) > NOW() AND spawnDuration >= 0 AND pokemonName = pokemonNameParam;
END $$
