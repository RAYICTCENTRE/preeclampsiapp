<h2>Approved Doctors</h2>
<table>
<tr>
<th>ID</th>
<th>Username</th>
<th>Age</th>
<th>Status</th>
</tr>
<?php
$approved_docs = $conn->query("SELECT * FROM users WHERE role='doctor' AND status='approved'");
while($doc = $approved_docs->fetch_assoc()){ ?>
<tr>
<td><?php echo $doc['user_id']; ?></td>
<td><?php echo $doc['username']; ?></td>
<td><?php echo $doc['age']; ?></td>
<td><?php echo $doc['status']; ?></td>
</tr>
<?php } ?>
</table>