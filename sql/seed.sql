-- Seed data (mirrors the project sample records). Run after schema.sql.

insert into user_role(role_id,role_name) values (1,'Administrator'),(2,'Staff');
insert into soil_type(soil_type_name) values ('Loam'),('Clay Loam'),('Sandy Loam'),('Clay');
insert into irrigation_type(irrigation_type_name) values ('Rainfed'),('Irrigated'),('Drip Irrigation');
insert into crop_category(crop_category_name) values ('Cereal'),('Vegetable'),('Fruit'),('Legume'),('Root Crop');
insert into season(season_name) values ('Wet Season'),('Dry Season'),('Year Round');
insert into planting_status(status_name) values ('Planted'),('Growing'),('Harvested'),('Failed');
insert into quality_grade(grade_name) values ('Grade A'),('Grade B'),('Grade C');
insert into harvest_remark(remark_description) values ('Good Harvest'),('Acceptable Quality'),('Low Yield'),('Poor Quality'),('Damaged Harvest');
insert into app_user(username,full_name,password_hash,role_id) values ('admin','System Administrator','$2y$10$Qo/0gnrgQqteMLpkoTrXuOdJ89u00VNh2m811ZBelQ80Ujji8IoJ2',1),('staff','Field Staff','$2y$10$6Npgl95d42u6wfZN1UO34.Fem4dI50JIsP/02H8q5MjNhoz4WUFmG',2);
insert into crop(crop_id,crop_name,variety,crop_category_id,growing_period_days,season_id) values
(1,'Rice','NSIC Rc 222',1,115,1),
(2,'Corn','Yellow Corn',1,100,2),
(3,'Tomato','Diamante Max',2,90,2),
(4,'Eggplant','Morena',2,100,3),
(5,'Banana','Lakatan',3,300,3),
(6,'Mango','Carabao',3,150,3),
(7,'Peanut','BPI Pn 9',4,110,3),
(8,'Cassava','Golden Yellow',5,240,3),
(9,'Sweet Potato','NSIC Sp 30',5,120,3),
(10,'Watermelon','Sweet 16',3,85,2);
insert into farmer(farmer_id,first_name,last_name,contact_number,address,date_registered) values
(1,'Juan','Dela Cruz','09171234567','Cebu City','2026-10-26'),
(2,'Maria','Santos','09181234567','Carcar City, Cebu','2026-10-26'),
(3,'Pedro','Reyes','09191234567','Danao City, Cebu','2026-10-26'),
(4,'Ana','Garcia','09201234567','Argao, Cebu','2026-10-26'),
(5,'Jose','Mendoza','09211234567','Bogo City, Cebu','2026-10-26'),
(6,'Ramon','Flores','09221234567','Toledo City, Cebu','2026-10-26'),
(7,'Elena','Cruz','09231234567','Naga City, Cebu','2026-10-26'),
(8,'Antonio','Lim','09241234567','Minglanilla, Cebu','2026-10-26'),
(9,'Rosa','Fernandez','09251234567','Liloan, Cebu','2026-10-26'),
(10,'Mario','Villanueva','09261234567','Compostela, Cebu','2026-10-26'),
(11,'Teresa','Navarro','09271234567','Talisay City, Cebu','2026-10-26'),
(12,'Carlos','Ramos','09281234567','Consolacion, Cebu','2026-10-26'),
(13,'Liza','Torres','09291234567','Dalaguete, Cebu','2026-10-26'),
(14,'Roberto','Castillo','09301234567','Barili, Cebu','2026-10-26'),
(15,'Susan','Aquino','09311234567','Sibonga, Cebu','2026-10-26');
insert into farm(farm_id,farmer_id,farm_name,location,area_hectares,soil_type_id,irrigation_type_id) values
(1,1,'Dela Cruz Farm','Cebu City',2.5,1,1),
(2,2,'Santos Rice Farm','Carcar City, Cebu',4.2,2,2),
(3,3,'Reyes Agricultural Farm','Danao City, Cebu',3.8,3,1),
(4,4,'Garcia Vegetable Farm','Argao, Cebu',1.75,1,3),
(5,5,'Mendoza Farm','Bogo City, Cebu',5.0,4,2),
(6,6,'Flores Corn Farm','Toledo City, Cebu',3.4,3,1),
(7,7,'Cruz Family Farm','Naga City, Cebu',2.8,1,2),
(8,8,'Lim Agricultural Farm','Minglanilla, Cebu',2.25,2,3),
(9,9,'Fernandez Farm','Liloan, Cebu',3.1,3,1),
(10,10,'Villanueva Green Farm','Compostela, Cebu',4.5,1,2),
(11,11,'Navarro Rice Field','Talisay City, Cebu',3.75,4,2),
(12,12,'Ramos Farm','Consolacion, Cebu',2.6,1,1),
(13,13,'Torres Vegetable Farm','Dalaguete, Cebu',1.8,3,3),
(14,14,'Castillo Agricultural Farm','Barili, Cebu',5.5,2,2),
(15,15,'Aquino Farm','Sibonga, Cebu',3.2,1,1);
insert into planting(planting_id,farm_id,crop_id,planting_date,area_planted,seed_quantity,expected_harvest_date,status_id) values
(1,1,1,'2026-01-10',0.50,10.00,'2026-05-05',3),
(2,2,2,'2026-01-13',0.60,15.00,'2026-04-23',3),
(3,3,3,'2026-01-16',0.70,20.00,'2026-04-16',3),
(4,4,4,'2026-01-19',0.80,25.00,'2026-04-29',3),
(5,5,5,'2026-01-22',0.90,30.00,'2026-11-18',3),
(6,6,6,'2026-01-25',1.00,35.00,'2026-06-24',3),
(7,7,7,'2026-01-28',1.10,40.00,'2026-05-18',3),
(8,8,8,'2026-01-31',1.20,45.00,'2026-09-28',3),
(9,9,9,'2026-02-03',1.30,10.00,'2026-06-03',3),
(10,10,10,'2026-02-06',1.40,15.00,'2026-05-02',3),
(11,11,1,'2026-02-09',0.50,20.00,'2026-06-04',3),
(12,12,2,'2026-02-12',0.60,25.00,'2026-05-23',3),
(13,13,3,'2026-02-15',0.70,30.00,'2026-05-16',3),
(14,14,4,'2026-02-18',0.80,35.00,'2026-05-29',3),
(15,15,5,'2026-02-21',0.90,40.00,'2026-12-18',3),
(16,1,6,'2026-02-24',1.00,45.00,'2026-07-24',3),
(17,2,7,'2026-02-27',1.10,10.00,'2026-06-17',3),
(18,3,8,'2026-03-02',1.20,15.00,'2026-10-28',3),
(19,4,9,'2026-03-05',1.30,20.00,'2026-07-03',3),
(20,5,10,'2026-03-08',1.40,25.00,'2026-06-01',3),
(21,6,1,'2026-03-11',0.50,30.00,'2026-07-04',2),
(22,7,2,'2026-03-14',0.60,35.00,'2026-06-22',2),
(23,8,3,'2026-03-17',0.70,40.00,'2026-06-15',2),
(24,9,4,'2026-03-20',0.80,45.00,'2026-06-28',2),
(25,10,5,'2026-03-23',0.90,10.00,'2027-01-17',2),
(26,11,6,'2026-03-26',1.00,15.00,'2026-08-23',2),
(27,12,7,'2026-03-29',1.10,20.00,'2026-07-17',2),
(28,13,8,'2026-04-01',1.20,25.00,'2026-11-27',2),
(29,14,9,'2026-04-04',1.30,30.00,'2026-08-02',1),
(30,15,10,'2026-04-07',1.40,35.00,'2026-07-01',4);
insert into harvest(harvest_id,planting_id,harvest_date,quantity_kg,selling_price_per_kg,grade_id,remark_id) values
(1,1,'2026-05-07',900.00,22.00,1,1),
(2,2,'2026-04-25',1025.00,25.50,2,1),
(3,3,'2026-04-18',1150.00,29.00,3,2),
(4,4,'2026-05-01',1275.00,32.50,1,1),
(5,5,'2026-11-20',1400.00,36.00,2,1),
(6,6,'2026-06-26',1525.00,39.50,3,2),
(7,7,'2026-05-20',1650.00,43.00,1,1),
(8,8,'2026-09-30',1775.00,46.50,2,1),
(9,9,'2026-06-05',1900.00,22.00,3,2),
(10,10,'2026-05-04',2025.00,25.50,1,1),
(11,11,'2026-06-06',2150.00,29.00,2,1),
(12,12,'2026-05-25',2275.00,32.50,3,2),
(13,13,'2026-05-18',2400.00,36.00,1,1),
(14,14,'2026-05-31',2525.00,39.50,2,1),
(15,15,'2026-12-20',2650.00,43.00,3,2),
(16,16,'2026-07-26',2775.00,46.50,1,1),
(17,17,'2026-06-19',2900.00,22.00,2,1),
(18,18,'2026-10-30',3025.00,25.50,3,2),
(19,19,'2026-07-05',3150.00,29.00,1,1),
(20,20,'2026-06-03',3275.00,32.50,2,1);

