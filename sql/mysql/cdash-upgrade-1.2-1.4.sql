ALTER TABLE dynamicanalysisdefect MODIFY value INT NOT NULL DEFAULT 0;

ALTER TABLE test2image DROP PRIMARY KEY;
ALTER TABLE test2image CHANGE imgid imgid INT( 11 ) NOT NULL AUTO_INCREMENT;
ALTER TABLE test2image ADD INDEX ( testid );

ALTER TABLE image CHANGE checksum checksum BIGINT( 20 ) NOT NULL;
ALTER TABLE note CHANGE crc32 crc32 BIGINT( 20 ) NOT NULL;
ALTER TABLE test CHANGE crc32 crc32 BIGINT( 20 ) NOT NULL;
ALTER TABLE coveragefile CHANGE crc32 crc32 BIGINT( 20 ) NOT NULL;


