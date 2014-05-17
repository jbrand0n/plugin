<?php $obj->infusion_init();
if ( ! empty( $_POST ) && check_admin_referer( 'inf-settings_save', 'inf-settings_wpnonce' ) ) {
   $option = $_POST['inf_member_options'];
   update_option('inf_member_options', $option);
  
   
}

$options = get_option('inf_member_options');
//print_r($options);
?>
<div class="wrap">
  <header>
    <h2>General Options</h2>
  </header>
  <form method="post" action="">
  	<?php wp_nonce_field('inf-settings_save','inf-settings_wpnonce'); ?>
    <table class="form-table">
      <tbody>
        <tr>
          <th scope="row">Hide WP-Admin Bar</th>
          <td>
          <?php $checked = isset($options['admin_bar']) && $options['admin_bar']=='on'? 'checked="checked"':'';?>
          <input type="checkbox" value="on" class="admin_bar" id="admin_bar" name="inf_member_options[admin_bar]" <?php echo $checked;?>></td>
        </tr>
        <?php /*?><tr>
          <th scope="row">Allow Infusion Users</th>
          <td>
          <?php $checked = isset($options['inf_user']) && $options['inf_user']=='on'? 'checked="checked"':'';?>
          <input type="checkbox" value="on" class="inf_user" id="inf_user" name="inf_member_options[inf_user]" desc="Allow Infusion Users to login as Admin" <?php echo $checked;?>>
            <p>Allow Infusion Users to login as Admin</p></td>
        </tr><?php */?>
        <tr>
          <th scope="row">Custom Login Page</th>
          <td>
          <select class="login_page" id="login_page" name="inf_member_options[login_page]" default="Default Login Page" value="Array">
              <option value="0">Default Login Page</option>
              <?php foreach($obj->get_pages_data() as $page){?>
              	<option value="<?php echo $page[val];?>" <?php echo $page['val'] == $options['login_page']?'selected="selected"':'';?>><?php echo $page[name];?></option>
              <?php }?>
            </select></td>
        </tr>
        <tr>
          <th scope="row">Non-Members</th>
          <td>
          <select class="non_members" id="non_members" name="inf_member_options[non_members]" default="Non-members Page" value="Array">
              <?php 
			  foreach($obj->get_pages_data() as $page){
				$selected = ($page['val'] == $options['non_members'])?'selected="selected"':''
			  ?>
              <option value="<?php echo $page['val'];?>" <?php echo $selected;?>><?php echo $page['name'];?></option>
              <?php }?>
            </select></td>
        </tr>
        
        <tr>
          <th scope="row">Wrong Membership Level</th>
          <td>
          <select class="wrong_membership" id="wrong_membership" name="inf_member_options[wrong_membership]" default="Wrong Membership Page" value="Array">
              <?php 
			  foreach($obj->get_pages_data() as $page){
				$selected = ($page['val'] == $options['wrong_membership'])?'selected="selected"':''
			  ?>
              <option value="<?php echo $page['val'];?>" <?php echo $selected;?>><?php echo $page['name'];?></option>
              <?php }?>
            </select></td>
        </tr>
        
        
        <tr>
          <th scope="row">Password Field</th>
          <td><?php //print_r($obj->form_fields);exit;?><select class="pass_field" id="pass_field" name="inf_member_options[pass_field]" default="Select Password Field" value="Array" desc="If none is selected &quot;Password&quot; is used.">
              <option value="0">Select Password Field</option>
              <?php 
				
			  foreach($obj->form_fields as $key => $val){
				  $selected = ($val['val'] == $options['pass_field'])?'selected="selected"':'';
				  echo "<option value='$val[val]' $selected>$val[name]</option>";
			  }?>
            </select>
            <p>If none is selected "Password" is used.</p></td>
        </tr>
        <tr>
          <th scope="row">Sync with Infusionsoft</th>
          <td><select class="sync" id="sync" name="inf_member_options[sync]" default="Default Login Page" value="Array">
              <option value="24" <?php echo $options['sync'] == 24?'selected="selected"':'';?>>Every 24 hours</option>
              <option value="12" <?php echo $options['sync'] == 12?'selected="selected"':'';?>>Every 12 hours</option>
            </select> OR <div class="button button-primary" id="syncnow"> Sync Now </div><img src="<?php echo plugins_url().'/inf-member/static/img/spinner_white.gif';?>" style="display:none;" id="syncloader" /><br/>[Note*: Make sure you have selected appropriate tags to create Membership. Clicking this button, app will sync with infusion users.]</td>
        </tr>
      </tbody>
    </table>
    <p class="submit">
      <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
    </p>
  </form>
</div>
