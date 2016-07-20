DROP PROCEDURE IF EXISTS sp_selectAllPokemonInfo $$
CREATE PROCEDURE sp_selectAllPokemonInfo ()
BEGIN
	SELECT * From Pokemoninfo;
END $$
