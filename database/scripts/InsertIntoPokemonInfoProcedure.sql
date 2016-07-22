DROP PROCEDURE IF EXISTS sp_insertIntoPokemonInfo$$

CREATE PROCEDURE sp_insertIntoPokemonInfo(
	IN pokemonNameParam 	VARCHAR(60),
	IN latitudeParam 		FLOAT(10, 6),
	IN longitudeParam 		FLOAT(10,6),
	IN hourParam			INT,
	IN minuteParam			INT,
	IN createdDateParam		DATETIME,
	IN updatedDateParam		DATETIME,
	IN spawnDurationParam		INT
	)
BEGIN

INSERT INTO pokemonInfo (
	pokemonName,
	latitude,
	longitude,
	hour,
	minute,
	createdDate,
	updatedDate,
	spawnDuration
	)

    VALUES (
    	pokemonNameParam,
    	latitudeParam,
    	longitudeParam,
    	hourParam,
	minuteParam,
	createdDateParam,
	updatedDateParam,
	spawnDurationParam
	)
	ON DUPLICATE KEY UPDATE updatedDate = updatedDateParam, spawnDuration = spawnDurationParam;
END$$