DROP PROCEDURE IF EXISTS sp_createPokemonNameReference $$
CREATE PROCEDURE sp_createPokemonNameReference()
BEGIN
	CREATE TABLE pokemonName(
        pokemonName 		VARCHAR( 60 ) NOT NULL PRIMARY KEY
    );
END $$