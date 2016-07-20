DROP PROCEDURE IF EXISTS sp_selectPokemonInfoByName$$

CREATE PROCEDURE sp_selectPokemonInfoByName(
	IN pokemonNameParam 	VARCHAR(60)
	)
BEGIN

SELECT * FROM pokemonInfo
WHERE pokemonName = pokemonNameParam;

END$$