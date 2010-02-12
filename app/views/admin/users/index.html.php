<h1><?php echo $this->title = 'Users' ?></h1>
<?php echo $this->paginate($users) ?>
<table>
  <thead>
    <tr>
      <th>&nbsp;</th>
      <th>id</th>
      <th>Username</th>
      <th>Email</th>
      <th>UUID</th>
      <th>Admin</th>
      <th>Verified</th>
      <th>Signed Up</th>
      <th colspan='3'>&nbsp;</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($users as $i => $user): ?>
      <tr class='<?php echo $i % 2 == 1 ? 'even' : 'odd' ?>'>
        <td><?php echo $this->icon_only_link('sudo', "Login As {$user->username}", url('sudo', 'login_as', $user)) ?></td>
        <td><?php echo $user->id ?></td>
        <td><?php echo $this->link_to($user->username, url('show', $user)) ?></td>
        <td><?php echo h($user->email) ?></td>
        <td class='uuid'><?php echo $user->uuid ?></td>
        <td class='boolean'><?php echo format_y_n($user->admin) ?></td>
        <td class='boolean'><?php echo format_y_n($user->is_verified()) ?></td>
        <td class='timestamp'><?php echo format_datetime($user->created_at) ?></td>
        <td class='action'>
          <?php echo $this->icon_only_link('show', 'Details', url('show', $user)) ?></td>
        </td>
        <td class='action'>
          <?php echo $this->icon_only_link('edit', 'Edit', url('edit', $user)) ?></td>
        </td>
        <td class='action'>
          <?php echo $this->icon_only_link('destroy', 'Delete', url('destroy', $user), array('confirm' => true)) ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