select setval(pg_get_serial_sequence('farmer','farmer_id'), (select max(farmer_id) from farmer));
select setval(pg_get_serial_sequence('farm','farm_id'), (select max(farm_id) from farm));
select setval(pg_get_serial_sequence('crop','crop_id'), (select max(crop_id) from crop));
select setval(pg_get_serial_sequence('planting','planting_id'), (select max(planting_id) from planting));
select setval(pg_get_serial_sequence('harvest','harvest_id'), (select max(harvest_id) from harvest));
select setval(pg_get_serial_sequence('soil_type','soil_type_id'), (select max(soil_type_id) from soil_type));
select setval(pg_get_serial_sequence('irrigation_type','irrigation_type_id'), (select max(irrigation_type_id) from irrigation_type));
select setval(pg_get_serial_sequence('crop_category','crop_category_id'), (select max(crop_category_id) from crop_category));
select setval(pg_get_serial_sequence('season','season_id'), (select max(season_id) from season));
select setval(pg_get_serial_sequence('planting_status','status_id'), (select max(status_id) from planting_status));
select setval(pg_get_serial_sequence('quality_grade','grade_id'), (select max(grade_id) from quality_grade));
select setval(pg_get_serial_sequence('harvest_remark','remark_id'), (select max(remark_id) from harvest_remark));
select setval(pg_get_serial_sequence('app_user','user_id'), (select max(user_id) from app_user));
