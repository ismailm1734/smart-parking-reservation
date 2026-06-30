<?php
// index.php — User homepage
// Lists the trigger and stored procedure with descriptions and links.
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Smart Parking — User Home</title>
</head>
<body>

<h1>Smart Parking Reservation — User Home</h1>

<fieldset>
    <legend><b>Triggers:</b></legend>

    <fieldset>
        <b>Trigger 1 (by İsmail Memiş):</b>
        Prevents overlapping reservations on the same parking spot.
        When a new reservation is inserted, the trigger checks whether
        any existing non-cancelled reservation conflicts with the
        requested time range. If a conflict exists, the insert is
        rejected; otherwise the spot is automatically marked as occupied
        when the new reservation is active.
        <br>
        <a href="trigger_page.php">Go to the trigger's page</a>
    </fieldset>
</fieldset>

<br>

<fieldset>
    <legend><b>Stored Procedures:</b></legend>

    <fieldset>
        <b>Stored Procedure 1 (by İsmail Memiş):</b>
        <code>sp_GetDriverReservationStats(p_driver_id)</code> — returns
        a summary for the given driver: total number of reservations,
        breakdown by status (active / completed / cancelled), and the
        parking lot most frequently used by that driver.
        <br>
        <a href="procedure_page.php">Go to the procedure's page</a>
    </fieldset>
</fieldset>

<br><br>
<a href="tickets.php"><b>Support Page</b></a>

</body>
</html>
