<?php
// create_ticket.php — Create a new support ticket
require_once 'mongo.php';

$created   = false;
$error     = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? "");
    $body     = trim($_POST['body']     ?? "");

    if ($username === "" || $body === "") {
        $error = "Both username and body are required.";
    } else {
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->insert([
            'username'   => $username,
            'body'       => $body,
            'status'     => true,                              // active
            'created_at' => date('Y-m-d H:i:s'),               // stored as string
            'comments'   => [],                                // empty array
        ]);

        try {
            $manager->executeBulkWrite('tickets.entries', $bulk);
            $created = true;
        } catch (Exception $e) {
            $error = "Failed to create ticket: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Create Ticket</title>
</head>
<body>

<a href="tickets.php">View Tickets</a><br>
<a href="index.php">Home</a>

<h2>Create a Ticket</h2>

<?php if ($created): ?>
    <p style="color:green;">Ticket was successfully created.</p>
    <a href="create_ticket.php">Create another ticket</a><br>
    <a href="tickets.php">Go to ticket list</a>
<?php else: ?>
    <?php if ($error !== ""): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post">
        <input type="text" name="username" placeholder="username" required><br>
        <textarea name="body" placeholder="describe your issue..." rows="4" cols="40" required></textarea><br>
        <button type="submit">Create Ticket</button>
    </form>
<?php endif; ?>

</body>
</html>
