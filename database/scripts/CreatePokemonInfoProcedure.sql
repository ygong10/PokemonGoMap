DROP PROCEDURE IF EXISTS sp_createPokemonInfoTable $$
CREATE PROCEDURE sp_createPokemonInfoTable ()
BEGIN
	CREATE TABLE pokemonInfo (
        id 			INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
        pokemonName 		VARCHAR( 60 ) NOT NULL ,
        latitude		FLOAT( 10, 6 ) NOT NULL ,
        longitude 		FLOAT( 10, 6 ) NOT NULL ,
	hour			INT NOT NULL ,
	minute			INT NOT NULL ,
	createdDate		DATETIME NOT NULL,
	FOREIGN KEY (pokemonName) REFERENCES pokemonName(pokemonName),
	FOREIGN KEY (hour) REFERENCES hour(Id),
	FOREIGN KEY (minute) REFERENCES minute(Id)
    );
END $$