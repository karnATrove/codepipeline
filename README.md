20170502

sql update:

UPDATE warehouse_booking wb INNER JOIN warehouse_shipment ws ON wb.id = ws.booking_id
SET wb.shipped = ws.created;

**RLogistic by rove concept**