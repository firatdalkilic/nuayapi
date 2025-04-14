ALTER TABLE properties 
ADD COLUMN room_count VARCHAR(50) DEFAULT NULL AFTER net_area,
ADD COLUMN living_room_count VARCHAR(50) DEFAULT NULL AFTER room_count; 