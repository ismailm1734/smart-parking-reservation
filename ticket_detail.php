<?php
// ticket_detail.php — User view of a single ticket
require_once 'mongo.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    die("No ticket id provided.");
}

$oid = new MongoDB\BSON\ObjectId($id);

// ---- Add comment (POST) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $c_user = trim($_POST['c_user']    ?? "");
    $c_text = trim($_POST['c_comment'] ?? "");

    if ($c_user !== "" && $c_text !== "") {
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->update(
            ['_id' => $oid],
            ['$push' => [
                'comments' => [
                    'username'   => $c_user,
                    'comment'    => $c_text,
                    'created_at' => date('Y-m-d H:i:s'),
                ]
            ]]
        );
        $manager->executeBulkWrite('tickets.entries', $bulk);
        // Redirect to refresh the view (avoid form re-submission on reload)
        header("Location: ticket_detail.php?id=" . urlencode($id));
        exit;
    }
}

// ---- Fetch ticket ----
$query  = new MongoDB\Driver\Query(['_id' => $oid]);
$cursor = $manager->executeQuery('tickets.entries', $query);
$ticket = current($cursor->toArray());

if (!$ticket) {
    die("Ticket not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Ticket Details</title>
</head>
<body>

<h2>Ticket Details</h2>

<b>Username:</b> <?= htmlspecialchars($ticket->username) ?><br>
<b>Body:</b> <?= htmlspecialchars($ticket->body) ?><br>
<b>Status:</b> <?= $ticket->status ? 'Active' : 'Resolved' ?><br>
<b>Created At:</b> <?= htmlspecialchars($ticket->created_at) ?><br>

<br>
<fieldset>
    <legend><b>Comments:</b></legend>
    <?php
    $comments = $ticket->comments ?? [];
    if (count((array)$comments) === 0) {
        echo "<p>No comments yet.</p>";
    } else {
        foreach ($comments as $c) {
            $c = (array)$c;
            echo "<fieldset>";
            echo "<b>Created At:</b> " . htmlspecialchars($c['created_at'] ?? '') . "<br>";
            echo "<b>Username:</b> "   . htmlspecialchars($c['username']   ?? '') . "<br>";
            echo "<b>Comment:</b> "    . htmlspecialchars($c['comment']    ?? '');
            echo "</fieldset>";
        }
    }
    ?>
</fieldset>

<br>
<form method="post">
    <textarea name="c_comment" placeholder="Add a comment" rows="3" cols="40" required></textarea><br>
    <input type="text" name="c_user" placeholder="Your Username" required><br>
    <button type="submit">Add Comment</button>
</form>

<a href="tickets.php">Back to Tickets</a>

</body>
</html>
