<h1><?php echo $this->title = 'Users' ?></h1>
<table>
  <thead>
    <tr>
      <th>id</th>
      <th>Username</th>
      <th>Email</th>
      <th>UUID</th>
      <th>Admin</th>
      <th>Validated</th>
      <th>Created</th>
      <th>Updated</th>
      <th>&nbsp;</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($users as $user): ?>
      <tr class='<?php echo cycle('odd', 'even') ?>'>
        <td><?php echo $user->id ?></td>
        <td><?php echo link_to($user->username, url('show', $user)) ?></td>
        <td><?php echo h($user->email) ?></td>
        <td><?php echo $user->uuid ?></td>
        <td><?php echo format_y_n($user->admin) ?></td>
        <td><?php echo $user->activated_at ?></td>
        <td><?php echo $user->created_at ?></td>
        <td><?php echo $user->updated_at ?></td>
        <td>
          <?php echo link_to('Delete', url('destroy', $user), array('class' => 'delete icon_only', 'confirm' => true)) ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
