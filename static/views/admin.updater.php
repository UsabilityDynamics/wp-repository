<?php
/**
 * Composer Repository Updater view
 */
?>
<div class="updater">
  <div class="desc">
    <h3><?php _e( 'Update Composer Repository Files.' ); ?></h3>
    <p class="description"><?php _e( 'Files will be saved in defined Composer Repository directory.' ); ?></p>
    <div>
      <input type="hidden" name="a_access_token" value="<?php echo $github_access_token; ?>" />
      <input type="hidden" name="a_path" value="<?php echo urlencode( $repository_path ); ?>" />
      <?php if( count( $organizations ) > 1 ) : ?>
      <ul>
        <li><label><input type="radio" name="a_organizations" value="<?php echo implode( ',', $organizations ); ?>" checked /> <?php _e( 'All', $this->domain ); ?></label></li>
        <?php foreach( $organizations as $organization ) : ?>
         <li><label><input type="radio" name="a_organizations" value="<?php echo $organization ?>" /> <?php echo $organization ?></label></li>
        <?php endforeach; ?>
      </ul>
      <?php else : ?>
        <input type="hidden" name="a_organizations" value="<?php echo implode( ',', $organizations ); ?>" />
      <?php endif; ?>
      <p><label><input type="checkbox" name="a_nocache" value="true" checked /><?php _e( 'No Cache', $this->domain ); ?></label></p>
    </div>
    <button id="wpr_check_updates_for_repository" class="button-primary" ><?php _e( 'Update Files' ); ?></button>
    <img id="wpr_spinner" style="display:none;padding-left:10px;" src="<?php echo $this->path( 'static/images/ajax-loader.gif' ); ?>" />
    
  </div>
</div>