Update log
=

2017-06-06
-

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


2017-05-02
-
````
sql update:

UPDATE warehouse_booking wb INNER JOIN warehouse_shipment ws ON wb.id = ws.booking_id
SET wb.shipped = ws.created;
````

**RLogistic by rove concept**