from datetime import date, timedelta
H_ADMIN='$2y$10$Qo/0gnrgQqteMLpkoTrXuOdJ89u00VNh2m811ZBelQ80Ujji8IoJ2'
H_STAFF='$2y$10$6Npgl95d42u6wfZN1UO34.Fem4dI50JIsP/02H8q5MjNhoz4WUFmG'
q=lambda s:"'"+s.replace("'","''")+"'"
o=[]
o.append("-- Seed data (mirrors the project sample records). Run after schema.sql.\n")
o.append("insert into user_role(role_id,role_name) values (1,'Administrator'),(2,'Staff');")
def lk(table,cols,vals):
    o.append(f"insert into {table}({cols}) values "+",".join(f"({q(v)})" for v in vals)+";")
lk("soil_type","soil_type_name",["Loam","Clay Loam","Sandy Loam","Clay"])
lk("irrigation_type","irrigation_type_name",["Rainfed","Irrigated","Drip Irrigation"])
lk("crop_category","crop_category_name",["Cereal","Vegetable","Fruit","Legume","Root Crop"])
lk("season","season_name",["Wet Season","Dry Season","Year Round"])
lk("planting_status","status_name",["Planted","Growing","Harvested","Failed"])
lk("quality_grade","grade_name",["Grade A","Grade B","Grade C"])
lk("harvest_remark","remark_description",["Good Harvest","Acceptable Quality","Low Yield","Poor Quality","Damaged Harvest"])
o.append(f"insert into app_user(username,full_name,password_hash,role_id) values ('admin','System Administrator','{H_ADMIN}',1),('staff','Field Staff','{H_STAFF}',2);")

crops=[("Rice","NSIC Rc 222","Cereal",115,"Wet Season"),("Corn","Yellow Corn","Cereal",100,"Dry Season"),
("Tomato","Diamante Max","Vegetable",90,"Dry Season"),("Eggplant","Morena","Vegetable",100,"Year Round"),
("Banana","Lakatan","Fruit",300,"Year Round"),("Mango","Carabao","Fruit",150,"Year Round"),
("Peanut","BPI Pn 9","Legume",110,"Year Round"),("Cassava","Golden Yellow","Root Crop",240,"Year Round"),
("Sweet Potato","NSIC Sp 30","Root Crop",120,"Year Round"),("Watermelon","Sweet 16","Fruit",85,"Dry Season")]
cats=["Cereal","Vegetable","Fruit","Legume","Root Crop"]; seas=["Wet Season","Dry Season","Year Round"]
o.append("insert into crop(crop_id,crop_name,variety,crop_category_id,growing_period_days,season_id) values\n"+",\n".join(
 f"({i+1},{q(c[0])},{q(c[1])},{cats.index(c[2])+1},{c[3]},{seas.index(c[4])+1})" for i,c in enumerate(crops))+";")

farmers=[("Juan","Dela Cruz","Cebu City"),("Maria","Santos","Carcar City, Cebu"),("Pedro","Reyes","Danao City, Cebu"),
("Ana","Garcia","Argao, Cebu"),("Jose","Mendoza","Bogo City, Cebu"),("Ramon","Flores","Toledo City, Cebu"),
("Elena","Cruz","Naga City, Cebu"),("Antonio","Lim","Minglanilla, Cebu"),("Rosa","Fernandez","Liloan, Cebu"),
("Mario","Villanueva","Compostela, Cebu"),("Teresa","Navarro","Talisay City, Cebu"),("Carlos","Ramos","Consolacion, Cebu"),
("Liza","Torres","Dalaguete, Cebu"),("Roberto","Castillo","Barili, Cebu"),("Susan","Aquino","Sibonga, Cebu")]
o.append("insert into farmer(farmer_id,first_name,last_name,contact_number,address,date_registered) values\n"+",\n".join(
 f"({i+1},{q(f[0])},{q(f[1])},{q('0917'+str(i+1)+'1234567'[:7] if False else ['09171234567','09181234567','09191234567','09201234567','09211234567','09221234567','09231234567','09241234567','09251234567','09261234567','09271234567','09281234567','09291234567','09301234567','09311234567'][i])},{q(f[2])},'2026-10-26')" for i,f in enumerate(farmers))+";")

