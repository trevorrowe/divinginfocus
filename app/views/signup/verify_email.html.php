<?php $this->title('Email Verification'); ?>

<form method='POST' action=''>
  <fieldset class='vertical'>
    <legend>Verify Your Email Address</legend>
    <p>Click the link sent to your email address or enter the validation
      code here.</p>
    <p><a href='<?php echo url('resend_verification_email') ?>'>
      Click here to resend the verification email.
    </a></p>

    <?php echo $this->form_row($this->text_field_tag('code', $params->code), array(
      'label' => 'Code',
      'required' => true,
    )) ?>

    <?php echo $this->submit_row('Validate') ?>

  </fieldset>
</form>
