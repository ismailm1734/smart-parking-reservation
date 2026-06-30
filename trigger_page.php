<?php
// trigger_page.php — Demonstrates trg_prevent_overlap_reservation
require_once 'db.php';

$message = "";
$ok      = false;

if (isset($_POST['case'])) {
    $case = $_POST['case'];

    // ---- Clean up any leftover test rows from previous runs ----
    $conn->query("DELETE FROM RESERVATION WHERE reservation_id IN (9001, 9002)");

    if ($case === "1") {
        // Case 1: Valid reservation on a free spot (spot_id = 7 is currently 'available').
        // The trigger should allow the insert AND mark the spot as 'occupied'.
        $sql = "INSERT INTO RESERVATION
                    (reservation_id, start_time, end_time, status, driver_id, spot_id)
                VALUES
                    (9001, '2026-04-10 09:00:00', '2026-04-10 11:00:00',
                     'active', 1, 7)";
        if ($conn->query($sql)) {
            // Read back the spot status to prove the trigger updated it
            $res = $conn->query("SELECT status FROM PARKING_SPOT WHERE spot_id = 7");
            $row = $res->fetch_assoc();
            $message = "Case 1 SUCCESS: Reservation #9001 inserted on spot 7. "
                     . "Spot status is now '" . htmlspecialchars($row['status']) . "' "
                     . "(automatically set by the trigger).";
            $ok = true;
        } else {
            $message = "Case 1 FAILED: " . $conn->error;
        }
    }
    elseif ($case === "2") {
        // Case 2: Conflicting reservation on the same spot, overlapping time.
        // Reservation #5 already exists for spot 3 from 2026-03-04 10:00 to 12:00.
        // We try to insert another one from 11:00 to 13:00 — it MUST be rejected.
        $sql = "INSERT INTO RESERVATION
                    (reservation_id, start_time, end_time, status, driver_id, spot_id)
                VALUES
                    (9002, '2026-03-04 11:00:00', '2026-03-04 13:00:00',
                     'active', 2, 3)";
        if ($conn->query($sql)) {
            $message = "Case 2 UNEXPECTED: the insert was NOT rejected. "
                     . "Trigger may be missing or disabled.";
        } else {
            // Expected behaviour: trigger raises SQLSTATE 45000
            $message = "Case 2 BLOCKED (as expected): " . $conn->error;
            $ok = true;
        }
    }
    elseif ($case === "3") {
        // Case 3: Reset — restore spot 7 to 'available' so Case 1 can be re-run.
        $conn->query("UPDATE PARKING_SPOT SET status = 'available' WHERE spot_id = 7");
        $message = "Case 3 SUCCESS: test data reset. Spot 7 is 'available' again, "
                 . "and test reservations (9001, 9002) have been removed.";
        $ok = true;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Trigger — Prevent Overlap Reservation</title>
</head>
<body>

<fieldset>
    <b>Trigger 1 (by İsmail Memiş):</b>
    <code>trg_prevent_overlap_reservation</code> fires BEFORE INSERT on
    RESERVATION. It blocks any new reservation that overlaps in time
    with an existing non-cancelled reservation on the same spot, and
    automatically marks the spot as occupied when an active reservation
    is accepted.

    <form method="post">
        <button type="submit" name="case" value="1">
            Case 1 — Valid reservation (spot 7, no conflict)
        </button>
        <button type="submit" name="case" value="2">
            Case 2 — Conflicting reservation (spot 3, time overlap)
        </button>
        <button type="submit" name="case" value="3">
            Case 3 — Reset test data
        </button>
    </form>
</fieldset>

<?php if ($message !== ""): ?>
    <br>
    <fieldset>
        <legend><b>Result:</b></legend>
        <p style="color: <?= $ok ? 'green' : 'red' ?>;">
            <?= htmlspecialchars($message) ?>
        </p>
    </fieldset>
<?php endif; ?>

<br>
<a href="index.php">Go to homepage</a>

</body>
</html>
