DROP PROCEDURE IF EXISTS sp_insertIntoPokemonInfo$$

CREATE PROCEDURE sp_insertIntoPokemonInfo(
	IN pokemonNameParam 	VARCHAR(60),
	IN latitudeParam 		FLOAT(10, 6),
	IN longitudeParam 		FLOAT(10,6),
	IN timeParam			INT
	)
BEGIN

INSERT INTO pokemonInfo (
	pokemonName,
	latitude,
	longitude,
	time
	)

    VALUES (
    	pokemonNameParam,
    	latitudeParam,
    	longitudeParam,
    	timeParam
	);

END$$