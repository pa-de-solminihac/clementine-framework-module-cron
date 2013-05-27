Clementine CRON Selfcheck reported tasks started more than <?php echo $data['seconds_ago']; ?> seconds ago, and never finished since then : <br />
<br />
<ol>
<?php
foreach ($data['tasks'] as $task){
?>
    <li><em><?php echo 'cron/' . $task['action']; ?></em> started at <em><?php echo $task['date_start']; ?></em></li>
<?php
}
?>
</ol>
