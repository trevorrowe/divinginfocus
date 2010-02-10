<h1><?php echo $this->title = 'Users' ?></h1>
<?php echo $this->paginate($users) ?>
<table>
  <thead>
    <tr>
      <th>id</th>
      <th>Username</th>
      <th>Email</th>
      <th>UUID</th>
      <th>Validated</th>
      <th>Created At</th>
      <th>Updated At</th>
      <th colspan='3'>&nbsp;</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($users as $i => $user): ?>
      <tr class='<?php echo $i % 2 == 1 ? 'even' : 'odd' ?>'>
        <td><?php echo $user->id ?></td>
        <td><?php echo $this->link_to($user->username, url('show', $user)) ?></td>
        <td><?php echo h($user->email) ?></td>
        <td class='uuid'><?php echo $user->uuid ?></td>
        <td class='boolean'><?php echo format_y_n($user->is_validated()) ?></td>
        <td class='timestamp'><?php echo format_datetime($user->created_at) ?></td>
        <td class='timestamp'><?php echo format_datetime($user->updated_at) ?></td>
        <td class='action'>
          <?php echo $this->link_to('View', url('show', $user), array('class' => 'show icon_only')) ?>
        </td>
        <td class='action'>
          <?php echo $this->link_to('Edit', url('edit', $user), array('class' => 'edit icon_only')) ?>
        </td>
        <td class='action'>
          <?php echo $this->link_to('Delete', url('destroy', $user), array('class' => 'destroy icon_only', 'confirm' => true)) ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