soil=["Loam","Clay Loam","Sandy Loam","Clay"]; irr=["Rainfed","Irrigated","Drip Irrigation"]
farms=[("Dela Cruz Farm",2.5,"Loam","Rainfed"),("Santos Rice Farm",4.2,"Clay Loam","Irrigated"),("Reyes Agricultural Farm",3.8,"Sandy Loam","Rainfed"),
("Garcia Vegetable Farm",1.75,"Loam","Drip Irrigation"),("Mendoza Farm",5.0,"Clay","Irrigated"),("Flores Corn Farm",3.4,"Sandy Loam","Rainfed"),
("Cruz Family Farm",2.8,"Loam","Irrigated"),("Lim Agricultural Farm",2.25,"Clay Loam","Drip Irrigation"),("Fernandez Farm",3.1,"Sandy Loam","Rainfed"),
("Villanueva Green Farm",4.5,"Loam","Irrigated"),("Navarro Rice Field",3.75,"Clay","Irrigated"),("Ramos Farm",2.6,"Loam","Rainfed"),
("Torres Vegetable Farm",1.8,"Sandy Loam","Drip Irrigation"),("Castillo Agricultural Farm",5.5,"Clay Loam","Irrigated"),("Aquino Farm",3.2,"Loam","Rainfed")]
o.append("insert into farm(farm_id,farmer_id,farm_name,location,area_hectares,soil_type_id,irrigation_type_id) values\n"+",\n".join(
 f"({i+1},{i+1},{q(f[0])},{q(farmers[i][2])},{f[1]},{soil.index(f[2])+1},{irr.index(f[3])+1})" for i,f in enumerate(farms))+";")

pl=[];hv=[]
N=30
for i in range(1,N+1):
    pd=date(2026,1,10)+timedelta(days=3*(i-1))
    farm=(i-1)%15+1; crop=(i-1)%10+1
    area=round(0.5+0.1*((i-1)%10),2); seed=10+5*((i-17)%8)
    exp=pd+timedelta(days=crops[crop-1][3])
    st=3 if i<=20 else (2 if i<=28 else (1 if i==29 else 4))
    pl.append(f"({i},{farm},{crop},'{pd}',{area:.2f},{seed:.2f},'{exp}',{st})")
    if i<=20:
        qty=900+125*(i-1); price=22+3.5*((i-1)%8); g=(i-1)%3+1; r=1 if g<3 else 2
        hv.append(f"({i},{i},'{exp+timedelta(days=2)}',{qty:.2f},{price:.2f},{g},{r})")
o.append("insert into planting(planting_id,farm_id,crop_id,planting_date,area_planted,seed_quantity,expected_harvest_date,status_id) values\n"+",\n".join(pl)+";")
o.append("insert into harvest(harvest_id,planting_id,harvest_date,quantity_kg,selling_price_per_kg,grade_id,remark_id) values\n"+",\n".join(hv)+";")
o.append("""
-- Re-sync identity sequences after explicit-id inserts
select setval(pg_get_serial_sequence(t,c), (select coalesce(max(id),1) from (values (0)) x(id)), true) from (values ('x','y')) v(t,c) where false;
""")
for t,c in [("farmer","farmer_id"),("farm","farm_id"),("crop","crop_id"),("planting","planting_id"),("harvest","harvest_id"),
            ("soil_type","soil_type_id"),("irrigation_type","irrigation_type_id"),("crop_category","crop_category_id"),
            ("season","season_id"),("planting_status","status_id"),("quality_grade","grade_id"),("harvest_remark","remark_id"),("app_user","user_id")]:
    o.append(f"select setval(pg_get_serial_sequence('{t}','{c}'), (select max({c}) from {t}));")
open("sql/seed.sql","w").write("\n".join(o)+"\n")
