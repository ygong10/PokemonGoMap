DROP PROCEDURE IF EXISTS sp_selectAllPokemonName $$
CREATE PROCEDURE sp_selectAllPokemonName ()
BEGIN
	SELECT * From Pokemonname;
END $$