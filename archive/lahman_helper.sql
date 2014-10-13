-- Add a column for better performance for now
ALTER TABLE mlb.master ADD nameConcat varchar(30);
UPDATE mlb.master SET nameConcat = CONCAT(nameFirst,' ',nameLast);