Update log
=
v1.0.2
-
Add new feature: Notify customer service once user add comment to booking
Need to run composer update to install new packages.
Need to update config file for rovecomcept.com api data.

v1.0.1
-
clean up code

```
ALTER TABLE warehouse_incoming CHANGE type type_id INT NOT NULL;
ALTER TABLE warehouse_incoming CHANGE status status_id INT NOT NULL;

ALTER TABLE wms_production.warehouse_incoming MODIFY type_id INT(11) NOT NULL;
ALTER TABLE wms_production.warehouse_incoming MODIFY status_id INT(11) NOT NULL;

ALTER TABLE wms_production.warehouse_incoming_status MODIFY id INT(11) NOT NULL;

```

v1.0.0
-
Supporting user management feature.
Supporting user group feature.
User group only support admin/user for now. Only admin group have permission to edit/create/delete user.

Supporting profile feature.
User can update profile info and password by clicking into profile menu from top right nav bar.


Run
````
composer update
````

Execute:

````
php bin/console doctrine:schema:update --force
````
If you receive foreign key error, that's expected

Execute mysql query in db:
````
INSERT INTO warehouse_incoming_type (id, code, detail) VALUES (1, 'OCEAN_FREIGHT', 'Ocean Freight');
INSERT INTO warehouse_incoming_type (id, code, detail) VALUES (2, 'FORWARD', 'Forward');

INSERT INTO warehouse_incoming_status (id, code, detail) VALUES (0, 'DELETED', 'Deleted');
INSERT INTO warehouse_incoming_status (id, code, detail) VALUES (1, 'INBOUND', 'Inbound');
INSERT INTO warehouse_incoming_status (id, code, detail) VALUES (2, 'ARRIVED', 'Arrived');
INSERT INTO warehouse_incoming_status (id, code, detail) VALUES (3, 'COMPLETED', 'Completed');
````
Execute:

````
php bin/console doctrine:schema:update --force
````

Execute mysql query:
````
INSERT INTO wms_production.user_group (name, roles) VALUES ('admin', 'a:1:{i:0;s:10:"ROLE_ADMIN";}');
INSERT INTO wms_production.user_group (name, roles) VALUES ('User', 'a:0:{}');
UPDATE app_user
SET roles = 'a:0:{}';
````
Add yourself admin permission

````
UPDATE app_user
SET roles = 'a:1:{i:0;s:10:"ROLE_ADMIN";}' WHERE username = |your user name|;
````

Assign user group for admin user in /user/, only admin have permission to access /user.

2017-05-02
-
````
sql update:

UPDATE warehouse_booking wb INNER JOIN warehouse_shipment ws ON wb.id = ws.booking_id
SET wb.shipped = ws.created;


````



**RLogistic by rove concept**