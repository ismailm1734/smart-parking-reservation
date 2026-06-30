<?php
// procedure_page.php — Calls sp_GetDriverReservationStats(driver_id)
require_once 'db.php';

$result_row = null;
$error      = "";
$submitted  = false;

if (isset($_POST['driver_id']) && $_POST['driver_id'] !== "") {
    $submitted = true;
    $driver_id = (int)$_POST['driver_id'];

    $stmt = $conn->prepare("CALL sp_GetDriverReservationStats(?)");
    $stmt->bind_param("i", $driver_id);

    if ($stmt->execute()) {
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $result_row = $res->fetch_assoc();
        } else {
            $error = "Procedure ran but returned no rows.";
        }
    } else {
        $error = "Procedure error: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Stored Procedure — Driver Reservation Stats</title>
</head>
<body>

<fieldset>
    <b>Stored Procedure 1 (by İsmail Memiş):</b>
    <code>sp_GetDriverReservationStats(p_driver_id)</code> takes a
    driver ID and returns a summary row with the driver's name, total
    number of reservations, status breakdown, and the parking lot they
    use most.

    <form method="post">
        <label>Driver ID (e.g. 1, 2, 3, 4, 5, 6):</label><br>
        <input type="number" name="driver_id" required min="1">
        <button type="submit">Call Procedure</button>
    </form>
</fieldset>

<?php if ($submitted): ?>
    <br>
    <fieldset>
        <legend><b>Result:</b></legend>
        <?php if ($error !== ""): ?>
            <p style="color:red;"><?= htmlspecialchars($error) ?></p>
        <?php elseif ($result_row): ?>
            <table border="1" cellpadding="6">
                <tr>
                    <th>driver_id</th>
                    <th>driver_name</th>
                    <th>total_reservations</th>
                    <th>active</th>
                    <th>completed</th>
                    <th>cancelled</th>
                    <th>most_used_lot</th>
                </tr>
                <tr>
                    <td><?= htmlspecialchars($result_row['driver_id']) ?></td>
                    <td><?= htmlspecialchars($result_row['driver_name']) ?></td>
                    <td><?= htmlspecialchars($result_row['total_reservations']) ?></td>
                    <td><?= htmlspecialchars($result_row['active_count'] ?? 0) ?></td>
                    <td><?= htmlspecialchars($result_row['completed_count'] ?? 0) ?></td>
                    <td><?= htmlspecialchars($result_row['cancelled_count'] ?? 0) ?></td>
                    <td><?= htmlspecialchars($result_row['most_used_lot'] ?? '(none)') ?></td>
                </tr>
            </table>
        <?php endif; ?>
    </fieldset>
<?php endif; ?>

<br>
<a href="index.php">Go to homepage</a>

</body>
</html>
